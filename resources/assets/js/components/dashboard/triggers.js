var app = app || {};

(function ($) {
    $('#trigger').on('show.bs.modal', function (event) {
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
            $('#trigger_id').val('');
            $('#trigger_name').val('');
            $('#trigger_type').val('');
            $('#trigger :input[id^=trigger_config]').val('');
            $('#trigger .trigger-config input[type=checkbox]').prop('checked', true);
            $('#trigger .trigger-enabled input[type=checkbox]').prop('checked', true);
            $('#trigger .modal-footer').hide();
            $('.trigger-config').hide();
            $('.trigger-enabled').hide();
            $('#trigger-type').show();
            modal.find('.modal-title span').text(trans('triggers.create'));
        }
    });

    $('.schedule-editor:radio').on('change', function (event) {
        var target = $(event.currentTarget);
        console.log(target.val());
        $('div.schedule-editor-container').hide();
        if (target.val() === 'daily') {
            $('#daily-form').show();
        } else if (target.val() === 'daysOfWeek') {
            $('#daysOfWeek-form').show();
        } else if (target.val() === 'daysOfMonth') {
            $('#daysOfMonth-form').show();
        } else if (target.val() === 'advanced') {
            $('#advanced-form').show();
        }
    });

    $('#trigger #trigger-type a.btn-app').on('click', function(event) {
        var button = $(event.currentTarget);
        var modal = $('#trigger');

        if (button.attr('disabled')) {
            $('.callout-warning', modal).show();
            return;
        }

        $('.callout-warning', modal).hide();

        var type = button.data('type');
        setTitleWithIcon(type, 'create');
    });

    function setTitleWithIcon(type, action) {
        $('#trigger .modal-title span').text(trans('triggers.' + action + '_' + type));

        var element = $('#trigger .modal-title i').removeClass().addClass('ion');
        var icon = 'clock';

        if (type === 'schedule') {
            icon = 'clock';
        } else if (type === 'daily') {
            icon = 'calendar';
        }

        element.addClass('ion-' + icon);

        $('#trigger .modal-footer').show();
        $('.trigger-config').hide();
        $('#trigger-type').hide();
        $('#trigger-name').show();
        $('#trigger-triggers').show();
        $('.trigger-enabled').show();
        $('#trigger-config-' + type).show();
        $('#trigger_type').val(type);
    }

    //$('#trigger button.btn-delete').on('click', function (event) {
    $('body').delegate('.trigger-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var trigger = app.Triggers.get($('#model_id').val());

        trigger.destroy({
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

    $('#trigger button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var trigger_id = $('#trigger_id').val();

        if (trigger_id) {
            var trigger = app.Triggers.get(trigger_id);
        } else {
            var trigger = new app.Trigger();
        }

        var data = {
          config:     null,
          name:       $('#trigger_name').val(),
          type:       $('#trigger_type').val(),
          project_id: parseInt($('input[name="project_id"]').val()),
          enabled:    $('#trigger_enabled').is(':checked')
        };

        $('#trigger #trigger-config-' + data.type + ' :input[id^=trigger_config]').each(function(key, field) {
            var name = $(field).attr('name');

            data[name] = $(field).val();
        });

        trigger.save(data, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!trigger_id) {
                    app.Triggers.add(response);
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

                icon.removeClass('ion-refresh ion-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    app.Trigger = Backbone.Model.extend({
        urlRoot: '/triggers'
    });

    var Triggers = Backbone.Collection.extend({
        model: app.Trigger
    });

    app.Triggers = new Triggers();

    app.TriggersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#trigger_list tbody');

            $('#no_triggers').show();
            $('#trigger_list').hide();

            this.listenTo(app.Triggers, 'add', this.addOne);
            this.listenTo(app.Triggers, 'reset', this.addAll);
            this.listenTo(app.Triggers, 'remove', this.addAll);
            this.listenTo(app.Triggers, 'all', this.render);


            app.listener.on('trigger:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var trigger = app.Triggers.get(parseInt(data.model.id));

                if (trigger) {
                    trigger.set(data.model);
                }
            });

            app.listener.on('trigger:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                if (parseInt(data.model.project_id) === parseInt(app.project_id)) {
                    app.Triggers.add(data.model);
                }
            });

            app.listener.on('trigger:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var trigger = app.Triggers.get(parseInt(data.model.id));

                if (trigger) {
                    app.Triggers.remove(trigger);
                }
            });
        },
        render: function () {
            if (app.Triggers.length) {
                $('#no_triggers').hide();
                $('#trigger_list').show();
            } else {
                $('#no_triggers').show();
                $('#trigger_list').hide();
            }
        },
        addOne: function (trigger) {
            var view = new app.TriggerView({
                model: trigger
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Triggers.each(this.addOne, this);
        }
    });

    app.TriggerView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#trigger-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.icon = 'clock';
            data.label = trans('triggers.schedule');

            if (this.model.get('type') !== 'schedule') {
                data.label = trans('triggers.' + this.model.get('type'));
            }

            if (this.model.get('type') === 'schedule') {
                data.icon = 'clock';
            } else if (this.model.get('type') === 'daily') {
                data.icon = 'calendar';
            }

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            var type = this.model.get('type');

            $.each(this.model.get('config'), function(field, value) {
                $('#trigger-config-' + type + ' #trigger_config_' + field).val(value);
            });

            $('#trigger_id').val(this.model.id);
            $('#trigger_name').val(this.model.get('name'));
            $('#trigger_type').val(type);
            $('#trigger_enabled').prop('checked', (this.model.get('enabled') === true));

            setTitleWithIcon(this.model.get('type'), 'edit');
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade trigger-trash');
        }
    });
})(jQuery);