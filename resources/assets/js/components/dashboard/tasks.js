(function ($) {

    $('#task').on('show.bs.modal', function (event) {
        var modal = $(this);
        $('.callout-danger', modal).hide();
        var targetable_type = $('input[name="targetable_type"]').val();

        if (new RegExp("BuildPlan$").test(targetable_type)) {
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

        var task = new Piplin.Deploy();

        var environment_ids = [];

        $('.task-environment:checked').each(function() {
            environment_ids.push($(this).val());
        });

        var optional = [];
        $('.task-command:checked').each(function() {
            optional.push($(this).val());
        });

        task.save({
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

    var Task = Backbone.Collection.extend({
        model: Piplin.ServerLog
    });

    Piplin.Task = new Task();

    Piplin.TaskView = Backbone.View.extend({
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

            this.listenTo(Piplin.Task, 'add', this.addOne);
            this.listenTo(Piplin.Task, 'reset', this.addAll);
            this.listenTo(Piplin.Task, 'remove', this.addAll);
            this.listenTo(Piplin.Task, 'all', this.render);

            Piplin.listener.on('serverlog:' + Piplin.events.SVRLOG_CHANGED, function (data) {
                var deployment = Piplin.Task.get(data.log_id);

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
                    var status_data = Piplin.formatTaskStatus(parseInt(data.model.status));
                    
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