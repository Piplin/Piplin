(function ($) {
    var COMPLETED = 0;
    var PENDING   = 1;
    var RUNNING   = 2;
    var FAILED    = 3;
    var CANCELLED = 4;

    $('#redeploy').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);

        var deployment = button.data('deployment-id');

        var tmp = button.data('optional-commands') + '';
        var commands = tmp.split(',');

        if (tmp.length > 0) {
            commands = $.map(commands, function(value) {
                return parseInt(value, 10);
            });
        } else {
            commands = [];
        }

        var modal = $(this);

        $('form', modal).prop('action', '/deployment/' + deployment + '/rollback');

        $('input:checkbox', modal).each(function (index, element) {
            var input = $(element);

            input.prop('checked', false);
            if ($.inArray(parseInt(input.val(), 10), commands) != -1) {
                input.prop('checked', true);
            }
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

            Fixhub.listener.on('serverlog-' + log_id + ':Fixhub\\Bus\\Events\\ServerOutputChangedEvent', function (data) {
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

            Fixhub.listener.on('serverlog:Fixhub\\Bus\\Events\\ServerLogChangedEvent', function (data) {
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

            Fixhub.listener.on('deployment:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                if (parseInt(data.model.project_id) === parseInt(Fixhub.project_id)) {
                    var status_bar = $('#deploy_status_bar');
                    var status_data = Fixhub.parseDeploymentStatus(parseInt(data.model.status));
                    
                    status_bar.attr('class', 'label label-' + status_data.label_class);
                    $('i', status_bar).attr('class', 'ion ion-' + status_data.icon_class);
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

            data.status_css = 'info';
            data.icon_css = 'clock';
            data.status = trans('deployments.pending');

            if (parseInt(this.model.get('status')) === COMPLETED) {
                data.status_css = 'success';
                data.icon_css = 'checkmark-round';
                data.status = trans('deployments.completed');
            } else if (parseInt(this.model.get('status')) === RUNNING) {
                data.status_css = 'warning';
                data.icon_css = 'load-c fixhub-spin';
                data.status = trans('deployments.running');
            } else if (parseInt(this.model.get('status')) === FAILED || parseInt(this.model.get('status')) === CANCELLED) {
                data.status_css = 'danger';
                data.icon_css = 'close-round';

                data.status = trans('deployments.failed');
                if (parseInt(this.model.get('status')) === CANCELLED) {
                    data.status = trans('deployments.cancelled');
                }
            }

            data.formatted_start_time = data.started_at ? moment(data.started_at).format('HH:mm:ss') : false;
            data.formatted_end_time   = data.finished_at ? moment(data.finished_at).format('HH:mm:ss') : false;

            this.$el.html(this.template(data));

            return this;
        }
    });
})(jQuery);