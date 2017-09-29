var app = app || {};

(function ($) {
   // FIXME: This seems very wrong
    $('#environment').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('environments.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('environments.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#environment_id').val('');
            $('#environment_name').val('');
            $('#environment_description').val('');
            $('#environment_default_on').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('body').delegate('.environment-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var environment = app.Environments.get($('#model_id').val());

        environment.destroy({
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
    $('#environment button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var environment_id = $('#environment_id').val();

        if (environment_id) {
            var environment = app.Environments.get(environment_id);
        } else {
            var environment = new app.Environment();
        }

        environment.save({
            name:            $('#environment_name').val(),
            description:     $('#environment_description').val(),
            default_on:      $('#environment_default_on').is(':checked'),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!environment_id) {
                    app.Environments.add(response);
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

    app.Environment = Backbone.Model.extend({
        urlRoot: '/environments',
        initialize: function() {

        }
    });

    var Environments = Backbone.Collection.extend({
        model: app.Environment
    });

    app.Environments = new Environments();

    app.EnvironmentsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#environment_list tbody');

            $('#environment_list').hide();

            this.listenTo(app.Environments, 'add', this.addOne);
            this.listenTo(app.Environments, 'reset', this.addAll);
            this.listenTo(app.Environments, 'remove', this.addAll);
            this.listenTo(app.Environments, 'all', this.render);

            app.listener.on('environment:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                $('#environment_' + data.model.id).html(data.model.name);

                var environment = app.Environments.get(parseInt(data.model.id));

                if (environment) {
                    environment.set(data.model);
                }
            });

            app.listener.on('environment:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    app.Environments.add(data.model);
                }
            });

            app.listener.on('environment:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var environment = app.Environments.get(parseInt(data.model.id));

                if (environment) {
                    app.Environments.remove(environment);
                }
            });
        },
        render: function () {
            if (app.Environments.length) {
                $('#environment_list').show();
            } else {
                $('#environment_list').hide();
            }
        },
        addOne: function (environment) {

            var view = new app.EnvironmentView({
                model: environment
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Environments.each(this.addOne, this);
        }
    });

    app.EnvironmentView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editEnvironment',
            'click .btn-delete': 'trashEnvironment'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#environment-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        editEnvironment: function() {
            $('#environment_id').val(this.model.id);
            $('#environment_name').val(this.model.get('name'));
            $('#environment_description').val(this.model.get('description'));
            $('#environment_default_on').prop('checked', (this.model.get('default_on') === true));
        },
        trashEnvironment: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade environment-trash');
        }
    });
})(jQuery);
