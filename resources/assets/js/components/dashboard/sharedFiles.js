var app = app || {};

(function ($) {
    $('#sharedfile').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('sharedFiles.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('sharedFiles.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#sharedfile_id').val('');
            $('#name').val('');
            $('#file').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.sharedfile-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file = app.SharedFiles.get($('#model_id').val());

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

    $('#sharedfile button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file_id = $('#sharedfile_id').val();

        if (file_id) {
            var file = app.SharedFiles.get(file_id);
        } else {
            var file = new app.SharedFile();
        }

        file.save({
            name:            $('#name').val(),
            file:            $('#file').val(),
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

                if (!file_id) {
                    app.SharedFiles.add(response);
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

    app.SharedFile = Backbone.Model.extend({
        urlRoot: '/shared-files'
    });

    var SharedFiles = Backbone.Collection.extend({
        model: app.SharedFile
    });

    app.SharedFiles = new SharedFiles();

    app.SharedFilesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#sharedfile_list tbody');

            $('#no_sharedfiles').show();
            $('#sharedfile_list').hide();

            this.listenTo(app.SharedFiles, 'add', this.addOne);
            this.listenTo(app.SharedFiles, 'reset', this.addAll);
            this.listenTo(app.SharedFiles, 'remove', this.addAll);
            this.listenTo(app.SharedFiles, 'all', this.render);

            app.listener.on('sharedfile:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var share = app.SharedFiles.get(parseInt(data.model.id));

                if (share) {
                    share.set(data.model);
                }
            });

            app.listener.on('sharedfile:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    app.SharedFiles.add(data.model);
                }
            });

            app.listener.on('sharedfile:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var share = app.SharedFiles.get(parseInt(data.model.id));

                if (share) {
                    app.SharedFiles.remove(share);
                }
            });
        },
        render: function () {
            if (app.SharedFiles.length) {
                $('#no_sharedfiles').hide();
                $('#sharedfile_list').show();
            } else {
                $('#no_sharedfiles').show();
                $('#sharedfile_list').hide();
            }
        },
        addOne: function (file) {

            var view = new app.SharedFileView({
                model: file
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.SharedFiles.each(this.addOne, this);
        }
    });

    app.SharedFileView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#sharedfile-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#sharedfile_id').val(this.model.id);
            $('#name').val(this.model.get('name'));
            $('#file').val(this.model.get('file'));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade sharedfile-trash');
        }
    });

})(jQuery);
