(function ($) {

    $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
        jqXHR.setRequestHeader('X-CSRF-Token', $('meta[name="token"]').attr('content'));
    });

    // Prevent double form submission
    $('form').submit(function () {
        var $form = $(this);
        $form.find(':submit').prop('disabled', true);
    });

    // Don't need to try and connect to the web socket when not logged in
    if (window.location.href.match(/login|password/) != null) {
        return;
    }

    window.Fixhub = {};

    var locale = $('meta[name="locale"]').attr('content');

    Lang.setLocale(locale);

    moment.locale(locale);

    $('abbr.timeago').each(function () {
        var $el = $(this);
        $el.livestamp($el.data('timeago')).tooltip();
    });

    $('[data-toggle="tooltip"]').tooltip();

    Fixhub.select2_options = {
        width: '100%',
        minimumResultsForSearch: Infinity
    };

    $(".select2").select2(Fixhub.select2_options);

    // Toastr options
    toastr.options.closeButton = true;
    toastr.options.progressBar = true;
    toastr.options.preventDuplicates = true;
    toastr.options.closeMethod = 'fadeOut';
    toastr.options.closeDuration = 3000;
    toastr.options.closeEasing = 'swing';
    toastr.options.positionClass = 'toast-bottom-right';
    toastr.options.timeOut = 5000;
    toastr.options.extendedTimeOut = 7000;

    // Deployment status
    var DEPLOYMENT_COMPLETED = 0;
    var DEPLOYMENT_PENDING   = 1;
    var DEPLOYMENT_DEPLOYING = 2;
    var DEPLOYMENT_FAILED    = 3;
    var DEPLOYMENT_ERRORS    = 4;
    var DEPLOYMENT_CANCELLED = 5;
    var DEPLOYMENT_ABORTED   = 6;
    var DEPLOYMENT_APPROVING = 7;
    var DEPLOYMENT_APPROVED  = 8;

    Fixhub.project_id = Fixhub.project_id || null;

    Fixhub.listener = io.connect($('meta[name="socket_url"]').attr('content'), {
        query: 'jwt=' + $('meta[name="jwt"]').attr('content')
    });

    Fixhub.connection_error = false;

    Fixhub.listener.on('connect_error', function(error) {
        if (!Fixhub.connection_error) {
            $('#socket_offline').show();
        }

        Fixhub.connection_error = true;
    });

    Fixhub.listener.on('connect', function() {
        $('#socket_offline').hide();
        Fixhub.connection_error = false;
    });

    Fixhub.listener.on('reconnect', function() {
        $('#socket_offline').hide();
        Fixhub.connection_error = false;
    });

    Fixhub.listener.on('deployment:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {

        // Update todo bar
        updateTodoBar(data);

        if ($('#timeline').length > 0) {
            updateTimeline();
        }

        var deployment  = $('#deployment_' + data.model.id);

        if (deployment.length > 0) {

            $('td:nth-child(6)', deployment).text(data.model.committer);

            if (data.model.commit_url) {
                $('td:nth-child(7)', deployment).html('<a href="' + data.model.commit_url + '" target="_blank">' + data.model.short_commit + '</a>');
            } else {
                $('td:nth-child(8)', deployment).text(data.model.short_commit);
            }

            var status_bar = $('td:nth-child(9) span.label', deployment);

            var status_data = Fixhub.parseDeploymentStatus(parseInt(data.model.status));

            if (status_data.done) {
                $('button#deploy_project:disabled').removeAttr('disabled');
                $('td:nth-child(10) a.btn-cancel', deployment).remove();

                if (status_data.success) {
                    $('button.btn-rollback').removeClass('hide');
                }
            }

            status_bar.attr('class', 'label label-' + status_data.label_class);
            $('i', status_bar).attr('class', 'ion ion-' + status_data.icon_class);
            $('span', status_bar).text(status_data.label);
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

    Fixhub.listener.on('project:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {

        var project = $('#project_' + data.model.id);

        if (project.length > 0) {
            var status_bar = $('td:nth-child(4) span.label', project);

            var status_data = Fixhub.parseProjectStatus(parseInt(data.model.status));

            $('td:first a', project).text(data.model.name);
            $('td:nth-child(3)', project).text(moment(data.model.last_run).fromNow());
            status_bar.attr('class', 'label label-' + status_data.label_class)
            $('i', status_bar).attr('class', 'ion ion-' + status_data.icon_class);
            $('span', status_bar).text(status_data.label);
        }
    });

    Fixhub.listener.on('project:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {

        if (parseInt(data.model.id) === parseInt(Fixhub.project_id)) {
            window.location.href = '/';
        }
    });

    Fixhub.parseProjectStatus = function (deploy_status) {
        // Project and Environment status
        var FINISHED     = 0;
        var PENDING      = 1;
        var DEPLOYING    = 2;
        var FAILED       = 3;
        var NOT_DEPLOYED = 4;

        var data = {};

        data.icon_class = 'help';
        data.label_class = 'default';
        data.label = trans('projects.not_deployed');

        if (deploy_status === FINISHED) {
            data.icon_class = 'checkmark-round';
            data.label_class = 'success';
            data.label = trans('projects.finished');
        } else if (deploy_status === DEPLOYING) {
            data.icon_class = 'load-c fixhub-spin';
            data.label_class = 'warning';
            data.label = trans('projects.deploying');
        } else if (deploy_status === FAILED) {
            data.icon_class = 'close-round';
            data.label_class = 'danger';
            data.label = trans('projects.failed');
        } else if (deploy_status === PENDING) {
            data.icon_class = 'clock';
            data.label_class = 'info';
            data.label = trans('projects.pending');
        }

        return data;
    };

    Fixhub.parseDeploymentStatus = function (deploy_status) {

        var data = {};

        data.icon_class = 'clock-o';
        data.label_class = 'info';
        data.label = trans('deployments.pending');
        data.done = false;
        data.success = false;

        if (deploy_status === DEPLOYMENT_COMPLETED) {
            data.icon_class = 'checkmark-round';
            data.label_class = 'success';
            data.label = trans('deployments.completed');
            data.done = true;
            data.success = true;
        } else if (deploy_status === DEPLOYMENT_DEPLOYING) {
            data.icon_class = 'load-c fixhub-spin';
            data.label_class = 'warning';
            data.label = trans('deployments.running');
        } else if (deploy_status === DEPLOYMENT_FAILED) {
            data.icon_class = 'close-round';
            data.label_class = 'danger';
            data.label = trans('deployments.failed');
            data.done = true;
        } else if (deploy_status === DEPLOYMENT_ERRORS) {
            data.icon_class = 'close';
            data.label_class = 'success';
            data.label = trans('deployments.completed_with_errors');
            data.done = true;
            data.success = true;
        } else if (deploy_status === DEPLOYMENT_CANCELLED) {
            data.icon_class = 'alert';
            data.label_class = 'danger';
            data.label = trans('deployments.cancelled');
            data.done = true;
        }

        return data;
    };

    function updateTimeline() {
        $.ajax({
            type: 'GET',
            url: '/timeline'
        }).done(function (response) {
            $('#timeline').html(response);
        });
    }

    function updateTodoBar(data) {
        data.model.time = moment(data.model.started_at).fromNow();
        data.model.url = '/deployment/' + data.model.id;

        $('#deployment_info_' + data.model.id).remove();

        var template = _.template($('#deployment-list-template').html());
        var html = template(data.model);

        if (data.model.status === DEPLOYMENT_PENDING) {
            $('.pending_menu').append(html);
        } else if (data.model.status === DEPLOYMENT_DEPLOYING) {
            $('.deploying_menu').append(html);
        } else if (data.model.status === DEPLOYMENT_APPROVING || data.model.status === DEPLOYMENT_APPROVED) {
            $('.approving_menu').append(html);
        }

        var pending = $('.pending_menu li.todo_item').length;
        var deploying = $('.deploying_menu li.todo_item').length;
        var approving = $('.approving_menu li.todo_item').length;

        var todo_count = pending + deploying + approving;

    
        if(todo_count > 0) {
            $('#todo_menu span.label').html(todo_count).addClass('label-success');
            $('#todo_menu .dropdown-toggle i.ion').addClass('text-danger');
        } else {
            $('#todo_menu span.label').html('').removeClass('label-success');
            $('#todo_menu .dropdown-toggle i.ion').removeClass('text-danger')
        }

        var empty_template = _.template($('#todo-item-empty-template').html());
        if(pending > 0) {
            $('.pending_header i').addClass('fixhub-spin');
            $('.pending_menu li.item_empty').remove();
        } else {
            $('.pending_header i').removeClass('fixhub-spin');
            $('.pending_menu li.item_empty').remove();
            $('.pending_menu').append(empty_template({empty_text:trans('dashboard.pending_empty')}));
        }

        if(deploying > 0) {
            $('.deploying_header i').addClass('fixhub-spin');
            $('.deploying_menu li.item_empty').remove();
        } else {
            $('.deploying_header i').removeClass('fixhub-spin');
            $('.deploying_menu li.item_empty').remove();
            $('.deploying_menu').append(empty_template({empty_text:trans('dashboard.running_empty')}));
        }

        if(approving > 0) {
            $('.approving_header i').addClass('fixhub-spin');
            $('.approving_menu li.item_empty').remove();
        } else {
            $('.approving_header i').removeClass('fixhub-spin');
            $('.approving_menu li.item_empty').remove();
            $('.approving_menu').append(empty_template({empty_text:trans('dashboard.approving_empty')}));
        }

        var pending_label = Lang.choice('dashboard.pending', pending, {
            'count': pending
        });
        var deploying_label = Lang.choice('dashboard.running', deploying, {
            'count': deploying
        });
        var approving_label = Lang.choice('dashboard.approving', approving, {
            'count': approving
        });

        $('.deploying_header span').text(deploying_label);
        $('.pending_header span').text(pending_label);
        $('.approving_header span').text(approving_label);
    }

})(jQuery);