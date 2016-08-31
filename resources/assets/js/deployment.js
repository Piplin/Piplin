var app = app || {};

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

        $.ajax({
            type: 'GET',
            url: '/log/' + log_id
        }).done(function (data) {
            var output = data.output;
            // FIXME: There has to be a cleaner way to do this surely?
            output = output.replace(/<\/error>/g, '</span>')
            output = output.replace(/<\/info>/g, '</span>');
            output = output.replace(/<error>/g, '<span class="text-red">')
            output = output.replace(/<info>/g, '<span class="text-default">');

            log.html(output);

            log.show();
            loader.hide();
        }).fail(function() {

        }).always(function() {

        });
    });

    app.ServerLog = Backbone.Model.extend({
        urlRoot: '/status'
    });

    var Deployment = Backbone.Collection.extend({
        model: app.ServerLog
    });

    app.Deployment = new Deployment();

    app.DeploymentView = Backbone.View.extend({
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

            this.listenTo(app.Deployment, 'add', this.addOne);
            this.listenTo(app.Deployment, 'reset', this.addAll);
            this.listenTo(app.Deployment, 'remove', this.addAll);
            this.listenTo(app.Deployment, 'all', this.render);

            app.listener.on('serverlog:Fixhub\\Bus\\Events\\ServerLogChanged', function (data) {
                var deployment = app.Deployment.get(data.log_id);

                if (deployment) {
                    deployment.set({
                        status: data.status,
                        output: data.output,
                        runtime: data.runtime,
                        started_at: data.started_at ? data.started_at : false,
                        finished_at: data.finished_at ? data.finished_at : false
                    });

                    // FIXME: If cancelled update all other deployments straight away
                    // FIXME: If completed fake making the next model "running" so it looks responsive
                }
            });

            app.listener.on('deployment:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                if (parseInt(data.model.project_id) === parseInt(app.project_id)) {
                    if (data.model.repo_failure) {
                        $('#repository_error').show();
                    }
                }
            });

        },
        addOne: function (step) {
            var view = new app.LogView({
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

            app.Commands.each(this.addOne, this);
        }
    });

    app.LogView = Backbone.View.extend({
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
                data.icon_css = 'alert';

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
