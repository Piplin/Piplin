var app = app || {};

(function ($) {
    // FIXME: This seems very wrong
    $('#notifyslack').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('notifySlacks.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('notifySlacks.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#notifyslack_id').val('');
            $('#notifyslack_name').val('');
            $('#notifyslack_webhook').val('');
            $('#notifyslack_channel').val('');
            $('#notifyslack_icon').val('');
            $('#notifyslack_failure_only').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('body').delegate('.notifyslack-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var notifyslack = app.NotifySlacks.get($('#model_id').val());

        notifyslack.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    // FIXME: This seems very wrong
    $('#notifyslack button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var notifyslack_id = $('#notifyslack_id').val();

        if (notifyslack_id) {
            var notifyslack = app.NotifySlacks.get(notifyslack_id);
        } else {
            var notifyslack = new app.NotifySlack();
        }

        notifyslack.save({
            name:         $('#notifyslack_name').val(),
            webhook:      $('#notifyslack_webhook').val(),
            channel:      $('#notifyslack_channel').val(),
            icon:         $('#notifyslack_icon').val(),
            project_id:   $('input[name="project_id"]').val(),
            failure_only: $('#notifyslack_failure_only').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!notifyslack_id) {
                    app.NotifySlacks.add(response);
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
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });



    app.NotifySlack = Backbone.Model.extend({
        urlRoot: '/notify-slack'
    });

    var NotifySlacks = Backbone.Collection.extend({
        model: app.NotifySlack
    });

    app.NotifySlacks = new NotifySlacks();

    app.NotifySlacksTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#notifyslack_list tbody');

            $('#no_notifyslacks').show();
            $('#notifyslack_list').hide();

            this.listenTo(app.NotifySlacks, 'add', this.addOne);
            this.listenTo(app.NotifySlacks, 'reset', this.addAll);
            this.listenTo(app.NotifySlacks, 'remove', this.addAll);
            this.listenTo(app.NotifySlacks, 'all', this.render);


            app.listener.on('notifyslack:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var notifyslack = app.NotifySlacks.get(parseInt(data.model.id));

                if (server) {
                    notifyslack.set(data.model);
                }
            });

            app.listener.on('notifyslack:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                if (parseInt(data.model.project_id) === parseInt(app.project_id)) {
                    app.NotifySlacks.add(data.model);
                }
            });

            app.listener.on('notifyslack:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var notifyslack = app.NotifySlacks.get(parseInt(data.model.id));

                if (notifyslack) {
                    app.NotifySlacks.remove(notifyslack);
                }
            });
        },
        render: function () {
            if (app.NotifySlacks.length) {
                $('#no_notifyslacks').hide();
                $('#notifyslack_list').show();
            } else {
                $('#no_notifyslacks').show();
                $('#notifyslack_list').hide();
            }
        },
        addOne: function (notifyslack) {

            var view = new app.NotifySlackView({
                model: notifyslack
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.NotifySlacks.each(this.addOne, this);
        }
    });

    app.NotifySlackView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editNotifySlack',
            'click .btn-delete': 'trashNotifySlack'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#notifyslack-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        editNotifySlack: function() {
            // FIXME: Sure this is wrong?
            $('#notifyslack_id').val(this.model.id);
            $('#notifyslack_name').val(this.model.get('name'));
            $('#notifyslack_webhook').val(this.model.get('webhook'));
            $('#notifyslack_channel').val(this.model.get('channel'));
            $('#notifyslack_icon').val(this.model.get('icon'));
            $('#notifyslack_failure_only').prop('checked', (this.model.get('failure_only') === true));
        },
        trashNotifySlack: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade notifyslack-trash');
        }
    });
})(jQuery);
