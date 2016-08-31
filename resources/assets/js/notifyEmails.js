var app = app || {};

(function ($) {
    // FIXME: This seems very wrong
    $('#notifyemail').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('notifyEmails.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('notifyEmails.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#notifyemail_id').val('');
            $('#notifyemail_name').val('');
            $('#notifyemail_address').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
$('body').delegate('.notifyemail-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file = app.NotifyEmails.get($('#model_id').val());

        file.destroy({
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
    $('#notifyemail button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var notifyemail_id = $('#notifyemail_id').val();

        if (notifyemail_id) {
            var file = app.NotifyEmails.get(notifyemail_id);
        } else {
            var file = new app.NotifyEmail();
        }

        file.save({
            name:       $('#notifyemail_name').val(),
            email:      $('#notifyemail_address').val(),
            project_id: $('input[name="project_id"]').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!notifyemail_id) {
                    app.NotifyEmails.add(response);
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

    app.NotifyEmail = Backbone.Model.extend({
        urlRoot: '/notify-email'
    });

    var NotifyEmails = Backbone.Collection.extend({
        model: app.NotifyEmail
    });

    app.NotifyEmails = new NotifyEmails();

    app.NotifyEmailsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#notifyemail_list tbody');

            $('#no_notifyemails').show();
            $('#notifyemail_list').hide();

            this.listenTo(app.NotifyEmails, 'add', this.addOne);
            this.listenTo(app.NotifyEmails, 'reset', this.addAll);
            this.listenTo(app.NotifyEmails, 'remove', this.addAll);
            this.listenTo(app.NotifyEmails, 'all', this.render);

            app.listener.on('notifyemail:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var email = app.NotifyEmails.get(parseInt(data.model.id));

                if (server) {
                    email.set(data.model);
                }
            });

            app.listener.on('notifyemail:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                if (parseInt(data.model.project_id) === parseInt(app.project_id)) {
                    app.NotifyEmails.add(data.model);
                }
            });

            app.listener.on('notifyemail:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var email = app.NotifyEmails.get(parseInt(data.model.id));

                if (email) {
                    app.NotifyEmails.remove(email);
                }
            });
        },
        render: function () {
            if (app.NotifyEmails.length) {
                $('#no_notifyemails').hide();
                $('#notifyemail_list').show();
            } else {
                $('#no_notifyemails').show();
                $('#notifyemail_list').hide();
            }
        },
        addOne: function (file) {

            var view = new app.EmailView({
                model: file
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.NotifyEmails.each(this.addOne, this);
        }
    });

    app.EmailView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editEmail',
            'click .btn-delete': 'trashEmail'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#notifyemail-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        editEmail: function() {
            // FIXME: Sure this is wrong?
            $('#notifyemail_id').val(this.model.id);
            $('#notifyemail_name').val(this.model.get('name'));
            $('#notifyemail_address').val(this.model.get('email'));
        },
        trashEmail: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade notifyemail-trash');
        }
    });

})(jQuery);
