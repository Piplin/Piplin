(function ($) {

    $('.command-list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('command-id'));
            });

            $.ajax({
                url: '/commands/reorder',
                method: 'POST',
                data: {
                    commands: ids
                }
            });
        }
    });

    var editor;

    $('#command').on('hidden.bs.modal', function (event) {
        editor.destroy();
    });

    $('#command').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('commands.create');

        editor = ace.edit('command_script');
        editor.getSession().setMode('ace/mode/sh');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('commands.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#command_id').val('');
            $('#command_step').val(button.data('step'));
            $('#command_name').val('');
            editor.setValue('');
            editor.gotoLine(1);
            $('#command_user').val('');
            $('#command_optional').prop('checked', false);
            $('#command_default_on').prop('checked', false);
            $('#command_default_on_row').addClass('hide');

            $('.command-environment').prop('checked', true);
            $('.command-pattern').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.command-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var command = Piplin.Commands.get($('#model_id').val());

        command.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('commands.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#command_optional').on('change', function (event) {
        $('#command_default_on_row').addClass('hide');
        if ($(this).is(':checked') === true) {
            $('#command_default_on_row').removeClass('hide');
        }
    });

    //If no `off`, the code below will be executed twice.
    $('#command button.btn-save').off('click').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find(':input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var command_id = $('#command_id').val();
        if (command_id) {
            var command = Piplin.Commands.get(command_id);
        } else {
            var command = new Piplin.Command();
        }

        var environment_ids = [];
        $('.command-environment:checked').each(function() {
            environment_ids.push($(this).val());
        });

        var pattern_ids = [];
        $('.command-pattern:checked').each(function() {
            pattern_ids.push($(this).val());
        });

        command.save({
            name:            $('#command_name').val(),
            script:          editor.getValue(),
            user:            $('#command_user').val(),
            step:            $('#command_step').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val(),
            environments:    environment_ids,
            patterns:        pattern_ids,
            optional:        $('#command_optional').is(':checked'),
            default_on:      $('#command_default_on').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find(':input').removeAttr('disabled');

                var msg = trans('commands.edit_success');
                if (!command_id) {
                    Piplin.Commands.add(response);
                    msg = trans('commands.create_success');
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
                dialog.find(':input').removeAttr('disabled');
            }
        });
    });

    Piplin.Command = Backbone.Model.extend({
        urlRoot: '/commands',
        defaults: function() {
            return {
                order: Piplin.Commands.nextOrder()
            };
        },
        isAfter: function() {
            return (parseInt(this.get('step')) % 3 === 0);
        }
    });

    var Commands = Backbone.Collection.extend({
        model: Piplin.Command,
        comparator: 'order',
        nextOrder: function() {
            if (!this.length) {
                return 1;
            }

            return this.last().get('order') + 1;
        }
    });

    Piplin.Commands = new Commands();

    Piplin.CommandsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$beforeList = $('#commands-before .command-list tbody');
            this.$afterList = $('#commands-after .command-list tbody');

            $('.no-commands').show();
            $('.command-list').hide();

            this.listenTo(Piplin.Commands, 'add', this.addOne);
            this.listenTo(Piplin.Commands, 'reset', this.addAll);
            this.listenTo(Piplin.Commands, 'remove', this.addAll);
            this.listenTo(Piplin.Commands, 'all', this.render);

            Piplin.listener.on('command:' + Piplin.events.MODEL_CHANGED, function (data) {
                var command = Piplin.Commands.get(parseInt(data.model.id));

                if (command) {
                    command.set(data.model);
                }
            });

            Piplin.listener.on('command:' + Piplin.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                //if (data.model.targetable_type == Piplin.targetable_type && parseInt(data.model.targetable_id) === parseInt(Piplin.targetable_id)) {

                    // Make sure the command is for this action (clone, install, activate, purge)
                    if (parseInt(data.model.step) + 1 === parseInt(Piplin.command_action) || parseInt(data.model.step) - 1 === parseInt(Piplin.command_action)) {
                        Piplin.Commands.add(data.model);
                    }
                }
            });

            Piplin.listener.on('command:' + Piplin.events.MODEL_TRASHED, function (data) {
                var command = Piplin.Commands.get(parseInt(data.model.id));

                if (command) {
                    Piplin.Commands.remove(command);
                }
            });
        },
        render: function () {
            var before = Piplin.Commands.find(function(model) {
                return !model.isAfter();
            });

            if (typeof before !== 'undefined') {
                $('#commands-before .no-commands').hide();
                $('#commands-before .command-list').show();
            } else {
                $('#commands-before .no-commands').show();
                $('#commands-before .command-list').hide();
            }

            var after = Piplin.Commands.find(function(model) {
                return model.isAfter();
            });

            if (typeof after !== 'undefined') {
                $('#commands-after .no-commands').hide();
                $('#commands-after .command-list').show();
            } else {
                $('#commands-after .no-commands').show();
                $('#commands-after .command-list').hide();
            }
        },
        addOne: function (command) {
            var view = new Piplin.CommandView({
                model: command
            });

            if (command.isAfter()) {
                this.$afterList.append(view.render().el);
                if ($('tr', this.$afterList).length < 2) {
                    $('.drag-handle', this.$afterList).hide();
                } else {
                    $('.drag-handle', this.$afterList).show();
                }
            } else {
                this.$beforeList.append(view.render().el);
                if ($('tr', this.$beforeList).length < 2) {
                    $('.drag-handle', this.$beforeList).hide();
                } else {
                    $('.drag-handle', this.$beforeList).show();
                }
            }
        },
        addAll: function () {
            this.$beforeList.html('');
            this.$afterList.html('');
            Piplin.Commands.each(this.addOne, this);
        }
    });

    Piplin.CommandView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#command-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#command_id').val(this.model.id);
            $('#command_step').val(this.model.get('step'));
            $('#command_name').val(this.model.get('name'));
            $('#command_script').text(this.model.get('script'));
            $('#command_user').val(this.model.get('user'));
            $('#command_optional').prop('checked', (this.model.get('optional') === true));
            $('#command_default_on').prop('checked', (this.model.get('default_on') === true));

            $('#command_default_on_row').addClass('hide');
            if (this.model.get('optional') === true) {
                $('#command_default_on_row').removeClass('hide');
            }

            $('.command-environment').prop('checked', false);
            $(this.model.get('environments')).each(function (index, environment) {
                $('#command_environment_' + environment.id).prop('checked', true);
            });

            $('.command-pattern').prop('checked', false);
            $(this.model.get('patterns')).each(function (index, pattern) {
                $('#command_pattern_' + pattern.id).prop('checked', true);
            });
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade command-trash');
        }
    });
})(jQuery);

(function ($) {

    $('#task').on('show.bs.modal', function (event) {
        var modal = $(this);
        $('.callout-danger', modal).hide();
        var targetable_type = $('input[name="targetable_type"]').val();

        if (new RegExp("Plan$").test(targetable_type)) {
            var title = trans('tasks.build');
            modal.find('.modal-title span').text(title);
            modal.find('.modal-title i').removeClass().addClass('piplin piplin-build');
            modal.find('button.btn-save span').text(title);
        }
    });

    $('.task-source:radio').on('change', function (event) {
        var target = $(event.currentTarget);

        $('div.task-source-container').hide();
        if (target.val() === 'branch') {
            $('#task_branch').parent('div').show();
        } else if (target.val() === 'tag') {
            $('#task_tag').parent('div').show();
        } else if (target.val() === 'commit') {
            $('#task_commit').parent('div').show();
        }
    });

    $('#task button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var deployment = new Piplin.Deploy();

        var environment_ids = [];

        $('.task-environment:checked').each(function() {
            environment_ids.push($(this).val());
        });

        var optional = [];
        $('.task-command:checked').each(function() {
            optional.push($(this).val());
        });

        deployment.save({
            environments:    environment_ids,
            project_id:      $('input[name="project_id"]').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val(),
            reason:          $('#task_reason').val(),
            source:          $('input[name="source"]:checked').val(),
            source_branch:   $('#task_branch').val(),
            source_tag:      $('#task_tag').val(),
            source_commit:   $('#task_commit').val(),
            optional:        optional
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('tasks.submit_success');
                Piplin.toast(msg);
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                if (typeof errors['environments'] !== 'undefined') {
                    var element = $('.task-environment');
                    var parent = element.parents('div.form-group');
                    parent.addClass('has-error');
                }

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#rollback').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var task   = button.data('task-id');
        var modal  = $(this);

        $('input[name="task_id"]', modal).val(task);
        $('#task_reason', modal).val('');
        $('.task-command', modal).prop('checked', false);
    });

    $('#rollback button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var reason = $('#task_reason', dialog).val();

        var optional = [];
        $('.task-command:checked', dialog).each(function() {
            optional.push($(this).val());
        });

        $.ajax({
            url: '/task/' + $('input[name="task_id"]', dialog).val() + '/rollback',
            method: 'POST',
            data: {
                reason: reason,
                optional: optional
            }
        }).fail(function (response){
            $('.callout-danger', dialog).show();
            var errors = response.responseJSON;

            $('.has-error', dialog).removeClass('has-error');
            $('.label-danger', dialog).remove();
            icon.removeClass().addClass('piplin piplin-save');
            $('button.close', dialog).show();
            dialog.find('input').removeAttr('disabled');
        }).done(function (data) {
            dialog.modal('hide');
            $('.callout-danger', dialog).hide();

            icon.removeClass().addClass('piplin piplin-save');
            $('button.close', dialog).show();
            dialog.find('input').removeAttr('disabled');

            Piplin.toast(trans('tasks.submit_success'));
        });
    });

    $('#task_draft').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);

        var task = button.data('task-id');

        var modal = $(this);

        $('input[name="task_id"]', modal).val(task);

        //$('form', modal).prop('action', '/task/' + task + '/task-draft');
    });

    $('#task_draft button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        $.ajax({
            url: '/task/' + $('input[name="task_id"]', dialog).val() + '/task-draft',
            method: 'POST'
        }).done(function (data) {
            dialog.modal('hide');
            $('.callout-danger', dialog).hide();

            icon.removeClass().addClass('piplin piplin-save');
            $('button.close', dialog).show();
            dialog.find('input').removeAttr('disabled');

            Piplin.toast(trans('tasks.submit_success'));
        });
    });

    $('.btn-cancel').on('click', function (event) {
        var button = $(event.currentTarget);
        var task = button.data('task-id');

        $('form#abort_' + task).trigger('submit');
    });

    var fetchingLog = false;
    $('#log').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var log_id = button.attr('id').replace('log_', '');

        var step = $('h3 span', button.parents('.box')).text();
        var modal = $(this);
        var log = $('pre', modal);
        var loader = $('#loading', modal);

        log.hide();
        loader.show();

        $('#action', modal).text(step);
        log.text('');

        fetchingLog = true;

        $.ajax({
            type: 'GET',
            url: '/log/' + log_id
        }).done(function (data) {
            var output = parseOutput(data.output ? data.output : '');

            log.html(output);

            log.show();
            loader.hide();

            Piplin.listener.on('serverlog-' + log_id + ':' + Piplin.events.OUTPUT_CHANGED, function (data) {
                if (data.log_id === parseInt(log_id)) {
                  fetchLog(log, data.log_id);
                }
            });
        }).always(function() {
            fetchingLog = false;
        });
    });

    $('#log').on('hide.bs.modal', function () {
        fetchingLog = false;
    });

    function fetchLog(element, log_id) {
        if (fetchingLog) {
            return;
        }

        fetchingLog = true;

        $.ajax({
            type: 'GET',
            url: '/log/' + log_id
        }).done(function (data) {
            var output = parseOutput(data.output ? data.output : '');
            var atBottom = false;

            if (element.scrollTop() + element.innerHeight() >= element.get(0).scrollHeight) {
                atBottom = true;
            }

            element.html(output);

            if (atBottom) {
                element.scrollTop(element.get(0).scrollHeight);
            }
        }).always(function() {
            fetchingLog = false;
        });
    }


    function parseOutput(output) {
        return output.replace(/<\/error>/g, '</span>')
            .replace(/<\/info>/g, '</span>')
            .replace(/<error>/g, '<span class="text-red">')
            .replace(/<info>/g, '<span class="text-default">');
    }

    Piplin.Deploy = Backbone.Model.extend({
        urlRoot: '/tasks'
    });

    Piplin.ServerLog = Backbone.Model.extend({
        urlRoot: '/status'
    });

    var Deployment = Backbone.Collection.extend({
        model: Piplin.ServerLog
    });

    Piplin.Deployment = new Deployment();

    Piplin.DeploymentView = Backbone.View.extend({
        el: '#app',
        $containers: [],
        events: {

        },
        initialize: function() {
            var that = this;
            $('.task-step tbody').each(function(index, element) {
                that.$containers.push({
                    step: parseInt($(element).attr('id').replace('step_', '')),
                    element: element
                })
            });

            this.listenTo(Piplin.Deployment, 'add', this.addOne);
            this.listenTo(Piplin.Deployment, 'reset', this.addAll);
            this.listenTo(Piplin.Deployment, 'remove', this.addAll);
            this.listenTo(Piplin.Deployment, 'all', this.render);

            Piplin.listener.on('serverlog:' + Piplin.events.SVRLOG_CHANGED, function (data) {
                var deployment = Piplin.Deployment.get(data.log_id);

                if (deployment) {
                    deployment.set({
                        status: data.status,
                        output: data.output,
                        runtime: data.runtime,
                        started_at: data.started_at ? data.started_at : false,
                        finished_at: data.finished_at ? data.finished_at : false
                    });
                }
            });

            Piplin.listener.on('task:' + Piplin.events.MODEL_CHANGED, function (data) {
                if (parseInt(data.model.project_id) === parseInt(Piplin.project_id)) {
                    var status_bar = $('#task_status_bar');
                    var status_data = Piplin.formatDeploymentStatus(parseInt(data.model.status));
                    
                    status_bar.attr('class', 'text-' + status_data.label_class);
                    $('i', status_bar).attr('class', 'piplin piplin-' + status_data.icon_class);
                    $('span', status_bar).text(status_data.label);

                    if (data.model.run_failure) {
                        $('#task_status').find('p').text(data.model.output);
                        $('#task_status').removeClass('hide').show();
                    } else {
                        $('#task_status').hide();
                    }
                }
            });

        },
        addOne: function (step) {
            var view = new Piplin.LogView({
                model: step
            });

            var found = _.find(this.$containers, function(element) {
                return parseInt(element.step) === parseInt(step.get('task_step_id'));
            });

            $(found.element).append(view.render().el);

        },
        addAll: function () {
            $(this.$containers).each(function (index, element) {
                element.html('');
            });

            Piplin.Commands.each(this.addOne, this);
        }
    });

    Piplin.LogView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            //'click .btn-log': 'showLog',
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#log-template').html());
        },
        render: function () {
            var data = this.model.toJSON();
            var task_status = parseInt(this.model.get('status'));

            data.label_class = 'info';
            data.icon_css = 'clock';
            data.label = trans('tasks.pending');

            if (task_status === Piplin.statuses.SVRLOG_COMPLETED) {
                data.label_class = 'success';
                data.icon_css = 'check';
                data.label = trans('tasks.completed');
            } else if (task_status === Piplin.statuses.SVRLOG_RUNNING) {
                data.label_class = 'warning';
                data.icon_css = 'load piplin-spin';
                data.label = trans('tasks.running');
            } else if (task_status === Piplin.statuses.SVRLOG_FAILED) {
                data.label_class = 'danger';
                data.icon_css = 'close';
                data.label = trans('tasks.failed');
             } else if (task_status === Piplin.statuses.SVRLOG_CANCELLED) {
                data.label_class = 'danger';
                data.icon_css = 'close';
                data.label = trans('tasks.cancelled');
            }

            data.formatted_start_time = data.started_at ? moment(data.started_at).format('HH:mm:ss') : false;
            data.formatted_end_time   = data.finished_at ? moment(data.finished_at).format('HH:mm:ss') : false;

            this.$el.removeClass().addClass('bg-' + data.label_class).html(this.template(data));

            return this;
        }
    });
})(jQuery);
(function ($) {

    $('#hook').on('show.bs.modal', function (event) {
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
            $('#hook_id').val('');
            $('#hook_name').val('');
            $('#hook_type').val('');
            $('#hook :input[id^=hook_config]').val('');
            $('#hook .hook-config input[type=checkbox]').prop('checked', true);
            $('#hook .hook-enabled input[type=checkbox]').prop('checked', true);
            $('#hook .modal-footer').hide();
            $('.hook-config').hide();
            $('.hook-enabled').hide();
            $('#hook-type').show();
            modal.find('.modal-title span').text(trans('hooks.create'));
        }
    });

    $('#hook #hook-type a.btn-app').on('click', function(event) {
        var button = $(event.currentTarget);
        var modal = $('#hook');

        if (button.attr('disabled')) {
            $('.callout-warning', modal).show();
            return;
        }

        $('.callout-warning', modal).hide();

        var type = button.data('type');
        setTitleWithIcon(type, 'create');
    });

    function setTitleWithIcon(type, action) {
        $('#hook .modal-title span').text(trans('hooks.' + action + '_' + type));

        var element = $('#hook .modal-title i').removeClass().addClass('ion');
        var icon = 'edit';

        if (type === 'slack') {
            icon = 'slack';
        } else if (type === 'dingtalk') {
            icon = 'pin';
        } else if (type === 'mail') {
            icon = 'email';
        } else if (type === 'custom') {
            icon = 'edit';
        }

        element.addClass('piplin-' + icon);

        $('#hook .modal-footer').show();
        $('.hook-config').hide();
        $('#hook-type').hide();
        $('#hook-name').show();
        $('#hook-triggers').show();
        $('.hook-enabled').show();
        $('#hook-config-' + type).show();
        $('#hook_type').val(type);
    }

    $('body').delegate('.hook-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var hook = Piplin.Hooks.get($('#model_id').val());

        hook.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('hooks.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#hook button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var hook_id = $('#hook_id').val();

        if (hook_id) {
            var hook = Piplin.Hooks.get(hook_id);
        } else {
            var hook = new Piplin.Hook();
        }

        var data = {
          config:                     null,
          name:                       $('#hook_name').val(),
          type:                       $('#hook_type').val(),
          project_id:                 parseInt($('input[name="project_id"]').val()),
          enabled:                    $('#hook_enabled').is(':checked'),
          on_deployment_success:      $('#hook_on_deployment_success').is(':checked'),
          on_deployment_failure:      $('#hook_on_deployment_failure').is(':checked')
        };

        $('#hook #hook-config-' + data.type + ' :input[id^=hook_config]').each(function(key, field) {
            var name = $(field).attr('name');

            data[name] = $(field).val();
        });

        hook.save(data, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('hooks.edit_success');
                if (!hook_id) {
                    Piplin.Hooks.add(response);
                    msg = trans('hooks.create_success');
                }
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
                        var parent = element.parent();
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

    Piplin.Hook = Backbone.Model.extend({
        urlRoot: '/hooks/' + parseInt($('input[name="project_id"]').val())
    });

    var Hooks = Backbone.Collection.extend({
        model: Piplin.Hook
    });

    Piplin.Hooks = new Hooks();

    Piplin.HooksTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#hook_list tbody');

            $('#no_hooks').show();
            $('#hook_list').hide();

            this.listenTo(Piplin.Hooks, 'add', this.addOne);
            this.listenTo(Piplin.Hooks, 'reset', this.addAll);
            this.listenTo(Piplin.Hooks, 'remove', this.addAll);
            this.listenTo(Piplin.Hooks, 'all', this.render);


            Piplin.listener.on('hook:' + Piplin.events.MODEL_CHANGED, function (data) {
                var hook = Piplin.Hooks.get(parseInt(data.model.id));

                if (hook) {
                    hook.set(data.model);
                }
            });

            Piplin.listener.on('hook:' + Piplin.events.MODEL_CREATED, function (data) {
                if (parseInt(data.model.project_id) === parseInt(Piplin.project_id)) {
                    Piplin.Hooks.add(data.model);
                }
            });

            Piplin.listener.on('hook:' + Piplin.events.MODEL_TRASHED, function (data) {
                var hook = Piplin.Hooks.get(parseInt(data.model.id));

                if (hook) {
                    Piplin.Hooks.remove(hook);
                }
            });
        },
        render: function () {
            if (Piplin.Hooks.length) {
                $('#no_hooks').hide();
                $('#hook_list').show();
            } else {
                $('#no_hooks').show();
                $('#hook_list').hide();
            }
        },
        addOne: function (hook) {
            var view = new Piplin.HookView({
                model: hook
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Hooks.each(this.addOne, this);
        }
    });

    Piplin.HookView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#hook-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.icon = 'edit';
            data.label = trans('hooks.custom');

            if (this.model.get('type') !== 'custom') {
                data.label = trans('hooks.' + this.model.get('type'));
            }

            if (this.model.get('type') === 'slack') {
                data.icon = 'slack';
            } else if (this.model.get('type') === 'dingtalk') {
                data.icon = 'pin';
            } else if (this.model.get('type') === 'mail') {
                data.icon = 'email';
            }

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            var type = this.model.get('type');

            $.each(this.model.get('config'), function(field, value) {
                $('#hook-config-' + type + ' #hook_config_' + field).val(value);
            });

            $('#hook_id').val(this.model.id);
            $('#hook_name').val(this.model.get('name'));
            $('#hook_type').val(type);
            $('#hook_enabled').prop('checked', (this.model.get('enabled') === true));
            $('#hook_on_deployment_success').prop('checked', (this.model.get('on_deployment_success') === true));
            $('#hook_on_deployment_failure').prop('checked', (this.model.get('on_deployment_failure') === true));

            setTitleWithIcon(this.model.get('type'), 'edit');
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade hook-trash');
        }
    });
})(jQuery);
(function ($) {

    // test
    var settings =  {
        placeholder: trans('members.search'),
        minimumInputLength: 1,
        width: '100%',
        ajax: {
            url: "/api/autocomplete/users",
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

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var member = Piplin.Members.get($('#model_id').val());

        member.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('members.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#member button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var member_id = $('#member_id').val();

        if (member_id) {
            var member = Piplin.Members.get(member_id);
        } else {
            var member = new Piplin.Member();
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('members.edit_success');
                if (!member_id) {
                    Piplin.Members.add(response);
                    msg = trans('members.create_success');
                }
                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    Piplin.Member = Backbone.Model.extend({
        urlRoot: '/members/' + parseInt($('input[name="project_id"]').val())
    });

    var Members = Backbone.Collection.extend({
        model: Piplin.Member
    });

    Piplin.Members = new Members();

    Piplin.MembersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#member_list tbody');

            $('#no_members').show();
            $('#member_list').hide();

            this.listenTo(Piplin.Members, 'add', this.addOne);
            this.listenTo(Piplin.Members, 'reset', this.addAll);
            this.listenTo(Piplin.Members, 'remove', this.addAll);
            this.listenTo(Piplin.Members, 'all', this.render);

            Piplin.listener.on('member:' + Piplin.events.MODEL_CHANGED, function (data) {
                var member = Piplin.Members.get(parseInt(data.model.id));

                if (member) {
                    member.set(data.model);
                }
            });

            Piplin.listener.on('member:' + Piplin.events.MODEL_CREATED, function (data) {
                if (parseInt(data.model.project_id) === parseInt(Piplin.project_id)) {
                    Piplin.Members.add(data.model);
                }
            });

            Piplin.listener.on('member:' + Piplin.events.MODEL_TRASHED, function (data) {
                var member = Piplin.Members.get(parseInt(data.model.id));

                if (member) {
                    Piplin.Members.remove(member);
                }
            });
        },
        render: function () {
            if (Piplin.Members.length) {
                $('#no_members').hide();
                $('#member_list').show();
            } else {
                $('#no_members').show();
                $('#member_list').hide();
            }
        },
        addOne: function (member) {
            var view = new Piplin.MemberView({
                model: member
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Members.each(this.addOne, this);
        }
    });

    Piplin.MemberView = Backbone.View.extend({
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
(function ($) {

    $('#project_create').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('projects.create');

        var project_id = button.data('project-id');

        if (button.hasClass('btn-edit')) {
            title = trans('projects.edit');
            $.ajax({
                type: 'POST',
                url: '/api/projects',
                data: {
                    project_id: project_id
                }
            }).done(function (data) {

                Piplin.Projects.reset(data);

                $('#project_id').val(data.id);
                $('#project_name').val(data.name);
                $('#project_repository').val(data.repository);
                $('#project_branch').val(data.branch);
                $('#project_deploy_path').val(data.deploy_path);
                $('#project_allow_other_branch').prop('checked', (data.allow_other_branch === true));
            });
        }

        modal.find('.modal-title span').text(title);
        $('.callout-danger', modal).hide();
    });

    $('#project_create button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var project_id = $('#project_id').val();

        if (project_id) {
            var project = Piplin.Projects.get(project_id);
        } else {
            var project = new Piplin.Project();
        }

        project.save({
            name:               $('#project_name').val(),
            repository:         $('#project_repository').val(),
            branch:             $('#project_branch').val(), 
            deploy_path:        $('#project_deploy_path').val(),
            allow_other_branch: $('#project_allow_other_branch').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.Projects.reset(response);
                var msg = trans('projects.edit_success');
                if (!project_id) {
                     msg = trans('projects.create_success');
                }
                Piplin.toast(msg, '', 'success');
                window.location.href = '/projects/' + response.id;
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form :input', dialog).each(function (index, element) {
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

    $('#model-trash').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('projects.delete');

        var project_id = button.data('project-id');

        if (button.hasClass('project-delete')) {
            var target = $('#model_id');
            target.val(project_id);
            target.parents('.modal').removeClass().addClass('modal fade project-trash');
            $.ajax({
                type: 'POST',
                url: '/api/projects',
                data: {
                    project_id: project_id
                }
            }).done(function (data) {
                Piplin.Projects.reset(data);
            });
        }
    });

    $('body').delegate('.project-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var project = Piplin.Projects.get($('#model_id').val());

        project.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('projects.delete_success'));
                window.location.href = '/';
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    Piplin.Project = Backbone.Model.extend({
        urlRoot: '/projects'
    });

    var Projects = Backbone.Collection.extend({
        model: Piplin.Project
    });

    Piplin.Projects = new Projects();

    $('#new_webhook').on('click', function(event) {
        var target = $(event.currentTarget);
        var project_id = target.data('project-id');
        var icon = $('i', target);
        var interval = 3000;

        if ($('.piplin-spin', target).length > 0) {
            return;
        }

        target.attr('disabled', 'disabled');

        icon.addClass('piplin-spin');
        $('#webhook').fadeOut(interval);

        $.ajax({
            type: 'GET',
            url: '/webhook/' + project_id + '/refresh'
        }).fail(function (response) {

        }).done(function (data) {
            $('#webhook').fadeIn(interval).val(data.url);
        }).always(function () {
            icon.removeClass('piplin-spin');
            target.removeAttr('disabled');
        });
    });
})(jQuery);

(function ($) {

    $('#pattern').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('patterns.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('patterns.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#pattern_id').val('');
            $('#name').val('');
            $('#copy_pattern').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.pattern-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var pattern = Piplin.Patterns.get($('#model_id').val());

        pattern.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('patterns.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#pattern button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var pattern_id = $('#pattern_id').val();

        if (pattern_id) {
            var pattern = Piplin.Patterns.get(pattern_id);
        } else {
            var pattern = new Piplin.Pattern();
        }

        pattern.save({
            name:         $('#name').val(),
            copy_pattern: $('#copy_pattern').val(),
            plan_id:      $('input[name="plan_id"]').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('patterns.edit_success');
                if (!pattern_id) {
                    Piplin.Patterns.add(response);
                    trans('patterns.create_success');
                }

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

    Piplin.Pattern = Backbone.Model.extend({
        urlRoot: '/patterns'
    });

    var Patterns = Backbone.Collection.extend({
        model: Piplin.Pattern
    });

    Piplin.Patterns = new Patterns();

    Piplin.PatternsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#pattern_list tbody');

            $('#no_patterns').show();
            $('#pattern_list').hide();

            this.listenTo(Piplin.Patterns, 'add', this.addOne);
            this.listenTo(Piplin.Patterns, 'reset', this.addAll);
            this.listenTo(Piplin.Patterns, 'remove', this.addAll);
            this.listenTo(Piplin.Patterns, 'all', this.render);

            Piplin.listener.on('pattern:' + Piplin.events.MODEL_CHANGED, function (data) {
                var share = Piplin.Patterns.get(parseInt(data.model.id));

                if (share) {
                    share.set(data.model);
                }
            });

            Piplin.listener.on('pattern:' + Piplin.events.MODEL_CREATED, function (data) {
                var plan_id = $('input[name="plan_id"]').val();
                if (parseInt(data.model.plan_id) === parseInt(plan_id)) {
                    Piplin.Patterns.add(data.model);
                }
            });

            Piplin.listener.on('pattern:' + Piplin.events.MODEL_TRASHED, function (data) {
                var share = Piplin.Patterns.get(parseInt(data.model.id));

                if (share) {
                    Piplin.Patterns.remove(share);
                }
            });
        },
        render: function () {
            if (Piplin.Patterns.length) {
                $('#no_patterns').hide();
                $('#pattern_list').show();
            } else {
                $('#no_patterns').show();
                $('#pattern_list').hide();
            }
        },
        addOne: function (pattern) {

            var view = new Piplin.PatternView({
                model: pattern
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Patterns.each(this.addOne, this);
        }
    });

    Piplin.PatternView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#pattern-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#pattern_id').val(this.model.id);
            $('#name').val(this.model.get('name'));
            $('#copy_pattern').val(this.model.get('copy_pattern'));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade pattern-trash');
        }
    });

})(jQuery);

(function ($) {

    // Stop the uploader causing errors on pages it shouldn't be used
    if ($('#upload').length === 0) {
        return;
    }

    $('#skin').on('change', function() {
        $('body').removeClass();
        $("body").addClass('skin-' + $(this).find(':selected').val());
    });

    $('#two-factor-auth').on('change', function () {

        var container = $('.auth-code');

        if ($(this).is(':checked')) {
            container.removeClass('hide');
        } else {
            container.addClass('hide');
        }
    });

    $('#request-change-email').on('click', function() {
        var box = $(this).parents('.box');
        box.children('.overlay').removeClass('hide');
        $.post('/profile/email', function(res) {
            if (res == 'success') {
                box.children('.overlay').addClass('hide');
                box.find('.help-block').removeClass('hide');
            }
        });
    });

    var cropperData = {};
    $('.avatar>img').cropper({
        aspectRatio: 1 / 1,
        preview: '.avatar-preview',
        crop: function(data) {
            cropperData.dataX = Math.round(data.x);
            cropperData.dataY = Math.round(data.y);
            cropperData.dataHeight = Math.round(data.height);
            cropperData.dataWidth = Math.round(data.width);
            cropperData.dataRotate = Math.round(data.rotate);
        },
        built: function() {
            $('#upload-overlay').addClass('hide');
        }
    });

    var uploader = new Uploader({
        trigger: '#upload',
        name: 'file',
        action: '/profile/upload',
        accept: 'image/*',
        data: {
            '_token': $('meta[name="token"]').attr('content')
        },
        multiple: false,
        change: function(){
            $('#upload-overlay').removeClass('hide');
            this.submit();
        },
        error: function(file) {
            if (file.responseJSON.file) {
                alert(file.responseJSON.file.join(''));
            } else if (file.responseJSON.error) {
                alert(file.responseJSON.error.message);
            }

            $('#upload-overlay').addClass('hide');
        },
        success: function(response) {
            if( response.message === 'success') {
                $('.avatar>img').cropper('replace', response.image);
                cropperData.path = response.path;

                $('.current-avatar-preview').addClass('hide');
                $('.avatar-preview').removeClass('hide');
                $('#save-avatar').removeClass('hide');
            }
        }
    });

    $('#save-avatar').on('click', function(){
        $('#upload-overlay').removeClass('hide');
        $('.avatar-message .alert').addClass('hide');
        $.post('/profile/avatar', cropperData).success(function(resp) {
            $('#upload-overlay').addClass('hide');
            if (resp.image) {
                $('.avatar-message .alert.alert-success').removeClass('hide');
                $('#use-gravatar').removeClass('hide');
            } else {
                $('.avatar-message .alert.alert-danger').removeClass('hide');
            }
        });
    });

    $('#use-gravatar').on('click', function () {

        $('#upload-overlay').removeClass('hide');
        $('.avatar-message .alert').addClass('hide');

        $.post('/profile/gravatar').success(function(resp) {

            $('#upload-overlay').addClass('hide');

            // if (resp.image) {
                $('.avatar-message .alert.alert-success').removeClass('hide');
                $('.avatar-preview').addClass('hide');
                $('.current-avatar-preview').removeClass('hide');
                $('.current-avatar-preview').attr('src', resp.image);
                $('#use-gravatar').addClass('hide');
                $('#avatar-save-buttons button').addClass('hide');
            // } else {
            //     $('.avatar-preview').addClass('hide');
            //     $('#use-gravatar').removeClass('hide');
            // }
        });
    });
})(jQuery);

(function ($) {

    var AUTOMATIC = 1;
    var MANUAL = 2;

    $('#link').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('links.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('links.edit');
            $('.btn-danger', modal).show();
            $('.link-environment').prop('checked', false);
            Piplin.EnvironmentLinks.each(function (environment){
                $('#link_opposite_environment_' + environment.id).prop('checked', true);
            });
        } else {
            //$('#link_id').val('');
            $('.link-environment').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    $('#link button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();


        var environment_link = new Piplin.EnvironmentLink();

        var environment_ids = [];

        $('.link-environment:checked').each(function() {
            environment_ids.push($(this).val());
        });

        //console.log(environment_link);
        //console.log(environment_ids);

        var interval = 3000;
        $('.opposite-environments').fadeOut(interval);

        environment_link.save({
            environment_id:   $('input[name="environment_id"]').val(),
            link_type:        $('#link_type').val(),
            environments:   environment_ids
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.EnvironmentLinks.reset(response);

                //Piplin.EnvironmentLinks.add(response);

                /*
                var str = [];
                $.each(response, function(index, content) {
                    str.push(content.name)
                });

                $('.opposite-environments').fadeIn(interval).html(str.join(','));
                */
                //Piplin.EnvironmentLinks.reset();
                //Piplin.EnvironmentLinks.add(response);

                Piplin.toast(trans('environments.link_success'));
            },
            error: function(model, response, options) {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });

    });

    Piplin.EnvironmentLink = Backbone.Model.extend({
        urlRoot: '/environment-links'
    });

    var EnvironmentLinks = Backbone.Collection.extend({
        model: Piplin.EnvironmentLink
    });

    Piplin.EnvironmentLinks = new EnvironmentLinks();

    Piplin.EnvironmentLinksTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#link_list tbody');

            $('#no_links').show();
            $('#link_list').hide();

            this.listenTo(Piplin.EnvironmentLinks, 'add', this.addOne);
            this.listenTo(Piplin.EnvironmentLinks, 'reset', this.addAll);
            this.listenTo(Piplin.EnvironmentLinks, 'remove', this.addAll);
            this.listenTo(Piplin.EnvironmentLinks, 'all', this.render);
        },
        render: function () {
            if (Piplin.EnvironmentLinks.length) {
                $('#no_links').hide();
                $('#link_list').show();
            } else {
                $('#no_links').show();
                $('#link_list').hide();
            }
        },
        addOne: function (link) {

            var view = new Piplin.EnvironmentLinkView({
                model: link
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.EnvironmentLinks.each(this.addOne, this);
        }
    });

    Piplin.EnvironmentLinkView = Backbone.View.extend({
        tagName:  'tr',
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);
            this.template = _.template($('#link-template').html());
        },
        render: function () {
             var data = this.model.toJSON();

             var link_type = parseInt(data.pivot.link_type);

             if (link_type == AUTOMATIC) {
                data.link_type = trans('environments.link_auto');
             } else {
                data.link_type = trans('environments.link_manual');
             }

             this.$el.html(this.template(data));

            return this;
        }
    });


})(jQuery);
(function ($) {

    // test
    var settings =  {
        placeholder: trans('cabinets.search'),
        minimumInputLength: 1,
        width: '100%',
        ajax: {
            url: "/api/autocomplete/cabinets",
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

    var cabinet_select2 = $('.environment-cabinets').select2(settings);

    // end
    $('#cabinet').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('cabinets.link');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.callout-warning', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('cabinets.edit');
            $('#cabinet_ids').parent().parent().hide();
            $('.btn-danger', modal).show();
        } else {
            $('#cabinet_ids').parent().parent().show();
            cabinet_select2.val('').trigger('change');
            modal.find('.modal-title span').text(trans('cabinets.link'));
        }

        modal.find('.modal-title span').text(title);
    });

    $('#cabinet #cabinet-type a.btn-app').on('click', function(event) {
        var button = $(event.currentTarget);
        var modal = $('#cabinet');

        if (button.attr('disabled')) {
            $('.callout-warning', modal).show();
            return;
        }

        $('.callout-warning', modal).hide();
    });

    $('body').delegate('.cabinet-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var cabinet = Piplin.Cabinets.get($('#model_id').val());

        cabinet.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('cabinets.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#cabinet button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var cabinet_id = $('#cabinet_id').val();

        if (cabinet_id) {
            var cabinet = Piplin.Cabinets.get(cabinet_id);
        } else {
            var cabinet = new Piplin.Cabinet();
        }

        var data = {
          cabinet_ids:    $('#cabinet_ids').val(),
          environment_id: parseInt($('input[name="environment_id"]').val())
        };

        cabinet.save(data, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('cabinets.edit_success');
                if (!cabinet_id) {
                    Piplin.Cabinets.add(response);
                    msg = trans('cabinets.create_success');
                }
                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    Piplin.Cabinet = Backbone.Model.extend({
        urlRoot: '/cabinets/' + parseInt($('input[name="environment_id"]').val())
    });

    var Cabinets = Backbone.Collection.extend({
        model: Piplin.Cabinet
    });

    Piplin.Cabinets = new Cabinets();

    Piplin.CabinetsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#cabinet_list tbody');

            $('#no_cabinets').show();
            $('#cabinet_list').hide();

            this.listenTo(Piplin.Cabinets, 'add', this.addOne);
            this.listenTo(Piplin.Cabinets, 'reset', this.addAll);
            this.listenTo(Piplin.Cabinets, 'remove', this.addAll);
            this.listenTo(Piplin.Cabinets, 'all', this.render);

            Piplin.listener.on('cabinet:' + Piplin.events.MODEL_CHANGED, function (data) {
                var cabinet = Piplin.Cabinets.get(parseInt(data.model.id));

                if (cabinet) {
                    cabinet.set(data.model);
                }
            });

            Piplin.listener.on('cabinet:' + Piplin.events.MODEL_TRASHED, function (data) {
                var cabinet = Piplin.Cabinets.get(parseInt(data.model.id));

                if (cabinet) {
                    Piplin.Cabinets.remove(cabinet);
                }
            });
        },
        render: function () {
            if (Piplin.Cabinets.length) {
                $('#no_cabinets').hide();
                $('#cabinet_list').show();
            } else {
                $('#no_cabinets').show();
                $('#cabinet_list').hide();
            }
        },
        addOne: function (cabinet) {
            var view = new Piplin.CabinetView({
                model: cabinet
            });

            this.$list.append(view.render().el);

            $('.server-names', this.$list).tooltip();
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Cabinets.each(this.addOne, this);
        }
    });

    Piplin.CabinetView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#cabinet-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade cabinet-trash');
        }
    });
})(jQuery);
(function ($) {

    $('.command-list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('command-id'));
            });

            $.ajax({
                url: '/commands/reorder',
                method: 'POST',
                data: {
                    commands: ids
                }
            });
        }
    });

    var editor;

    $('#command').on('hidden.bs.modal', function (event) {
        editor.destroy();
    });

    $('#command').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('commands.create');

        editor = ace.edit('command_script');
        editor.getSession().setMode('ace/mode/sh');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('commands.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#command_id').val('');
            $('#command_step').val(button.data('step'));
            $('#command_name').val('');
            editor.setValue('');
            editor.gotoLine(1);
            $('#command_user').val('');
            $('#command_optional').prop('checked', false);
            $('#command_default_on').prop('checked', false);
            $('#command_default_on_row').addClass('hide');

            $('.command-environment').prop('checked', true);
            $('.command-pattern').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.command-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var command = Piplin.Commands.get($('#model_id').val());

        command.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('commands.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#command_optional').on('change', function (event) {
        $('#command_default_on_row').addClass('hide');
        if ($(this).is(':checked') === true) {
            $('#command_default_on_row').removeClass('hide');
        }
    });

    //If no `off`, the code below will be executed twice.
    $('#command button.btn-save').off('click').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find(':input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var command_id = $('#command_id').val();
        if (command_id) {
            var command = Piplin.Commands.get(command_id);
        } else {
            var command = new Piplin.Command();
        }

        var environment_ids = [];
        $('.command-environment:checked').each(function() {
            environment_ids.push($(this).val());
        });

        var pattern_ids = [];
        $('.command-pattern:checked').each(function() {
            pattern_ids.push($(this).val());
        });

        command.save({
            name:            $('#command_name').val(),
            script:          editor.getValue(),
            user:            $('#command_user').val(),
            step:            $('#command_step').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val(),
            environments:    environment_ids,
            patterns:        pattern_ids,
            optional:        $('#command_optional').is(':checked'),
            default_on:      $('#command_default_on').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find(':input').removeAttr('disabled');

                var msg = trans('commands.edit_success');
                if (!command_id) {
                    Piplin.Commands.add(response);
                    msg = trans('commands.create_success');
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
                dialog.find(':input').removeAttr('disabled');
            }
        });
    });

    Piplin.Command = Backbone.Model.extend({
        urlRoot: '/commands',
        defaults: function() {
            return {
                order: Piplin.Commands.nextOrder()
            };
        },
        isAfter: function() {
            return (parseInt(this.get('step')) % 3 === 0);
        }
    });

    var Commands = Backbone.Collection.extend({
        model: Piplin.Command,
        comparator: 'order',
        nextOrder: function() {
            if (!this.length) {
                return 1;
            }

            return this.last().get('order') + 1;
        }
    });

    Piplin.Commands = new Commands();

    Piplin.CommandsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$beforeList = $('#commands-before .command-list tbody');
            this.$afterList = $('#commands-after .command-list tbody');

            $('.no-commands').show();
            $('.command-list').hide();

            this.listenTo(Piplin.Commands, 'add', this.addOne);
            this.listenTo(Piplin.Commands, 'reset', this.addAll);
            this.listenTo(Piplin.Commands, 'remove', this.addAll);
            this.listenTo(Piplin.Commands, 'all', this.render);

            Piplin.listener.on('command:' + Piplin.events.MODEL_CHANGED, function (data) {
                var command = Piplin.Commands.get(parseInt(data.model.id));

                if (command) {
                    command.set(data.model);
                }
            });

            Piplin.listener.on('command:' + Piplin.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                //if (data.model.targetable_type == Piplin.targetable_type && parseInt(data.model.targetable_id) === parseInt(Piplin.targetable_id)) {

                    // Make sure the command is for this action (clone, install, activate, purge)
                    if (parseInt(data.model.step) + 1 === parseInt(Piplin.command_action) || parseInt(data.model.step) - 1 === parseInt(Piplin.command_action)) {
                        Piplin.Commands.add(data.model);
                    }
                }
            });

            Piplin.listener.on('command:' + Piplin.events.MODEL_TRASHED, function (data) {
                var command = Piplin.Commands.get(parseInt(data.model.id));

                if (command) {
                    Piplin.Commands.remove(command);
                }
            });
        },
        render: function () {
            var before = Piplin.Commands.find(function(model) {
                return !model.isAfter();
            });

            if (typeof before !== 'undefined') {
                $('#commands-before .no-commands').hide();
                $('#commands-before .command-list').show();
            } else {
                $('#commands-before .no-commands').show();
                $('#commands-before .command-list').hide();
            }

            var after = Piplin.Commands.find(function(model) {
                return model.isAfter();
            });

            if (typeof after !== 'undefined') {
                $('#commands-after .no-commands').hide();
                $('#commands-after .command-list').show();
            } else {
                $('#commands-after .no-commands').show();
                $('#commands-after .command-list').hide();
            }
        },
        addOne: function (command) {
            var view = new Piplin.CommandView({
                model: command
            });

            if (command.isAfter()) {
                this.$afterList.append(view.render().el);
                if ($('tr', this.$afterList).length < 2) {
                    $('.drag-handle', this.$afterList).hide();
                } else {
                    $('.drag-handle', this.$afterList).show();
                }
            } else {
                this.$beforeList.append(view.render().el);
                if ($('tr', this.$beforeList).length < 2) {
                    $('.drag-handle', this.$beforeList).hide();
                } else {
                    $('.drag-handle', this.$beforeList).show();
                }
            }
        },
        addAll: function () {
            this.$beforeList.html('');
            this.$afterList.html('');
            Piplin.Commands.each(this.addOne, this);
        }
    });

    Piplin.CommandView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#command-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#command_id').val(this.model.id);
            $('#command_step').val(this.model.get('step'));
            $('#command_name').val(this.model.get('name'));
            $('#command_script').text(this.model.get('script'));
            $('#command_user').val(this.model.get('user'));
            $('#command_optional').prop('checked', (this.model.get('optional') === true));
            $('#command_default_on').prop('checked', (this.model.get('default_on') === true));

            $('#command_default_on_row').addClass('hide');
            if (this.model.get('optional') === true) {
                $('#command_default_on_row').removeClass('hide');
            }

            $('.command-environment').prop('checked', false);
            $(this.model.get('environments')).each(function (index, environment) {
                $('#command_environment_' + environment.id).prop('checked', true);
            });

            $('.command-pattern').prop('checked', false);
            $(this.model.get('patterns')).each(function (index, pattern) {
                $('#command_pattern_' + pattern.id).prop('checked', true);
            });
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade command-trash');
        }
    });
})(jQuery);

(function ($) {

    var editor;
    var previewfile;

    $('#configfile, #view-configfile').on('hidden.bs.modal', function (event) {
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
            'click .btn-view': 'view'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#configfiles-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        view: function() {
            previewfile = this.model.get('path');
            $('#preview-content').text(this.model.get('content'));
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

(function ($) {

    //Fix me please
    var FINISHED     = 0;
    var PENDING      = 1;
    var RUNNING    = 2;
    var FAILED       = 3;
    var NOT_DEPLOYED = 4;

    $('#environment_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('environment-id'));
            });

            $.ajax({
                url: '/environments/reorder',
                method: 'POST',
                data: {
                    environments: ids
                }
            });
        }
    });

    $('#environment').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('environments.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();
        $('#add-environment-command', modal).hide();

        if (button.hasClass('btn-edit')) {
            title = trans('environments.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#environment_id').val('');
            $('#environment_name').val('');
            $('#environment_description').val('');
            $('#environment_default_on').prop('checked', true);
            $('#add-environment-command', modal).show();
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.environment-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var environment = Piplin.Environments.get($('#model_id').val());

        environment.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('environments.delete_success'));
            },
            error: function() {
               icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#environment button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var environment_id = $('#environment_id').val();

        if (environment_id) {
            var environment = Piplin.Environments.get(environment_id);
        } else {
            var environment = new Piplin.Environment();
        }

        environment.save({
            name:            $('#environment_name').val(),
            description:     $('#environment_description').val(),
            default_on:      $('#environment_default_on').is(':checked'),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val(),
            add_commands:    $('#environment_commands').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('environments.edit_success');
                if (!environment_id) {
                    Piplin.Environments.add(response);
                    msg = trans('environments.create_success');
                }
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

    Piplin.Environment = Backbone.Model.extend({
        urlRoot: '/environments',
        initialize: function() {

        }
    });

    var Environments = Backbone.Collection.extend({
        model: Piplin.Environment
    });

    Piplin.Environments = new Environments();

    Piplin.EnvironmentsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#environment_list tbody');

            $('#no_environments').show();
            $('#environment_list').hide();

            this.listenTo(Piplin.Environments, 'add', this.addOne);
            this.listenTo(Piplin.Environments, 'reset', this.addAll);
            this.listenTo(Piplin.Environments, 'remove', this.addAll);
            this.listenTo(Piplin.Environments, 'all', this.render);

            Piplin.listener.on('environment:' + Piplin.events.MODEL_CHANGED, function (data) {
                $('#environment_' + data.model.id).html(data.model.name);

                var environment = Piplin.Environments.get(parseInt(data.model.id));

                if (environment) {
                    environment.set(data.model);
                }
            });

            Piplin.listener.on('environment:' + Piplin.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    Piplin.Environments.add(data.model);
                }
            });

            Piplin.listener.on('environment:' + Piplin.events.MODEL_TRASHED, function (data) {
                var environment = Piplin.Environments.get(parseInt(data.model.id));

                if (environment) {
                    Piplin.Environments.remove(environment);
                }
            });
        },
        render: function () {
            if (Piplin.Environments.length) {
                $('#no_environments').hide();
                $('#environment_list').show();
            } else {
                $('#no_environments').show();
                $('#environment_list').hide();
            }
        },
        addOne: function (environment) {

            var view = new Piplin.EnvironmentView({
                model: environment
            });

            this.$list.append(view.render().el);

            $('.server-names', this.$list).tooltip();
            
            if (Piplin.Environments.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Environments.each(this.addOne, this);
        }
    });

    Piplin.EnvironmentView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#environment-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            var parse_data = Piplin.formatProjectStatus(parseInt(this.model.get('status')));
            data = $.extend(data, parse_data);

            data.last_run = data.last_run != null ? moment(data.last_run).fromNow() : trans('app.never');

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#environment_id').val(this.model.id);
            $('#environment_name').val(this.model.get('name'));
            $('#environment_description').val(this.model.get('description'));
            $('#environment_default_on').prop('checked', (this.model.get('default_on') === true));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade environment-trash');
        }
    });
})(jQuery);

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

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file = Piplin.SharedFiles.get($('#model_id').val());

        file.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('sharedFiles.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#sharedfile button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file_id = $('#sharedfile_id').val();

        if (file_id) {
            var file = Piplin.SharedFiles.get(file_id);
        } else {
            var file = new Piplin.SharedFile();
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('sharedFiles.edit_success');
                if (!file_id) {
                    Piplin.SharedFiles.add(response);
                    trans('sharedFiles.create_success');
                }

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

    Piplin.SharedFile = Backbone.Model.extend({
        urlRoot: '/shared-files'
    });

    var SharedFiles = Backbone.Collection.extend({
        model: Piplin.SharedFile
    });

    Piplin.SharedFiles = new SharedFiles();

    Piplin.SharedFilesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#sharedfile_list tbody');

            $('#no_sharedfiles').show();
            $('#sharedfile_list').hide();

            this.listenTo(Piplin.SharedFiles, 'add', this.addOne);
            this.listenTo(Piplin.SharedFiles, 'reset', this.addAll);
            this.listenTo(Piplin.SharedFiles, 'remove', this.addAll);
            this.listenTo(Piplin.SharedFiles, 'all', this.render);

            Piplin.listener.on('sharedfile:' + Piplin.events.MODEL_CHANGED, function (data) {
                var share = Piplin.SharedFiles.get(parseInt(data.model.id));

                if (share) {
                    share.set(data.model);
                }
            });

            Piplin.listener.on('sharedfile:' + Piplin.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    Piplin.SharedFiles.add(data.model);
                }
            });

            Piplin.listener.on('sharedfile:' + Piplin.events.MODEL_TRASHED, function (data) {
                var share = Piplin.SharedFiles.get(parseInt(data.model.id));

                if (share) {
                    Piplin.SharedFiles.remove(share);
                }
            });
        },
        render: function () {
            if (Piplin.SharedFiles.length) {
                $('#no_sharedfiles').hide();
                $('#sharedfile_list').show();
            } else {
                $('#no_sharedfiles').show();
                $('#sharedfile_list').hide();
            }
        },
        addOne: function (file) {

            var view = new Piplin.SharedFileView({
                model: file
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.SharedFiles.each(this.addOne, this);
        }
    });

    Piplin.SharedFileView = Backbone.View.extend({
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

(function ($) {

    $('#variable').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('variables.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('variables.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#variable_id').val('');
            $('#variable_name').val('');
            $('#variable_value').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.variable-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var variable = Piplin.Variables.get($('#model_id').val());

        variable.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('variables.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#variable button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var variable_id = $('#variable_id').val();

        if (variable_id) {
            var variable = Piplin.Variables.get(variable_id);
        } else {
            var variable = new Piplin.Variable();
        }

        variable.save({
            name:            $('#variable_name').val(),
            value:           $('#variable_value').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('variables.edit_success');
                if (!variable_id) {
                    Piplin.Variables.add(response);
                    msg = trans('variables.create_success');
                }
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

    Piplin.Variable = Backbone.Model.extend({
        urlRoot: '/variables',
        initialize: function() {

        }
    });

    var Variables = Backbone.Collection.extend({
        model: Piplin.Variable
    });

    Piplin.Variables = new Variables();

    Piplin.VariablesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#variable_list tbody');

            $('#no_variables').show();
            $('#variable_list').hide();

            this.listenTo(Piplin.Variables, 'add', this.addOne);
            this.listenTo(Piplin.Variables, 'reset', this.addAll);
            this.listenTo(Piplin.Variables, 'remove', this.addAll);
            this.listenTo(Piplin.Variables, 'all', this.render);

            Piplin.listener.on('variable:' + Piplin.events.MODEL_CHANGED, function (data) {
                $('#variable_' + data.model.id).html(data.model.name);

                var variable = Piplin.Variables.get(parseInt(data.model.id));

                if (variable) {
                    variable.set(data.model);
                }
            });

            Piplin.listener.on('variable:' + Piplin.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    Piplin.Variables.add(data.model);
                }
            });

            Piplin.listener.on('variable:' + Piplin.events.MODEL_TRASHED, function (data) {
                var variable = Piplin.Variables.get(parseInt(data.model.id));

                if (variable) {
                    Piplin.Variables.remove(variable);
                }
            });
        },
        render: function () {
            if (Piplin.Variables.length) {
                $('#no_variables').hide();
                $('#variable_list').show();
            } else {
                $('#no_variables').show();
                $('#variable_list').hide();
            }
        },
        addOne: function (variable) {

            var view = new Piplin.VariableView({
                model: variable
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Variables.each(this.addOne, this);
        }
    });

    Piplin.VariableView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#variable-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#variable_id').val(this.model.id);
            $('#variable_name').val(this.model.get('name'));
            $('#variable_value').val(this.model.get('value'));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade variable-trash');
        }
    });
})(jQuery);

(function ($) {

    var SUCCESSFUL = 0;
    var UNTESTED   = 1;
    var FAILED     = 2;
    var TESTING    = 3;

    $('#server_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('server-id'));
            });

            $.ajax({
                url: '/servers/reorder',
                method: 'POST',
                data: {
                    servers: ids
                }
            });
        }
    });

    $('#server').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('servers.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('servers.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#server_id').val('');
            $('#server_name').val('');
            $('#server_enabled').prop('checked', true);
            $('#server_address').val('');
            $('#server_port').val('22');
            $('#server_user').val('');
            $('#server_targetable_id').val($("#server_targetable_id option:selected").val());
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.server-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var server = Piplin.Servers.get($('#model_id').val());

        server.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('servers.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#server button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var server_id = $('#server_id').val();

        if (server_id) {
            var server = Piplin.Servers.get(server_id);
        } else {
            var server = new Piplin.Server();
        }

        server.save({
            name:            $('#server_name').val(),
            ip_address:      $('#server_address').val(),
            enabled:         $('#server_enabled').is(':checked'),
            port:            $('#server_port').val(),
            user:            $('#server_user').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   parseInt($('#server_targetable_id').val())
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('servers.edit_success');
                if (!server_id) {
                    Piplin.Servers.add(response);
                    msg = trans('servers.create_success');
                }
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


    Piplin.Server = Backbone.Model.extend({
        urlRoot: '/servers'
    });

    var Servers = Backbone.Collection.extend({
        model: Piplin.Server,
        comparator: function(serverA, serverB) {
            if (serverA.get('name') > serverB.get('name')) {
                return -1; // before
            } else if (serverA.get('name') < serverB.get('name')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    Piplin.Servers = new Servers();

    Piplin.ServersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#server_list tbody');

            $('#no_servers').show();
            $('#server_list').hide();

            this.listenTo(Piplin.Servers, 'add', this.addOne);
            this.listenTo(Piplin.Servers, 'reset', this.addAll);
            this.listenTo(Piplin.Servers, 'remove', this.addAll);
            this.listenTo(Piplin.Servers, 'all', this.render);

            Piplin.listener.on('server:' + Piplin.events.MODEL_CHANGED, function (data) {
                var server = Piplin.Servers.get(parseInt(data.model.id));

                if (server) {
                    // Fix me - targetable_type
                    if(Piplin.targetable_id == data.model.targetable_id) {
                        server.set(data.model);
                    } else {
                        Piplin.Servers.remove(server);
                    }
                }
            });

            Piplin.listener.on('server:' + Piplin.events.MODEL_CREATED, function (data) {
                if (parseInt(data.model.targetable_id) === parseInt(Piplin.targetable_id)) {
                    Piplin.Servers.add(data.model);
                }
            });

            Piplin.listener.on('server:' + Piplin.events.MODEL_TRASHED, function (data) {
                var server = Piplin.Servers.get(parseInt(data.model.id));

                if (server) {
                    Piplin.Servers.remove(server);
                }
            });
        },
        render: function () {
            if (Piplin.Servers.length) {
                $('#no_servers').hide();
                $('#server_list').show();
            } else {
                $('#no_servers').show();
                $('#server_list').hide();
            }
        },
        addOne: function (server) {

            var view = new Piplin.ServerView({
                model: server
            });

            this.$list.append(view.render().el);

            if (Piplin.Servers.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Servers.each(this.addOne, this);
        }
    });

    Piplin.ServerView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-test': 'testConnection',
            'click .btn-show': 'showLog',
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#server-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.status_css = 'orange';
            data.icon_css   = 'circle';
            data.status     = trans('servers.untested');

            if (parseInt(this.model.get('status')) === SUCCESSFUL) {
                data.status_css = 'success';
                data.status     = trans('servers.successful');
            } else if (parseInt(this.model.get('status')) === TESTING) {
                data.status_css = 'purple';
                data.icon_css   = 'load piplin-spin';
                data.status     = trans('servers.testing');
            } else if (parseInt(this.model.get('status')) === FAILED) {
                data.status_css = 'danger';
                data.status     = trans('servers.failed');
            }

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#server_id').val(this.model.id);
            $('#server_name').val(this.model.get('name'));
            $('#server_targetable_id')
                .select2(Piplin.select2_options)
                .val(this.model.get('targetable_id'))
                .trigger('change');
            $('#server_enabled').prop('checked', (this.model.get('enabled') === true));
            $('#server_address').val(this.model.get('ip_address'));
            $('#server_port').val(this.model.get('port'));
            $('#server_user').val(this.model.get('user'));
        },
        showLog: function() {
            var data = this.model.toJSON();

            $('#log pre').html(data.output);
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade server-trash');
        },
        testConnection: function() {
            if (parseInt(this.model.get('status')) === TESTING) {
                return;
            }

            this.model.set({
                status: TESTING
            });

            var that = this;
            $.ajax({
                type: 'GET',
                url: this.model.urlRoot + '/' + this.model.id + '/test'
            }).fail(function (response) {
                that.model.set({
                    status: FAILED
                });
            });

        }
    });
})(jQuery);
