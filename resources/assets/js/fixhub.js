(function ($) {

    Fixhub.loadLivestamp();

    Fixhub.listener.on('deployment:' + Fixhub.events.MODEL_CHANGED, function (data) {

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

            var status_data = Fixhub.formatDeploymentStatus(parseInt(data.model.status));

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

            if (data.model.status === Fixhub.statuses.DEPLOYMENT_COMPLETED) {
                toastr.success(toast_title + ' - ' + trans('deployments.completed'), data.model.project_name);
            } else if (data.model.status === Fixhub.statuses.DEPLOYMENT_FAILED) {
                toastr.error(toast_title + ' - ' + trans('deployments.failed'), data.model.project_name);
            } else if (data.model.status === Fixhub.statuses.DEPLOYMENT_ERRORS) {
                toastr.warning(toast_title + ' - ' + trans('deployments.completed_with_errors'), data.model.project_name);
            } // FIXME: Add cancelled
        }
    });

    Fixhub.listener.on('project:' + Fixhub.events.MODEL_CHANGED, function (data) {

        var project = $('#project_' + data.model.id);

        if (project.length > 0) {
            var status_bar = $('td:nth-child(4) span.label', project);

            var status_data = Fixhub.formatProjectStatus(parseInt(data.model.status));

            $('td:first a', project).text(data.model.name);
            $('td:nth-child(3)', project).text(moment(data.model.last_run).fromNow());
            status_bar.attr('class', 'label label-' + status_data.label_class)
            $('i', status_bar).attr('class', 'ion ion-' + status_data.icon_class);
            $('span', status_bar).text(status_data.label);
        }
    });

    Fixhub.listener.on('project:' + Fixhub.events.MODEL_TRASHED, function (data) {

        if (parseInt(data.model.id) === parseInt(Fixhub.project_id)) {
            window.location.href = '/';
        }
    });

    function updateTimeline() {
        $.ajax({
            type: 'GET',
            url: '/timeline'
        }).done(function (response) {
            $('#timeline').html(response);
            Fixhub.loadLivestamp();
        });
    }

    function updateTodoBar(data) {
        data.model.time = moment(data.model.started_at).fromNow();
        data.model.url = '/deployment/' + data.model.id;

        $('#deployment_info_' + data.model.id).remove();

        var template = _.template($('#deployment-list-template').html());
        var html = template(data.model);

        if (data.model.status === Fixhub.statuses.DEPLOYMENT_PENDING) {
            $('.pending_menu').append(html);
        } else if (data.model.status === Fixhub.statuses.DEPLOYMENT_DEPLOYING) {
            $('.deploying_menu').append(html);
        } else if (data.model.status === Fixhub.statuses.DEPLOYMENT_APPROVING || data.model.status === Fixhub.statuses.DEPLOYMENT_APPROVED) {
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