(function ($) {

    //App setup
    window.Fixhub = {};

    Fixhub.project_id = Fixhub.project_id || null;

    Fixhub.statuses = {
        // Project and Environment
        FINISHED:     0,
        PENDING:      1,
        DEPLOYING:    2,
        FAILED:       3,
        NOT_DEPLOYED: 4,

        // Deployment status
        DEPLOYMENT_COMPLETED: 0,
        DEPLOYMENT_PENDING:   1,
        DEPLOYMENT_DEPLOYING: 2,
        DEPLOYMENT_FAILED:    3,
        DEPLOYMENT_ERRORS:    4,
        DEPLOYMENT_CANCELLED: 5,
        DEPLOYMENT_ABORTED:   6,

        // Server log status
        SVRLOG_COMPLETED: 0,
        SVRLOG_PENDING:   1,
        SVRLOG_RUNNING:   2,
        SVRLOG_FAILED:    3,
        SVRLOG_CANCELLED: 4
    };

    Fixhub.events = {
        // Common events
        MODEL_CREATED: 'Fixhub\\Bus\\Events\\ModelCreatedEvent',
        MODEL_CHANGED: 'Fixhub\\Bus\\Events\\ModelChangedEvent',
        MODEL_TRASHED: 'Fixhub\\Bus\\Events\\ModelTrashedEvent',

        // Server log changed
        SVRLOG_CHANGED: 'Fixhub\\Bus\\Events\\ServerLogChangedEvent',
        OUTPUT_CHANGED: 'Fixhub\\Bus\\Events\\ServerOutputChangedEvent'

    };

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

    var locale = $('meta[name="locale"]').attr('content');

    Lang.setLocale(locale);

    moment.locale(locale);

    $('[data-toggle="tooltip"]').tooltip();

    Fixhub.select2_options = {
        width: '100%',
        minimumResultsForSearch: Infinity
    };

    $(".select2").select2(Fixhub.select2_options);

    // Socket.io
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

    // Load livestamp
    Fixhub.loadLivestamp = function () {
        $('abbr.timeago').each(function () {
            var $el = $(this);
            $el.livestamp($el.data('timeago')).tooltip();
        });
    };

    // Format the project status
    Fixhub.formatProjectStatus = function (deploy_status) {
        var data = {};

        data.icon_class = 'help';
        data.label_class = 'default';
        data.label = trans('projects.not_deployed');

        if (deploy_status === Fixhub.statuses.FINISHED) {
            data.icon_class = 'check';
            data.label_class = 'success';
            data.label = trans('projects.finished');
        } else if (deploy_status === Fixhub.statuses.DEPLOYING) {
            data.icon_class = 'load fixhub-spin';
            data.label_class = 'warning';
            data.label = trans('projects.deploying');
        } else if (deploy_status === Fixhub.statuses.FAILED) {
            data.icon_class = 'close';
            data.label_class = 'danger';
            data.label = trans('projects.failed');
        } else if (deploy_status === Fixhub.statuses.PENDING) {
            data.icon_class = 'clock';
            data.label_class = 'info';
            data.label = trans('projects.pending');
        }

        return data;
    };

    // Format the deployment status
    Fixhub.formatDeploymentStatus = function (deploy_status) {
        var data = {};

        data.icon_class = 'clock';
        data.label_class = 'info';
        data.label = trans('deployments.pending');
        data.done = false;
        data.success = false;

        if (deploy_status === Fixhub.statuses.DEPLOYMENT_COMPLETED) {
            data.icon_class = 'check';
            data.label_class = 'success';
            data.label = trans('deployments.completed');
            data.done = true;
            data.success = true;
        } else if (deploy_status === Fixhub.statuses.DEPLOYMENT_DEPLOYING) {
            data.icon_class = 'load fixhub-spin';
            data.label_class = 'warning';
            data.label = trans('deployments.running');
        } else if (deploy_status === Fixhub.statuses.DEPLOYMENT_FAILED) {
            data.icon_class = 'close';
            data.label_class = 'danger';
            data.label = trans('deployments.failed');
            data.done = true;
        } else if (deploy_status === Fixhub.statuses.DEPLOYMENT_ERRORS) {
            data.icon_class = 'close';
            data.label_class = 'success';
            data.label = trans('deployments.completed_with_errors');
            data.done = true;
            data.success = true;
        } else if (deploy_status === Fixhub.statuses.DEPLOYMENT_CANCELLED) {
            data.icon_class = 'warning';
            data.label_class = 'danger';
            data.label = trans('deployments.cancelled');
            data.done = true;
        }

        return data;
    };

    Fixhub.toast = function (content, title, caller) {
        title = title || '';
        caller = caller || 'not_in_progress';

        if (!Config.get('fixhub.toastr') && caller == 'not_in_progress') {
            return;
        }

        if (caller == 'not_in_progress') {
            toastr.options.positionClass = 'toast-top-center';
            toastr.options.progressBar = false;
            toastr.options.closeDuration = 1000;
            toastr.options.timeOut = 3000;
            toastr.options.extendedTimeOut = 1000;
        } else {
            toastr.options.closeButton = true;
            toastr.options.progressBar = true;
            toastr.options.preventDuplicates = true;
            toastr.options.closeMethod = 'fadeOut';
            toastr.options.closeDuration = 3000;
            toastr.options.closeEasing = 'swing';
            toastr.options.positionClass = 'toast-bottom-right';
            toastr.options.timeOut = 5000;
            toastr.options.extendedTimeOut = 7000;
        }

        if (caller == 'error') {
            toastr.error(content, title);
        } else if(caller == 'warning') {
            toastr.warning(content, title);
        } else {
            toastr.success(content, title);
        }
    };

})(jQuery);