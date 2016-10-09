$.ajaxPrefilter(function(options, originalOptions, jqXHR) {
    jqXHR.setRequestHeader('X-CSRF-Token', $('meta[name="token"]').attr('content'));
});

var app = app || {};

toastr.options.closeButton = true;
toastr.options.progressBar = true;
toastr.options.preventDuplicates = true;
toastr.options.closeMethod = 'fadeOut';
toastr.options.closeDuration = 3000;
toastr.options.closeEasing = 'swing';
toastr.options.positionClass = 'toast-bottom-right';
toastr.options.timeOut = 5000;
toastr.options.extendedTimeOut = 7000;

(function ($) {

    // Don't need to try and connect to the web socket when not logged in
    if (window.location.href.match(/login|password/) != null) {
        return;
    }

    Lang.setLocale($('meta[name="locale"]').attr('content'));

    $('[data-toggle="tooltip"]').tooltip();

    var FINISHED     = 0;
    var PENDING      = 1;
    var DEPLOYING    = 2;
    var FAILED       = 3;
    var NOT_DEPLOYED = 4;

    var DEPLOYMENT_COMPLETED = 0;
    var DEPLOYMENT_PENDING   = 1;
    var DEPLOYMENT_DEPLOYING = 2;
    var DEPLOYMENT_FAILED    = 3;
    var DEPLOYMENT_ERRORS    = 4;
    var DEPLOYMENT_CANCELLED = 5;

    app.project_id = app.project_id || null;

    app.listener = io.connect($('meta[name="socket_url"]').attr('content'), {
        query: 'jwt=' + $('meta[name="jwt"]').attr('content')
    });

    app.connection_error = false;

    app.listener.on('connect_error', function(error) {
        if (!app.connection_error) {
            $('#socket_offline').show();
        }

        app.connection_error = true;
    });

    app.listener.on('connect', function() {
        $('#socket_offline').hide();
        app.connection_error = false;
    });

    app.listener.on('reconnect', function() {
        $('#socket_offline').hide();
        app.connection_error = false;
    });

    // Navbar deployment status
    // FIXME: Convert these menus to backbone
    // FIXME: Convert the project and deployments to backbone
    // TODO: Update the timeline
    app.listener.on('deployment:Fixhub\\Bus\\Events\\ModelChanged', function (data) {

        // Update todo bar
        updateTodoBar(data);

        //var project = $('#project_' + data.model.project_id);

        if ($('#timeline').length > 0) {
            updateTimeline();
        }

        var deployment  = $('#deployment_' + data.model.id);

        if (deployment.length > 0) {

            $('td:nth-child(5)', deployment).text(data.model.committer);

            if (data.model.commit_url) {
                $('td:nth-child(6)', deployment).html('<a href="' + data.model.commit_url + '" target="_blank">' + data.model.short_commit + '</a>');
            } else {
                $('td:nth-child(6)', deployment).text(data.model.short_commit);
            }

            var icon_class = 'clock-o';
            var label_class = 'info';
            var label = trans('deployments.pending');
            var done = false;
            var success = false;

            data.model.status = parseInt(data.model.status);
            var status = $('td:nth-child(8) span.label', deployment);

            if (data.model.status === DEPLOYMENT_COMPLETED) {
                icon_class = 'checkmark-round';
                label_class = 'success';
                label = trans('deployments.completed');
                done = true;
                success = true;
            } else if (data.model.status === DEPLOYMENT_DEPLOYING) {
                icon_class = 'load-c fixhub-spin';
                label_class = 'warning';
                label = trans('deployments.running');
            } else if (data.model.status === DEPLOYMENT_FAILED) {
                icon_class = 'alert';
                label_class = 'danger';
                label = trans('deployments.failed');
                done = true;
            } else if (data.model.status === DEPLOYMENT_ERRORS) {
                icon_class = 'alert';
                label_class = 'success';
                label = trans('deployments.completed_with_errors');
                done = true;
                success = true;
            } else if (data.model.status === DEPLOYMENT_CANCELLED) {
                icon_class = 'alert';
                label_class = 'danger';
                label = trans('deployments.cancelled');
                done = true;
            }

            if (done) {
                $('button#deploy_project:disabled').removeAttr('disabled');
                $('td:nth-child(9) a.btn-cancel', deployment).remove();

                if (success) {
                    $('button.btn-rollback').removeClass('hide');
                }
            }

            status.attr('class', 'label label-' + label_class)
            $('i', status).attr('class', 'ion ion-' + icon_class);
            $('span', status).text(label);
        //} else if ($('#timeline').length === 0) { // Don't show on dashboard
            // FIXME: Also don't show if viewing the deployment, or the project the deployment is for
        } else {
            var toast_title = trans('dashboard.deployment_number', {
                'id': data.model.id
            });

            if (data.model.status === DEPLOYMENT_COMPLETED) {
                toastr.success(toast_title + ' - ' + trans('deployments.completed'), data.model.project_name);
            } else if (data.model.status === DEPLOYMENT_FAILED) {
                toastr.error(toast_title + ' - ' + trans('deployments.failed'), data.model.project_name);
            } else if (data.model.status === DEPLOYMENT_ERRORS) {
                toastr.warning(toast_title + ' - ' + trans('deployments.completed_with_errors'), data.model.project_name);
            } // FIXME: Add cancelled
        }
    });

    app.listener.on('project:Fixhub\\Bus\\Events\\ModelChanged', function (data) {

        var project = $('#project_' + data.model.id);

        if (project.length > 0) {

            var icon_class = 'question-circle';
            var label_class = 'primary';
            var label = trans('projects.not_deployed');

            data.model.status = parseInt(data.model.status);
            var status = $('td:nth-child(4) span.label', project);

            if (data.model.status === FINISHED) {
                icon_class = 'checkmark-round';
                label_class = 'success';
                label = trans('projects.finished');
            } else if (data.model.status === DEPLOYING) {
                icon_class = 'load-c fixhub-spin';
                label_class = 'warning';
                label = trans('projects.deploying');
            } else if (data.model.status === FAILED) {
                icon_class = 'alert';
                label_class = 'danger';
                label = trans('projects.failed');
            } else if (data.model.status === PENDING) {
                icon_class = 'clock';
                label_class = 'info';
                label = trans('projects.pending');
            }

            $('td:first a', project).text(data.model.name);
            $('td:nth-child(3)', project).text(moment(data.model.last_run).format('MM-DD HH:mm'));
            status.attr('class', 'label label-' + label_class)
            $('i', status).attr('class', 'ion ion-' + icon_class);
            $('span', status).text(label);
        }
    });

    app.listener.on('project:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {

        if (parseInt(data.model.id) === parseInt(app.project_id)) {
            window.location.href = '/';
        }
    });

    function updateTimeline() {
        $.ajax({
            type: 'GET',
            url: '/timeline'
        }).success(function (response) {
            $('#timeline').html(response);
        });
    }

    function updateTodoBar(data) {

        data.model.time = moment(data.model.started_at).format('HH:mm:ss');
        data.model.url = '/deployment/' + data.model.id;

        $('#deployment_info_' + data.model.id).remove();

        var template = _.template($('#deployment-list-template').html());
        var html = template(data.model);

        if (data.model.status === DEPLOYMENT_PENDING) {
            $('.pending_menu').append(html);
        }
        else if (data.model.status === DEPLOYMENT_DEPLOYING) {
            $('.deploying_menu').append(html);
        }

        var pending = $('.pending_menu li').length;
        var deploying = $('.deploying_menu li').length;
        var todo_count = pending + deploying;

        if(todo_count > 0) {
            $('#todo_menu span.label').html(todo_count);
            $('#todo_menu .dropdown-toggle i.ion').addClass('text-danger');
        } else {
            $('#todo_menu span.label').html('');
            $('#todo_menu .dropdown-toggle i.ion').removeClass('text-danger')
        }

        if(pending > 0) {
            $('#todo_menu span.label').addClass('label-info');
            $('.pending_header i').addClass('fixhub-spin');
        } else {
            $('#todo_menu span.label').removeClass('label-info');
            $('.pending_header i').removeClass('fixhub-spin');
        }

        if(deploying > 0) {
            $('#todo_menu span.label').addClass('label-success');
            $('.deploying_header i').addClass('fixhub-spin');
        } else {
            $('#todo_menu span.label').removeClass('label-success');
            $('.deploying_header i').removeClass('fixhub-spin');
        }

        if(deploying > 0 && pending > 0) {
            $('#todo_menu span.label').removeClass('label-success').removeClass('label-info').addClass('label-warning');
        }

        var pending_label = Lang.choice('dashboard.pending', pending, {
            'count': pending
        });
        var deploying_label = Lang.choice('dashboard.running', deploying, {
            'count': deploying
        });

        $('.deploying_header span').text(deploying_label);
        $('.pending_header span').text(pending_label);
    }

})(jQuery);
