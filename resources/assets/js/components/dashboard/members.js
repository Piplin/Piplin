(function ($) {

    // test
    var settings =  {
        placeholder: trans('members.search'),
        minimumInputLength: 1,
        width: '100%',
        ajax: {
            url: "/autocomplete/users",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term // search term
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (obj) {
                        return {id: obj.id, text: obj.name};
                    })
                };
            },
            cache: true
        }
    };

    var member_select2 = $('.project-members').select2(settings);

    // end
    $('#member').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('members.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.callout-warning', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('members.edit');
            $('#user_ids').parent().parent().hide();
            $('.btn-danger', modal).show();
        } else {
            $('#user_ids').parent().parent().show();
            member_select2.val('').trigger('change');
            modal.find('.modal-title span').text(trans('members.create'));
        }

        modal.find('.modal-title span').text(title);
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

    $('body').delegate('.member-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('fixhub fixhub-load fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var member = Fixhub.Members.get($('#model_id').val());

        member.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('fixhub fixhub-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('members.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('fixhub fixhub-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#member button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('fixhub fixhub-load fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var member_id = $('#member_id').val();

        if (member_id) {
            var member = Fixhub.Members.get(member_id);
        } else {
            var member = new Fixhub.Member();
        }

        var data = {
          user_ids:    $('#user_ids').val(),
          project_id: parseInt($('input[name="project_id"]').val())
        };

        member.save(data, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('fixhub fixhub-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('members.edit_success');
                if (!member_id) {
                    Fixhub.Members.add(response);
                    msg = trans('members.create_success');
                }
                Fixhub.toast(msg);
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form select', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent();
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass().addClass('fixhub fixhub-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    Fixhub.Member = Backbone.Model.extend({
        urlRoot: '/members/' + parseInt($('input[name="project_id"]').val())
    });

    var Members = Backbone.Collection.extend({
        model: Fixhub.Member
    });

    Fixhub.Members = new Members();

    Fixhub.MembersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#member_list tbody');

            $('#no_members').show();
            $('#member_list').hide();

            this.listenTo(Fixhub.Members, 'add', this.addOne);
            this.listenTo(Fixhub.Members, 'reset', this.addAll);
            this.listenTo(Fixhub.Members, 'remove', this.addAll);
            this.listenTo(Fixhub.Members, 'all', this.render);

            Fixhub.listener.on('member:' + Fixhub.events.MODEL_CHANGED, function (data) {
                var member = Fixhub.Members.get(parseInt(data.model.id));

                if (member) {
                    member.set(data.model);
                }
            });

            Fixhub.listener.on('member:' + Fixhub.events.MODEL_CREATED, function (data) {
                if (parseInt(data.model.project_id) === parseInt(Fixhub.project_id)) {
                    Fixhub.Members.add(data.model);
                }
            });

            Fixhub.listener.on('member:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var member = Fixhub.Members.get(parseInt(data.model.id));

                if (member) {
                    Fixhub.Members.remove(member);
                }
            });
        },
        render: function () {
            if (Fixhub.Members.length) {
                $('#no_members').hide();
                $('#member_list').show();
            } else {
                $('#no_members').show();
                $('#member_list').hide();
            }
        },
        addOne: function (member) {
            var view = new Fixhub.MemberView({
                model: member
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.Members.each(this.addOne, this);
        }
    });

    Fixhub.MemberView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#member-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade member-trash');
        }
    });
})(jQuery);