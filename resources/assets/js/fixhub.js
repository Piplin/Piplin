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

            $('td.committer', deployment).text(data.model.committer);

            if (data.model.commit_url) {
                $('td.commit', deployment).html('<a href="' + data.model.commit_url + '" target="_blank">' + data.model.short_commit + '</a>'+'('+data.model.branch+')');
            }

            var status_bar = $('td.status span', deployment);

            var status_data = Fixhub.formatDeploymentStatus(parseInt(data.model.status));

            if (status_data.done) {
                $('button#deploy_project:disabled').removeAttr('disabled');
                $('td a.btn-cancel', deployment).remove();

                if (status_data.success) {
                    $('button.btn-rollback').removeClass('hide');
                }
            }

            status_bar.attr('class', 'text-' + status_data.label_class);
            $('i', status_bar).attr('class', 'fixhub fixhub-' + status_data.icon_class);
            $('span', status_bar).text(status_data.label);
        } else {
            var toast_title = trans('dashboard.deployment_number', {
                'id': data.model.id
            });

            if (data.model.status === Fixhub.statuses.DEPLOYMENT_COMPLETED) {
                Fixhub.toast(toast_title + ' - ' + trans('deployments.completed'), data.model.project_name, 'success');
            } else if (data.model.status === Fixhub.statuses.DEPLOYMENT_FAILED) {
                Fixhub.toast(toast_title + ' - ' + trans('deployments.failed'), data.model.project_name, 'error');
            } else if (data.model.status === Fixhub.statuses.DEPLOYMENT_ERRORS) {
                Fixhub.toast(toast_title + ' - ' + trans('deployments.completed_with_errors'), data.model.project_name, 'warning');
            } // FIXME: Add cancelled
        }
    });

    Fixhub.listener.on('project:' + Fixhub.events.MODEL_CHANGED, function (data) {

        var project = $('#project_' + data.model.id);

        if (project.length > 0) {
            var status_bar = $('td.status span', project);

            var status_data = Fixhub.formatProjectStatus(parseInt(data.model.status));

            $('td.name', project).text(data.model.name);
            $('td.time', project).text(moment(data.model.last_run).fromNow());
            status_bar.attr('class', 'text-' + status_data.label_class)
            $('i', status_bar).attr('class', 'fixhub fixhub-' + status_data.icon_class);
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

        if (data.model.status === Fixhub.statuses.DEPLOYMENT_DEPLOYING) {
            $('.deploying_menu').append(html);
        }

        var deploying = $('.deploying_menu li.todo_item').length;
        var pending = $('.pending_menu li.todo_item').length;

        var todo_count = deploying+pending;

    
        if(todo_count > 0) {
            $('#todo_menu span.label').html(todo_count).addClass('label-success');
            $('#todo_menu .dropdown-toggle i.ion').addClass('text-danger');
        } else {
            $('#todo_menu span.label').html('').removeClass('label-success');
            $('#todo_menu .dropdown-toggle i.ion').removeClass('text-danger')
        }

        var empty_template = _.template($('#todo-item-empty-template').html());

        if(deploying > 0) {
            $('.deploying_header i').addClass('fixhub-spin');
            $('.deploying_menu li.item_empty').remove();
        } else {
            $('.deploying_header i').removeClass('fixhub-spin');
            $('.deploying_menu li.item_empty').remove();
            $('.deploying_menu').append(empty_template({empty_text:trans('dashboard.running_empty')}));
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