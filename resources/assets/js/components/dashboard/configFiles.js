(function ($) {

    var SUCCESSFUL = 0;
    var UNSYNCED   = 1;
    var FAILED     = 2;
    var SYNCING    = 3;

    var editor;
    var previewfile;

    $('#configfile, #view-configfile, #sync-configfile').on('hidden.bs.modal', function (event) {
        editor.destroy();
    });

    $('#view-configfile').on('show.bs.modal', function (event) {
        editor = ace.edit('preview-content');
        editor.setReadOnly(true);
        editor.getSession().setUseWrapMode(true);

        var extension = previewfile.substr(previewfile.lastIndexOf('.') + 1).toLowerCase();

        if (extension === 'php' || extension === 'ini') {
            editor.getSession().setMode('ace/mode/' + extension);
        } else if (extension === 'yml') {
            editor.getSession().setMode('ace/mode/yaml');
        }
    });

    $('#configfile').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('configFiles.create');

        editor = ace.edit('content');

        var filename = $('#path').val();
        var extension = filename.substr(filename.lastIndexOf('.') + 1).toLowerCase();

        if (extension === 'php' || extension === 'ini') {
            editor.getSession().setMode('ace/mode/' + extension);
        } else if (extension === 'yml') {
            editor.getSession().setMode('ace/mode/yaml');
        }

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('configFiles.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#file_id').val('');
            $('#name').val('');
            $('#path').val('');
            $('.configfile-environment').prop('checked', true);
            editor.setValue('');
            editor.gotoLine(1);
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.configfile-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file = Piplin.ConfigFiles.get($('#model_id').val());

        file.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('configFiles.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#configfile button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var config_file_id = $('#config_file_id').val();

        if (config_file_id) {
            var file = Piplin.ConfigFiles.get(config_file_id);
        } else {
            var file = new Piplin.ConfigFile();
        }

        var environment_ids = [];

        $('.configfile-environment:checked').each(function() {
            environment_ids.push($(this).val());
        });

        file.save({
            name:            $('#name').val(),
            path:            $('#path').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val(),
            environments:    environment_ids,
            content:         editor.getValue()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('configFiles.edit_success');
                if (!config_file_id) {
                    Piplin.ConfigFiles.add(response);
                    trans('configFiles.create_success');
                }

                editor.setValue('');
                editor.gotoLine(1);

                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#sync-configfile button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var config_file_id = $('#sync-configfile_id').val();

        if (config_file_id) {
            var file = Piplin.ConfigFiles.get(config_file_id);
        } else {
            var file = new Piplin.ConfigFile();
        }

        file.set({
            status: SYNCING
        });

        var environment_ids = [];

        $('.sync-environment:checked').each(function() {
            environment_ids.push($(this).val());
        });
        var post_commands = editor.getValue();

        $.ajax({
            type: 'POST',
            url: '/config-files/' + config_file_id + '/sync',
            data: {
                post_commands:   post_commands,
                environment_ids: environment_ids
            }
        }).done(function (data) {
            dialog.modal('hide');
            $('.callout-danger', dialog).hide();

            icon.removeClass().addClass('piplin piplin-save');
            $('button.close', dialog).show();

            var msg = trans('configFiles.sync_success');
            Piplin.toast(msg);
        });

        console.log(post_commands);

    });

    Piplin.ConfigFile = Backbone.Model.extend({
        urlRoot: '/config-files'
    });

    var ConfigFiles = Backbone.Collection.extend({
        model: Piplin.ConfigFile
    });

    Piplin.ConfigFiles = new ConfigFiles();

    Piplin.ConfigFilesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#configfile_list tbody');

            $('#no_configfiles').show();
            $('#configfile_list').hide();

            this.listenTo(Piplin.ConfigFiles, 'add', this.addOne);
            this.listenTo(Piplin.ConfigFiles, 'reset', this.addAll);
            this.listenTo(Piplin.ConfigFiles, 'remove', this.addAll);
            this.listenTo(Piplin.ConfigFiles, 'all', this.render);

            Piplin.listener.on('configfile:' + Piplin.events.MODEL_CHANGED, function (data) {
                var file = Piplin.ConfigFiles.get(parseInt(data.model.id));

                if (file) {
                    file.set(data.model);
                }
            });

            Piplin.listener.on('configfile:' + Piplin.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    Piplin.ConfigFiles.add(data.model);
                }
            });

            Piplin.listener.on('configfile:' + Piplin.events.MODEL_TRASHED, function (data) {
                var file = Piplin.ConfigFiles.get(parseInt(data.model.id));

                if (file) {
                    Piplin.ConfigFiles.remove(file);
                }
            });
        },
        render: function () {
            if (Piplin.ConfigFiles.length) {
                $('#no_configfiles').hide();
                $('#configfile_list').show();
            } else {
                $('#no_configfiles').show();
                $('#configfile_list').hide();
            }
        },
        addOne: function (file) {

            var view = new Piplin.ConfigFileView({
                model: file
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.ConfigFiles.each(this.addOne, this);
        }
    });

    Piplin.ConfigFileView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash',
            'click .btn-view': 'view',
            'click .btn-sync': 'sync',
            'click .btn-show': 'showLog',
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#configfiles-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.status_css = 'orange';
            data.icon_css   = 'circle';
            data.status     = trans('configFiles.unsynced');

            if (parseInt(this.model.get('status')) === SUCCESSFUL) {
                data.status_css = 'success';
                data.status     = trans('configFiles.successful');
            } else if (parseInt(this.model.get('status')) === SYNCING) {
                data.status_css = 'purple';
                data.icon_css   = 'load piplin-spin';
                data.status     = trans('configFiles.syncing');
            } else if (parseInt(this.model.get('status')) === FAILED) {
                data.status_css = 'danger';
                data.status     = trans('configFiles.failed');
            }
            console.log(data);

            this.$el.html(this.template(data));

            return this;
        },
        view: function() {
            previewfile = this.model.get('path');
            $('#preview-content').text(this.model.get('content'));
        },
        showLog: function() {
            var data = this.model.toJSON();

            $('#log pre').html(data.output);
        },
        sync: function() {
            $('#sync-configfile_id').val(this.model.id);
            editor = ace.edit('command_script');
            editor.setValue('');
            editor.gotoLine(1);
            $('.sync-environment').prop('checked', false).prop('disabled', true).parent().attr('class', 'text-gray');
            $(this.model.get('environments')).each(function (index, environment) {
                $('#sync_environment_' + environment.id).prop('checked', true).prop('disabled', false).parent().removeClass('text-gray');
            });
        },
        edit: function() {
            $('#config_file_id').val(this.model.id);
            $('#name').val(this.model.get('name'));
            $('#path').val(this.model.get('path'));
            $('#content').text(this.model.get('content'));

            $('.configfile-environment').prop('checked', false);
            $(this.model.get('environments')).each(function (index, environment) {
                $('#configfile_environment_' + environment.id).prop('checked', true);
            });
        },
        trash: function () {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade configfile-trash');
        }
    });

})(jQuery);
