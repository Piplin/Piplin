var app = app || {};

(function ($) {
    // FIXME: This seems very wrong
    $('#template').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('templates.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('templates.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#template_id').val('');
            $('#template_name').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('body').delegate('.template-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var template = app.Templates.get($('#model_id').val());

        template.destroy({
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
    $('#template button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var template_id = $('#template_id').val();

        if (template_id) {
            var template = app.Templates.get(template_id);
        } else {
            var template = new app.Template();
        }

        template.save({
            name: $('#template_name').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!template_id) {
                    app.Templates.add(response);

                    window.location.href = '/admin/templates/' + response.id;
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

    app.Template = Backbone.Model.extend({
        urlRoot: '/admin/templates'
    });

    var Templates = Backbone.Collection.extend({
        model: app.Template
    });

    app.Templates = new Templates();

    app.TemplatesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#template_list tbody');

            $('#template_list').hide();
            $('#no_templates').show();

            this.listenTo(app.Templates, 'add', this.addOne);
            this.listenTo(app.Templates, 'reset', this.addAll);
            this.listenTo(app.Templates, 'remove', this.addAll);
            this.listenTo(app.Templates, 'all', this.render);

            app.listener.on('template:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var template = app.Templates.get(parseInt(data.model.id));

                if (template) {
                    template.set(data.model);
                }
            });

            app.listener.on('template:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                app.Templates.add(data.model);
            });

            app.listener.on('template:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var template = app.Templates.get(parseInt(data.model.id));

                if (template) {
                    app.Templates.remove(template);
                }
            });
        },
        render: function () {
            if (app.Templates.length) {
                $('#no_templates').hide();
                $('#template_list').show();
            } else {
                $('#no_templates').show();
                $('#template_list').hide();
            }
        },
        addOne: function (template) {
            var view = new app.TemplateView({
                model: template
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Templates.each(this.addOne, this);
        }
    });

    app.TemplateView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editTemplate',
            'click .btn-delete': 'trashTemplate'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#template-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        editTemplate: function() {
            $('#template_id').val(this.model.id);
            $('#template_name').val(this.model.get('name'));
        },
        trashTemplate: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade template-trash');
        }
    });
})(jQuery);
