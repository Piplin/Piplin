var app = app || {};

(function ($) {

    // test
    var settings =  {
        placeholder: 'Search for a collaborator',
        minimumInputLength: 3,
        width: '100%',
        ajax: {
          url: function () {
            return '/test.php';
          },
          dataType: 'json',
          quietMillis: 200,
          data: function (term, page) {
            return { q: term };
          },
          results: function (data, page) {
            return {results: data.collaborators};
          }
        },
        formatSelection: function(collaborator, container) {
          return collaborator.username;
        },
        formatResult: function(collaborator, container) {
          if(collaborator.first_name && collaborator.last_name) {
            return collaborator.first_name + ' ' + collaborator.last_name + ' (' + collaborator.username + ')';
          } else {
            return collaborator.username;
          }
        }
    }
    $('.collaborators').select2(settings);

    // end
    $('#member').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.callout-warning', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            $('.btn-danger', modal).show();
        } else {
            $('#member_id').val('');
            $('#member_name').val('');
            modal.find('.modal-title span').text(trans('members.create'));
        }
    });

    $('#member #member-type a.btn-app').on('click', function(event) {
        var button = $(event.currentTarget);
        var modal = $('#member');

        if (button.attr('disabled')) {
            $('.callout-warning', modal).show();
            return;
        }

        $('.callout-warning', modal).hide();
    });

    //$('#member button.btn-delete').on('click', function (event) {
    $('body').delegate('.member-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-load-c fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var member = app.Members.get($('#model_id').val());

        member.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass();
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass();
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#member button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-load-c fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var member_id = $('#member_id').val();

        if (member_id) {
            var member = app.Members.get(member_id);
        } else {
            var member = new app.Member();
        }

        var data = {
          config:     null,
          name:       $('#member_name').val(),
          project_id: parseInt($('input[name="project_id"]').val())
        };

        $('#member #member-config-' + data.type + ' :input[id^=member_config]').each(function(key, field) {
            var name = $(field).attr('name');

            data[name] = $(field).val();
        });

        member.save(data, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass();
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!member_id) {
                    app.Members.add(response);
                }
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parents('div.form-group');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass();
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    app.Member = Backbone.Model.extend({
        urlRoot: '/members'
    });

    var Members = Backbone.Collection.extend({
        model: app.Member
    });

    app.Members = new Members();

    app.MembersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#member_list tbody');

            $('#no_members').show();
            $('#member_list').hide();

            this.listenTo(app.Members, 'add', this.addOne);
            this.listenTo(app.Members, 'reset', this.addAll);
            this.listenTo(app.Members, 'remove', this.addAll);
            this.listenTo(app.Members, 'all', this.render);


            app.listener.on('member:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var member = app.Members.get(parseInt(data.model.id));

                if (member) {
                    member.set(data.model);
                }
            });

            app.listener.on('member:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                if (parseInt(data.model.project_id) === parseInt(app.project_id)) {
                    app.Members.add(data.model);
                }
            });

            app.listener.on('member:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var member = app.Members.get(parseInt(data.model.id));

                if (member) {
                    app.Members.remove(member);
                }
            });
        },
        render: function () {
            if (app.Members.length) {
                $('#no_members').hide();
                $('#member_list').show();
            } else {
                $('#no_members').show();
                $('#member_list').hide();
            }
        },
        addOne: function (member) {
            var view = new app.MemberView({
                model: member
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Members.each(this.addOne, this);
        }
    });

    app.MemberView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#member-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.label = this.model.get('pivot').level;

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            var type = this.model.get('type');

            $.each(this.model.get('config'), function(field, value) {
                $('#member-config-' + type + ' #member_config_' + field).val(value);
            });

            $('#member_id').val(this.model.id);
            $('#member_name').val(this.model.get('name'));

            setTitleWithIcon(this.model.get('type'), 'edit');
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade member-trash');
        }
    });
})(jQuery);