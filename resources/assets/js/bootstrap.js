(function ($) {

    //App setup
    window.Piplin = {};

    Piplin.project_id = Piplin.project_id || null;

    Piplin.statuses = {
        // Project and Environment
        FINISHED: 0,
        PENDING:  1,
        RUNNING:  2,
        FAILED:   3,
        INITIAL:  4,

        // Task status
        TASK_DRAFT:    -1,
        TASK_COMPLETED: 0,
        TASK_PENDING:   1,
        TASK_RUNNING:   2,
        TASK_FAILED:    3,
        TASK_ERRORS:    4,
        TASK_CANCELLED: 5,
        TASK_ABORTED:   6,

        // Server log status
        SVRLOG_COMPLETED: 0,
        SVRLOG_PENDING:   1,
        SVRLOG_RUNNING:   2,
        SVRLOG_FAILED:    3,
        SVRLOG_CANCELLED: 4
    };

    Piplin.events = {
        // Common events
        MODEL_CREATED: 'Piplin\\Bus\\Events\\ModelCreatedEvent',
        MODEL_CHANGED: 'Piplin\\Bus\\Events\\ModelChangedEvent',
        MODEL_TRASHED: 'Piplin\\Bus\\Events\\ModelTrashedEvent',

        // Server log changed
        SVRLOG_CHANGED: 'Piplin\\Bus\\Events\\ServerLogChangedEvent',
        OUTPUT_CHANGED: 'Piplin\\Bus\\Events\\ServerOutputChangedEvent'

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

    Piplin.select2_options = {
        width: '100%',
        minimumResultsForSearch: Infinity
    };

    $(".select2").select2(Piplin.select2_options);

    //Clipboard
    new Clipboard('.clipboard').on('success', function(e){
        Piplin.toast(trans('app.copied'));
    });

    // Socket.io
    Piplin.listener = io.connect($('meta[name="socket_url"]').attr('content'), {
        query: 'jwt=' + $('meta[name="jwt"]').attr('content')
    });

    Piplin.connection_error = false;

    Piplin.listener.on('connect_error', function(error) {
        if (!Piplin.connection_error) {
            $('#socket_offline').show();
        }

        Piplin.connection_error = true;
    });

    Piplin.listener.on('connect', function() {
        $('#socket_offline').hide();
        Piplin.connection_error = false;
    });

    Piplin.listener.on('reconnect', function() {
        $('#socket_offline').hide();
        Piplin.connection_error = false;
    });

    // Sidebar toggle
    Piplin.sidebarToggle = function() {
        var wrapper = $('.wrapper');

        if (window.localStorage && window.localStorage['piplin.stickySidebar'] == 'true') {
            wrapper.removeClass("wrapper-collapsed");
            wrapper.find(".main-sidebar").show();
        } else {
            wrapper.addClass("wrapper-collapsed");
            wrapper.find(".main-sidebar").hide();
        }

        $('.sidebar-toggle').on('click', function(e){
            e.preventDefault();

            if (wrapper.hasClass("wrapper-collapsed")) {
                 wrapper.find(".main-sidebar").show("slow");
                wrapper.removeClass("wrapper-collapsed");
                window.localStorage.setItem('piplin.stickySidebar', true);
            } else {
                wrapper.find(".main-sidebar").hide("slow");
                wrapper.addClass("wrapper-collapsed");
                window.localStorage.setItem('piplin.stickySidebar', false);
            }
        });
    };

    // Load livestamp
    Piplin.loadLivestamp = function () {
        $('abbr.timeago').each(function () {
            var $el = $(this);
            $el.livestamp($el.data('timeago')).tooltip();
        });
    };

    // Format the project status
    Piplin.formatProjectStatus = function (task_status) {
        var data = {};

        data.icon_class = 'help';
        data.label_class = 'default';
        data.label = trans('projects.initial');

        if (task_status === Piplin.statuses.FINISHED) {
            data.icon_class = 'check';
            data.label_class = 'success';
            data.label = trans('projects.finished');
        } else if (task_status === Piplin.statuses.RUNNING) {
            data.icon_class = 'load piplin-spin';
            data.label_class = 'warning';
            data.label = trans('projects.running');
        } else if (task_status === Piplin.statuses.FAILED) {
            data.icon_class = 'close';
            data.label_class = 'danger';
            data.label = trans('projects.failed');
        } else if (task_status === Piplin.statuses.PENDING) {
            data.icon_class = 'clock';
            data.label_class = 'info';
            data.label = trans('projects.pending');
        }

        return data;
    };

    // Format the deployment status
    Piplin.formatTaskStatus = function (task_status) {
        var data = {};

        data.icon_class = 'clock';
        data.label_class = 'info';
        data.label = trans('tasks.pending');
        data.done = false;
        data.success = false;

        if (task_status === Piplin.statuses.TASK_COMPLETED) {
            data.icon_class = 'check';
            data.label_class = 'success';
            data.label = trans('tasks.completed');
            data.done = true;
            data.success = true;
        } else if (task_status === Piplin.statuses.TASK_RUNNING) {
            data.icon_class = 'load piplin-spin';
            data.label_class = 'warning';
            data.label = trans('tasks.running');
        } else if (task_status === Piplin.statuses.TASK_FAILED) {
            data.icon_class = 'close';
            data.label_class = 'danger';
            data.label = trans('tasks.failed');
            data.done = true;
        } else if (task_status === Piplin.statuses.TASK_ERRORS) {
            data.icon_class = 'close';
            data.label_class = 'success';
            data.label = trans('tasks.completed_with_errors');
            data.done = true;
            data.success = true;
        } else if (task_status === Piplin.statuses.TASK_CANCELLED) {
            data.icon_class = 'warning';
            data.label_class = 'danger';
            data.label = trans('tasks.cancelled');
            data.done = true;
        } else if (task_status === Piplin.statuses.TASK_DRAFT) {
            data.icon_class = 'edit';
            data.label_class = 'danger';
            data.label = trans('tasks.draft');
        }

        return data;
    };

    Piplin.toast = function (content, title, caller) {
        title = title || '';
        caller = caller || 'not_in_progress';

        if (!Config.get('piplin.toastr') && caller == 'not_in_progress') {
            return;
        }

        if (caller == 'not_in_progress') {
            toastr.options.positionClass = 'toast-top-center';
            toastr.options.progressBar = false;
            toastr.options.preventDuplicates = true;
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
            return toastr.error(content, title);
        } else if(caller == 'warning') {
            return toastr.warning(content, title);
        } else if(caller == 'info') {
            toastr.options.closeButton = false;
            toastr.options.progressBar = false;
            return toastr.info(content, title);
        } else {
            return toastr.success(content, title);
        }
    };

})(jQuery);