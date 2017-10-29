(function ($) {

    $('#deploy button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('fixhub fixhub-load fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var deployment = new Fixhub.Deploy();

        var environment_ids = [];

        $('.deployment-environment:checked').each(function() {
            environment_ids.push($(this).val());
        });

        var optional = [];
        $('.deployment-command:checked').each(function() {
            optional.push($(this).val());
        });

        deployment.save({
            environments: environment_ids,
            project_id: $('input[name="project_id"]').val(),
            reason: $('#deployment_reason').val(),
            source: $('input[name="source"]:checked').val(),
            source_branch: $('#deployment_branch').val(),
            source_tag: $('#deployment_tag').val(),
            source_commit: $('#deployment_commit').val(),
            optional: optional
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('fixhub fixhub-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('deployments.submit_success');
                Fixhub.toast(msg);
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                if (typeof errors['environments'] !== 'undefined') {
                    var element = $('.deployment-environment');
                    var parent = element.parents('div.form-group');
                    parent.addClass('has-error');
                }

                icon.removeClass().addClass('fixhub fixhub-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#rollback').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var deployment = button.data('deployment-id');
        var modal = $(this);

        $('input[name="deployment_id"]', modal).val(deployment);
        $('#deployment_reason', modal).val('');
        $('.deployment-command', modal).prop('checked', false);
    });

    $('#rollback button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('fixhub fixhub-load fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var reason = $('#deployment_reason', dialog).val();

        var optional = [];
        $('.deployment-command:checked', dialog).each(function() {
            optional.push($(this).val());
        });

        $.ajax({
            url: '/deployment/' + $('input[name="deployment_id"]', dialog).val() + '/rollback',
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
            icon.removeClass().addClass('fixhub fixhub-save');
            $('button.close', dialog).show();
            dialog.find('input').removeAttr('disabled');
        }).done(function (data) {
            dialog.modal('hide');
            $('.callout-danger', dialog).hide();

            icon.removeClass().addClass('fixhub fixhub-save');
            $('button.close', dialog).show();
            dialog.find('input').removeAttr('disabled');

            Fixhub.toast(trans('deployments.submit_success'));
        });
    });

    $('#deploy_draft').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);

        var deployment = button.data('deployment-id');

        var modal = $(this);

        $('input[name="deployment_id"]', modal).val(deployment);

        //$('form', modal).prop('action', '/deployment/' + deployment + '/deploy-draft');
    });

    $('#deploy_draft button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('fixhub fixhub-load fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        $.ajax({
            url: '/deployment/' + $('input[name="deployment_id"]', dialog).val() + '/deploy-draft',
            method: 'POST'
        }).done(function (data) {
            dialog.modal('hide');
            $('.callout-danger', dialog).hide();

            icon.removeClass().addClass('fixhub fixhub-save');
            $('button.close', dialog).show();
            dialog.find('input').removeAttr('disabled');

            Fixhub.toast(trans('deployments.submit_success'));
        });
    });

    $('.btn-cancel').on('click', function (event) {
        var button = $(event.currentTarget);
        var deployment = button.data('deployment-id');

        $('form#abort_' + deployment).trigger('submit');
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

            Fixhub.listener.on('serverlog-' + log_id + ':' + Fixhub.events.OUTPUT_CHANGED, function (data) {
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

    Fixhub.Deploy = Backbone.Model.extend({
        urlRoot: '/deployments'
    });

    Fixhub.ServerLog = Backbone.Model.extend({
        urlRoot: '/status'
    });

    var Deployment = Backbone.Collection.extend({
        model: Fixhub.ServerLog
    });

    Fixhub.Deployment = new Deployment();

    Fixhub.DeploymentView = Backbone.View.extend({
        el: '#app',
        $containers: [],
        events: {

        },
        initialize: function() {
            var that = this;
            $('.deploy-step tbody').each(function(index, element) {
                that.$containers.push({
                    step: parseInt($(element).attr('id').replace('step_', '')),
                    element: element
                })
            });

            this.listenTo(Fixhub.Deployment, 'add', this.addOne);
            this.listenTo(Fixhub.Deployment, 'reset', this.addAll);
            this.listenTo(Fixhub.Deployment, 'remove', this.addAll);
            this.listenTo(Fixhub.Deployment, 'all', this.render);

            Fixhub.listener.on('serverlog:' + Fixhub.events.SVRLOG_CHANGED, function (data) {
                var deployment = Fixhub.Deployment.get(data.log_id);

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

            Fixhub.listener.on('deployment:' + Fixhub.events.MODEL_CHANGED, function (data) {
                if (parseInt(data.model.project_id) === parseInt(Fixhub.project_id)) {
                    var status_bar = $('#deploy_status_bar');
                    var status_data = Fixhub.formatDeploymentStatus(parseInt(data.model.status));
                    
                    status_bar.attr('class', 'text-' + status_data.label_class);
                    $('i', status_bar).attr('class', 'fixhub fixhub-' + status_data.icon_class);
                    $('span', status_bar).text(status_data.label);

                    if (data.model.deploy_failure) {
                        $('#deploy_status').find('p').text(data.model.output);
                        $('#deploy_status').removeClass('hide').show();
                    } else {
                        $('#deploy_status').hide();
                    }
                }
            });

        },
        addOne: function (step) {
            var view = new Fixhub.LogView({
                model: step
            });

            var found = _.find(this.$containers, function(element) {
                return parseInt(element.step) === parseInt(step.get('deploy_step_id'));
            });

            $(found.element).append(view.render().el);

        },
        addAll: function () {
            $(this.$containers).each(function (index, element) {
                element.html('');
            });

            Fixhub.Commands.each(this.addOne, this);
        }
    });

    Fixhub.LogView = Backbone.View.extend({
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
            var deploy_status = parseInt(this.model.get('status'));

            data.label_class = 'info';
            data.icon_css = 'clock';
            data.label = trans('deployments.pending');

            if (deploy_status === Fixhub.statuses.SVRLOG_COMPLETED) {
                data.label_class = 'success';
                data.icon_css = 'check';
                data.label = trans('deployments.completed');
            } else if (deploy_status === Fixhub.statuses.SVRLOG_RUNNING) {
                data.label_class = 'warning';
                data.icon_css = 'load fixhub-spin';
                data.label = trans('deployments.running');
            } else if (deploy_status === Fixhub.statuses.SVRLOG_FAILED) {
                data.label_class = 'danger';
                data.icon_css = 'close';
                data.label = trans('deployments.failed');
             } else if (deploy_status === Fixhub.statuses.SVRLOG_CANCELLED) {
                data.label_class = 'danger';
                data.icon_css = 'close';
                data.label = trans('deployments.cancelled');
            }

            data.formatted_start_time = data.started_at ? moment(data.started_at).format('HH:mm:ss') : false;
            data.formatted_end_time   = data.finished_at ? moment(data.finished_at).format('HH:mm:ss') : false;

            this.$el.removeClass().addClass('bg-' + data.label_class).html(this.template(data));

            return this;
        }
    });
})(jQuery);