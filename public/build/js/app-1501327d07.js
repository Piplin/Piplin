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

        // Update nav bar
        updateNavBar(data);

        //var project = $('#project_' + data.model.project_id);

        if ($('#timeline').length > 0) {
            updateTimeline();
        }

        var deployment  = $('#deployment_' + data.model.id);

        if (deployment.length > 0) {

            $('td:nth-child(4)', deployment).text(data.model.committer);

            if (data.model.commit_url) {
                $('td:nth-child(5)', deployment).html('<a href="' + data.model.commit_url + '" target="_blank">' + data.model.short_commit + '</a>');
            } else {
                $('td:nth-child(5)', deployment).text(data.model.short_commit);
            }

            var icon_class = 'clock-o';
            var label_class = 'info';
            var label = trans('deployments.pending');
            var done = false;
            var success = false;

            data.model.status = parseInt(data.model.status);
            var status = $('td:nth-child(7) span.label', deployment);

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
                $('td:nth-child(8) a.btn-cancel', deployment).remove();

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

    app.listener.on('group:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
        // do something.
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

    // FIXME: This is cheating
    function updateTimeline() {
        $.ajax({
            type: 'GET',
            url: '/timeline'
        }).success(function (response) {
            $('#timeline').html(response);
        });
    }

    function updateNavBar(data) {
        data.model.time = moment(data.model.started_at).format('HH:mm:ss');
        data.model.url = '/deployment/' + data.model.id;

        $('#deployment_info_' + data.model.id).remove();
        $('#pending_menu, #deploying_menu').show();

        var template = _.template($('#deployment-list-template').html());
        var html = template(data.model);

        if (data.model.status === DEPLOYMENT_PENDING) {
            $('#pending_menu ul.menu').append(html);
        }
        else if (data.model.status === DEPLOYMENT_DEPLOYING) {
            $('#deploying_menu ul.menu').append(html);
        }

        var pending = $('#pending_menu ul.menu li').length;
        var deploying = $('#deploying_menu ul.menu li').length;

        var pending_label = Lang.choice('dashboard.pending', pending, {
            'count': pending
        });

        if (pending === 0) {
            //$('#pending_menu').hide();
        }

        var deploying_label = Lang.choice('dashboard.running', deploying, {
            'count': deploying
        });

        if (deploying === 0) {
            //$('#deploying_menu').hide();
        }

        $('#deploying_menu span.label-warning').html(deploying);
        $('#deploying_menu .header').text(deploying_label);

        $('#pending_menu span.label-info').html(pending);
        $('#pending_menu .header').text(pending_label);
    }

    $(document).ready(function () {
        if ($('#pending_menu ul.menu li').length > 0) {
            $('#pending_menu').show();
        }

        if ($('#deploying_menu ul.menu li').length > 0) {
            $('#deploying_menu').show();
        }

        //INITIALIZE SPARKLINE CHARTS
        $(".sparkline").each(function () {
          var $this = $(this);
          $this.sparkline('html', $this.data());
        });

    
    });

})(jQuery);

var app = app || {};

(function ($) {
    $('select.deployment-source').select2({
        width: '100%',
        minimumResultsForSearch: 6
    });

    $('.deployment-source:radio').on('change', function (event) {
        var target = $(event.currentTarget);

        $('div.deployment-source-container').hide();
        if (target.val() === 'branch') {
            $('#deployment_branch').parent('div').show();
        } else if (target.val() === 'tag') {
            $('#deployment_tag').parent('div').show();
        }
    });

    $('#reason').on('show.bs.modal', function (event) {
        var modal = $(this);
        $('.callout-danger', modal).hide();
    });

    $('#reason button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');
        var source = $('input[name=source]:checked').val();

        $('.has-error', source).removeClass('has-error');

        if (source === 'branch' || source === 'tag') {
            if ($('#deployment_' + source).val() === '') {
                $('#deployment_' + source).parentsUntil('div').addClass('has-error');

                $('.callout-danger', dialog).show();
                event.stopPropagation();
                return;
            }
        }

        icon.addClass('ion-refresh fixhub-spin');
        $('button.close', dialog).hide();
    });

   // FIXME: This seems very wrong
    $('#project').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('projects.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();
        $('#template-list', modal).hide();

        $('.nav-tabs a:first', modal).tab('show');

        if (button.hasClass('btn-edit')) {
            title = trans('projects.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#template-list', modal).show();
            $('#project_id').val('');
            $('#project_name').val('');
            $('#project_repository').val('');
            $('#project_branch').val('master');
            $('#project_group_id').val($("#project_group_id option:first").val());
            $('#project_builds_to_keep').val(10);
            $('#project_url').val('');
            $('#project_build_url').val('');
            $('#project_allow_other_branch').prop('checked', true);
            $('#project_include_dev').prop('checked', false);
            $('#project_private_key').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('body').delegate('.project-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var project = app.Projects.get($('#model_id').val());

        project.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    // FIXME: This seems very wrong
    $('#project button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var project_id = $('#project_id').val();

        if (project_id) {
            var project = app.Projects.get(project_id);
        } else {
            var project = new app.Project();
        }

        project.save({
            name:               $('#project_name').val(),
            repository:         $('#project_repository').val(),
            branch:             $('#project_branch').val(),
            group_id:           $('#project_group_id').val(),
            builds_to_keep:     $('#project_builds_to_keep').val(),
            url:                $('#project_url').val(),
            build_url:          $('#project_build_url').val(),
            template_id:        $('#project_template_id') ? $('#project_template_id').val() : null,
            allow_other_branch: $('#project_allow_other_branch').is(':checked'),
            include_dev:        $('#project_include_dev').is(':checked'),
            private_key:        $('#project_private_key').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!project_id) {
                    app.Projects.add(response);
                }
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form :input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }

                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    app.Project = Backbone.Model.extend({
        urlRoot: '/admin/projects'
    });

    var Projects = Backbone.Collection.extend({
        model: app.Project
    });

    app.Projects = new Projects();

    app.ProjectsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#project_list tbody');

            $('#project_list').hide();
            $('#no_projects').show();

            this.listenTo(app.Projects, 'add', this.addOne);
            this.listenTo(app.Projects, 'reset', this.addAll);
            this.listenTo(app.Projects, 'remove', this.addAll);
            this.listenTo(app.Projects, 'all', this.render);

            app.listener.on('project:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var project = app.Projects.get(parseInt(data.model.id));

                if (project) {
                    project.set(data.model);
                }
            });

            app.listener.on('project:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                app.Projects.add(data.model);
            });

            app.listener.on('project:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var project = app.Projects.get(parseInt(data.model.id));

                if (project) {
                    app.Projects.remove(project);
                }

                $('#project_' + data.model.id).parent('li').remove();

                if (parseInt(data.model.id) === parseInt(app.project_id)) {
                    window.location.href = '/';
                }
            });
        },
        render: function () {
            if (app.Projects.length) {
                $('#no_projects').hide();
                $('#project_list').show();
            } else {
                $('#no_projects').show();
                $('#project_list').hide();
            }
        },
        addOne: function (project) {
            var view = new app.ProjectView({
                model: project
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Projects.each(this.addOne, this);
        }
    });

    app.ProjectView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editProject',
            'click .btn-trash': 'trashProject'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#project-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.deploy = data.last_run ? moment(data.last_run).format('YYYY-MM-DD HH:mm:ss') : false;

            this.$el.html(this.template(data));

            return this;
        },
        editProject: function() {
            $('#project_id').val(this.model.id);
            $('#project_name').val(this.model.get('name'));
            $('#project_repository').val(this.model.get('repository'));
            $('#project_branch').val(this.model.get('branch'));
            $('#project_group_id').val(this.model.get('group_id'));
            $('#project_builds_to_keep').val(this.model.get('builds_to_keep'));
            $('#project_url').val(this.model.get('url'));
            $('#project_build_url').val(this.model.get('build_url'));
            $('#project_allow_other_branch').prop('checked', (this.model.get('allow_other_branch') === true));
            $('#project_include_dev').prop('checked', (this.model.get('include_dev') === true));
            $('#project_private_key').val('');
        },
        trashProject: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade project-trash');
        }
    });

    $('#new_webhook').on('click', function(event) {
        var target = $(event.currentTarget);
        var project_id = target.data('project-id');
        var icon = $('i', target);

        if ($('.fixhub-spin', target).length > 0) {
            return;
        }

        target.attr('disabled', 'disabled');

        icon.addClass('fixhub-spin');

        $.ajax({
            type: 'GET',
            url: '/webhook/' + project_id + '/refresh'
        }).fail(function (response) {

        }).done(function (data) {
            $('#webhook').html(data.url);
        }).always(function () {
            icon.removeClass('fixhub-spin');
            target.removeAttr('disabled');
        });
    });
})(jQuery);

var app = app || {};

(function ($) {
    // FIXME: This seems very wrong
    $('#template').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('templates.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('templates.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#template_id').val('');
            $('#template_name').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('body').delegate('.template-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var template = app.Templates.get($('#model_id').val());

        template.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    // FIXME: This seems very wrong
    $('#template button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var template_id = $('#template_id').val();

        if (template_id) {
            var template = app.Templates.get(template_id);
        } else {
            var template = new app.Template();
        }

        template.save({
            name: $('#template_name').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!template_id) {
                    app.Templates.add(response);

                    window.location.href = '/admin/templates/' + response.id;
                }
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    app.Template = Backbone.Model.extend({
        urlRoot: '/admin/templates'
    });

    var Templates = Backbone.Collection.extend({
        model: app.Template
    });

    app.Templates = new Templates();

    app.TemplatesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#template_list tbody');

            $('#template_list').hide();
            $('#no_templates').show();

            this.listenTo(app.Templates, 'add', this.addOne);
            this.listenTo(app.Templates, 'reset', this.addAll);
            this.listenTo(app.Templates, 'remove', this.addAll);
            this.listenTo(app.Templates, 'all', this.render);

            app.listener.on('template:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var template = app.Templates.get(parseInt(data.model.id));

                if (template) {
                    template.set(data.model);
                }
            });

            app.listener.on('template:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                app.Templates.add(data.model);
            });

            app.listener.on('template:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var template = app.Templates.get(parseInt(data.model.id));

                if (template) {
                    app.Templates.remove(template);
                }
            });
        },
        render: function () {
            if (app.Templates.length) {
                $('#no_templates').hide();
                $('#template_list').show();
            } else {
                $('#no_templates').show();
                $('#template_list').hide();
            }
        },
        addOne: function (template) {
            var view = new app.TemplateView({
                model: template
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Templates.each(this.addOne, this);
        }
    });

    app.TemplateView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editTemplate',
            'click .btn-delete': 'trashTemplate'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#template-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        editTemplate: function() {
            $('#template_id').val(this.model.id);
            $('#template_name').val(this.model.get('name'));
        },
        trashTemplate: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade template-trash');
        }
    });
})(jQuery);

var app = app || {};

(function ($) {
    var SUCCESSFUL = 0;
    var UNTESTED   = 1;
    var FAILED     = 2;
    var TESTING    = 3;

    $('#server_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('server-id'));
            });

            $.ajax({
                url: '/servers/reorder',
                method: 'POST',
                data: {
                    servers: ids
                }
            });
        }
    });

    // FIXME: This seems very wrong
    $('#server').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('servers.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();
        $('#add-server-command', modal).hide();

        if (button.hasClass('btn-edit')) {
            title = trans('servers.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#server_id').val('');
            $('#server_name').val('');
            $('#server_address').val('');
            $('#server_port').val('22');
            $('#server_user').val('');
            $('#server_path').val('');
            $('#server_deploy_code').prop('checked', true);
            $('#add-server-command', modal).show();
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('body').delegate('.server-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var server = app.Servers.get($('#model_id').val());

        server.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    // FIXME: This seems very wrong
    $('#server button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var server_id = $('#server_id').val();

        if (server_id) {
            var server = app.Servers.get(server_id);
        } else {
            var server = new app.Server();
        }

        server.save({
            name:         $('#server_name').val(),
            ip_address:   $('#server_address').val(),
            port:         $('#server_port').val(),
            user:         $('#server_user').val(),
            path:         $('#server_path').val(),
            deploy_code:  $('#server_deploy_code').is(':checked'),
            project_id:   $('input[name="project_id"]').val(),
            add_commands: $('#server_commands').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!server_id) {
                    app.Servers.add(response);
                }
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });


    app.Server = Backbone.Model.extend({
        urlRoot: '/servers'
    });

    var Servers = Backbone.Collection.extend({
        model: app.Server,
        comparator: function(serverA, serverB) {
            if (serverA.get('name') > serverB.get('name')) {
                return -1; // before
            } else if (serverA.get('name') < serverB.get('name')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    app.Servers = new Servers();

    app.ServersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#server_list tbody');

            $('#no_servers').show();
            $('#server_list').hide();

            this.listenTo(app.Servers, 'add', this.addOne);
            this.listenTo(app.Servers, 'reset', this.addAll);
            this.listenTo(app.Servers, 'remove', this.addAll);
            this.listenTo(app.Servers, 'all', this.render);

            app.listener.on('server:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var server = app.Servers.get(parseInt(data.model.id));

                if (server) {
                    server.set(data.model);
                }
            });

            app.listener.on('server:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                if (parseInt(data.model.project_id) === parseInt(app.project_id)) {
                    app.Servers.add(data.model);
                }
            });

            app.listener.on('server:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var server = app.Servers.get(parseInt(data.model.id));

                if (server) {
                    app.Servers.remove(server);
                }
            });
        },
        render: function () {
            if (app.Servers.length) {
                $('#no_servers').hide();
                $('#server_list').show();
            } else {
                $('#no_servers').show();
                $('#server_list').hide();
            }
        },
        addOne: function (server) {

            var view = new app.ServerView({
                model: server
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Servers.each(this.addOne, this);
        }
    });

    app.ServerView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-test': 'testConnection',
            'click .btn-edit': 'editServer',
            'click .btn-delete': 'trashServer'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#server-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.status_css = 'primary';
            data.icon_css   = 'help';
            data.status     = trans('servers.untested');

            if (parseInt(this.model.get('status')) === SUCCESSFUL) {
                data.status_css = 'success';
                data.icon_css   = 'checkmark-round';
                data.status     = trans('servers.successful');
            } else if (parseInt(this.model.get('status')) === TESTING) {
                data.status_css = 'warning';
                data.icon_css   = 'load-c fixhub-spin';
                data.status     = trans('servers.testing');
            } else if (parseInt(this.model.get('status')) === FAILED) {
                data.status_css = 'danger';
                data.icon_css   = 'alert';
                data.status     = trans('servers.failed');
            }

            this.$el.html(this.template(data));

            return this;
        },
        editServer: function() {
            // FIXME: Sure this is wrong?
            $('#server_id').val(this.model.id);
            $('#server_name').val(this.model.get('name'));
            $('#server_address').val(this.model.get('ip_address'));
            $('#server_port').val(this.model.get('port'));
            $('#server_user').val(this.model.get('user'));
            $('#server_path').val(this.model.get('path'));

            $('#server_deploy_code').prop('checked', (this.model.get('deploy_code') === true));
        },
        trashServer: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade server-trash');
        },
        testConnection: function() {
            if (parseInt(this.model.get('status')) === TESTING) {
                return;
            }

            this.model.set({
                status: TESTING
            });

            var that = this;
            $.ajax({
                type: 'GET',
                url: this.model.urlRoot + '/' + this.model.id + '/test'
            }).fail(function (response) {
                that.model.set({
                    status: FAILED
                });
            });

        }
    });
})(jQuery);

var app = app || {};

(function ($) {
    var OK       = 0;
    var UNTESTED = 1;
    var MISSING  = 2;

    // FIXME: This seems very wrong
    $('#heartbeat').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('heartbeats.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('heartbeats.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#heartbeat_id').val('');
            $('#heartbeat_name').val('');
            $('#heartbeat_interval_30').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('body').delegate('.heartbeat-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var heartbeat = app.Heartbeats.get($('#model_id').val());

        heartbeat.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    // FIXME: This seems very wrong
    $('#heartbeat button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var heartbeat_id = $('#heartbeat_id').val();

        if (heartbeat_id) {
            var heartbeat = app.Heartbeats.get(heartbeat_id);
        } else {
            var heartbeat = new app.Heartbeat();
        }

        heartbeat.save({
            name:        $('#heartbeat_name').val(),
            interval:    parseInt($('input[name=interval]:checked').val()),
            project_id:  $('input[name="project_id"]').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!heartbeat_id) {
                    app.Heartbeats.add(response);
                }
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    app.Heartbeat = Backbone.Model.extend({
        urlRoot: '/heartbeats'
    });

    var Heartbeats = Backbone.Collection.extend({
        model: app.Heartbeat,
        comparator: function(heartbeatA, heartbeatB) {
            if (heartbeatA.get('name') > heartbeatB.get('name')) {
                return -1; // before
            } else if (heartbeatA.get('name') < heartbeatB.get('name')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    app.Heartbeats = new Heartbeats();

    app.HeartbeatsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#heartbeat_list tbody');

            $('#no_heartbeats').show();
            $('#heartbeat_list').hide();

            this.listenTo(app.Heartbeats, 'add', this.addOne);
            this.listenTo(app.Heartbeats, 'reset', this.addAll);
            this.listenTo(app.Heartbeats, 'remove', this.addAll);
            this.listenTo(app.Heartbeats, 'all', this.render);

            app.listener.on('heartbeat:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var heartbeat = app.Heartbeats.get(parseInt(data.model.id));

                if (heartbeat) {
                    heartbeat.set(data.model);
                }
            });

            app.listener.on('heartbeat:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                if (parseInt(data.model.project_id) === parseInt(app.project_id)) {
                    app.Heartbeats.add(data.model);
                }
            });

            app.listener.on('heartbeat:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var heartbeat = app.Heartbeats.get(parseInt(data.model.id));

                if (heartbeat) {
                    app.Heartbeats.remove(heartbeat);
                }
            });
        },
        render: function () {
            if (app.Heartbeats.length) {
                $('#no_heartbeats').hide();
                $('#heartbeat_list').show();
            } else {
                $('#no_heartbeats').show();
                $('#heartbeat_list').hide();
            }
        },
        addOne: function (heartbeat) {

            var view = new app.HeartbeatView({
                model: heartbeat
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Heartbeats.each(this.addOne, this);
        }
    });

    app.HeartbeatView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editHeartbeat',
            'click .btn-delete': 'trashHeartbeat'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#heartbeat-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.status_css = 'primary';
            data.icon_css   = 'question';
            data.status     = trans('heartbeats.untested');
            data.has_run    = false;

            if (parseInt(this.model.get('status')) === OK) {
                data.status_css = 'success';
                data.icon_css   = 'check';
                data.status     = trans('heartbeats.ok');
                data.has_run    = true;
            } else if (parseInt(this.model.get('status')) === MISSING) {
                data.status_css = 'danger';
                data.icon_css   = 'warning';
                data.status     = trans('heartbeats.missing');
                data.has_run    = data.last_activity ? true : false;
            }

            data.interval_label = trans('heartbeats.interval_' + data.interval);

            data.formatted_date = '';
            if (data.has_run) {
                data.formatted_date = moment(data.last_activity).format('YYYY-MM-DD HH:mm:ss');
            }

            this.$el.html(this.template(data));

            return this;
        },
        editHeartbeat: function() {
            // FIXME: Sure this is wrong?
            $('#heartbeat_id').val(this.model.id);
            $('#heartbeat_name').val(this.model.get('name'));
            $('#heartbeat_interval_' + this.model.get('interval')).prop('checked', true);
        },
        trashHeartbeat: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade heartbeat-trash');
        }
    });
})(jQuery);

var app = app || {};

(function ($) {
    // FIXME: This seems very wrong
    $('#notifyslack').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('notifySlacks.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('notifySlack.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#notifyslack_id').val('');
            $('#notifyslack_name').val('');
            $('#notifyslack_webhook').val('');
            $('#notifyslack_channel').val('');
            $('#notifyslack_icon').val('');
            $('#notifyslack_failure_only').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('body').delegate('.notifyslack-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var notifyslack = app.NotifySlacks.get($('#model_id').val());

        notifyslack.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    // FIXME: This seems very wrong
    $('#notifyslack button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var notifyslack_id = $('#notifyslack_id').val();

        if (notifyslack_id) {
            var notifyslack = app.NotifySlacks.get(notifyslack_id);
        } else {
            var notifyslack = new app.NotifySlack();
        }

        notifyslack.save({
            name:         $('#notifyslack_name').val(),
            webhook:      $('#notifyslack_webhook').val(),
            channel:      $('#notifyslack_channel').val(),
            icon:         $('#notifyslack_icon').val(),
            project_id:   $('input[name="project_id"]').val(),
            failure_only: $('#notifyslack_failure_only').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!notifyslack_id) {
                    app.NotifySlacks.add(response);
                }
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });



    app.NotifySlack = Backbone.Model.extend({
        urlRoot: '/notify-slack'
    });

    var NotifySlacks = Backbone.Collection.extend({
        model: app.NotifySlack
    });

    app.NotifySlacks = new NotifySlacks();

    app.NotifySlacksTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#notifyslack_list tbody');

            $('#no_notifyslacks').show();
            $('#notifyslack_list').hide();

            this.listenTo(app.NotifySlacks, 'add', this.addOne);
            this.listenTo(app.NotifySlacks, 'reset', this.addAll);
            this.listenTo(app.NotifySlacks, 'remove', this.addAll);
            this.listenTo(app.NotifySlacks, 'all', this.render);


            app.listener.on('notifyslack:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var notifyslack = app.NotifySlacks.get(parseInt(data.model.id));

                if (server) {
                    notifyslack.set(data.model);
                }
            });

            app.listener.on('notifyslack:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                if (parseInt(data.model.project_id) === parseInt(app.project_id)) {
                    app.NotifySlacks.add(data.model);
                }
            });

            app.listener.on('notifyslack:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var notifyslack = app.NotifySlacks.get(parseInt(data.model.id));

                if (notifyslack) {
                    app.NotifySlacks.remove(notifyslack);
                }
            });
        },
        render: function () {
            if (app.NotifySlacks.length) {
                $('#no_notifyslacks').hide();
                $('#notifyslack_list').show();
            } else {
                $('#no_notifyslacks').show();
                $('#notifyslack_list').hide();
            }
        },
        addOne: function (notifyslack) {

            var view = new app.NotifySlackView({
                model: notifyslack
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.NotifySlacks.each(this.addOne, this);
        }
    });

    app.NotifySlackView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editNotifySlack',
            'click .btn-delete': 'trashNotifySlack'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#notifyslack-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        editNotifySlack: function() {
            // FIXME: Sure this is wrong?
            $('#notifyslack_id').val(this.model.id);
            $('#notifyslack_name').val(this.model.get('name'));
            $('#notifyslack_webhook').val(this.model.get('webhook'));
            $('#notifyslack_channel').val(this.model.get('channel'));
            $('#notifyslack_icon').val(this.model.get('icon'));
            $('#notifyslack_failure_only').prop('checked', (this.model.get('failure_only') === true));
        },
        trashNotifySlack: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade notifyslack-trash');
        }
    });
})(jQuery);

var app = app || {};

(function ($) {
    // FIXME: This seems very wrong
    $('#notifyemail').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('notifyEmails.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('notifyEmails.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#notifyemail_id').val('');
            $('#notifyemail_name').val('');
            $('#notifyemail_address').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
$('body').delegate('.notifyemail-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file = app.NotifyEmails.get($('#model_id').val());

        file.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    // FIXME: This seems very wrong
    $('#notifyemail button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var notifyemail_id = $('#notifyemail_id').val();

        if (notifyemail_id) {
            var file = app.NotifyEmails.get(notifyemail_id);
        } else {
            var file = new app.NotifyEmail();
        }

        file.save({
            name:       $('#notifyemail_name').val(),
            email:      $('#notifyemail_address').val(),
            project_id: $('input[name="project_id"]').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!notifyemail_id) {
                    app.NotifyEmails.add(response);
                }
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    app.NotifyEmail = Backbone.Model.extend({
        urlRoot: '/notify-email'
    });

    var NotifyEmails = Backbone.Collection.extend({
        model: app.NotifyEmail
    });

    app.NotifyEmails = new NotifyEmails();

    app.NotifyEmailsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#notifyemail_list tbody');

            $('#no_notifyemails').show();
            $('#notifyemail_list').hide();

            this.listenTo(app.NotifyEmails, 'add', this.addOne);
            this.listenTo(app.NotifyEmails, 'reset', this.addAll);
            this.listenTo(app.NotifyEmails, 'remove', this.addAll);
            this.listenTo(app.NotifyEmails, 'all', this.render);

            app.listener.on('notifyemail:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var email = app.NotifyEmails.get(parseInt(data.model.id));

                if (server) {
                    email.set(data.model);
                }
            });

            app.listener.on('notifyemail:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                if (parseInt(data.model.project_id) === parseInt(app.project_id)) {
                    app.NotifyEmails.add(data.model);
                }
            });

            app.listener.on('notifyemail:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var email = app.NotifyEmails.get(parseInt(data.model.id));

                if (email) {
                    app.NotifyEmails.remove(email);
                }
            });
        },
        render: function () {
            if (app.NotifyEmails.length) {
                $('#no_notifyemails').hide();
                $('#notifyemail_list').show();
            } else {
                $('#no_notifyemails').show();
                $('#notifyemail_list').hide();
            }
        },
        addOne: function (file) {

            var view = new app.EmailView({
                model: file
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.NotifyEmails.each(this.addOne, this);
        }
    });

    app.EmailView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editEmail',
            'click .btn-delete': 'trashEmail'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#notifyemail-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        editEmail: function() {
            // FIXME: Sure this is wrong?
            $('#notifyemail_id').val(this.model.id);
            $('#notifyemail_name').val(this.model.get('name'));
            $('#notifyemail_address').val(this.model.get('email'));
        },
        trashEmail: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade notifyemail-trash');
        }
    });

})(jQuery);

var app = app || {};

(function ($) {
    // FIXME: This seems very wrong
    $('#sharefile').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('sharedFiles.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('sharedFiles.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#file_id').val('');
            $('#name').val('');
            $('#file').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('body').delegate('.sharefile-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file = app.SharedFiles.get($('#model_id').val());

        file.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    // FIXME: This seems very wrong
    $('#sharefile button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file_id = $('#file_id').val();

        if (file_id) {
            var file = app.SharedFiles.get(file_id);
        } else {
            var file = new app.SharedFile();
        }

        file.save({
            name:            $('#name').val(),
            file:            $('#file').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!file_id) {
                    app.SharedFiles.add(response);
                }
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    app.SharedFile = Backbone.Model.extend({
        urlRoot: '/shared-files'
    });

    var SharedFiles = Backbone.Collection.extend({
        model: app.SharedFile
    });

    app.SharedFiles = new SharedFiles();

    app.SharedFilesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#file_list tbody');

            $('#no_files').show();
            $('#file_list').hide();

            this.listenTo(app.SharedFiles, 'add', this.addOne);
            this.listenTo(app.SharedFiles, 'reset', this.addAll);
            this.listenTo(app.SharedFiles, 'remove', this.addAll);
            this.listenTo(app.SharedFiles, 'all', this.render);

            app.listener.on('sharedfile:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var share = app.SharedFiles.get(parseInt(data.model.id));

                if (share) {
                    share.set(data.model);
                }
            });

            app.listener.on('sharedfile:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    app.SharedFiles.add(data.model);
                }
            });

            app.listener.on('sharedfile:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var share = app.SharedFiles.get(parseInt(data.model.id));

                if (share) {
                    app.SharedFiles.remove(share);
                }
            });
        },
        render: function () {
            if (app.SharedFiles.length) {
                $('#no_files').hide();
                $('#file_list').show();
            } else {
                $('#no_files').show();
                $('#file_list').hide();
            }
        },
        addOne: function (file) {

            var view = new app.FileView({
                model: file
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.SharedFiles.each(this.addOne, this);
        }
    });

    app.FileView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editFile',
            'click .btn-delete': 'trashFile'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#files-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        editFile: function() {
            // FIXME: Sure this is wrong?
            $('#file_id').val(this.model.id);
            $('#name').val(this.model.get('name'));
            $('#file').val(this.model.get('file'));
        },
        trashFile: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade sharefile-trash');
        }
    });

})(jQuery);

var app = app || {};

(function ($) {

    var editor;
    var previewfile;

    $('#configfile, #view-configfile').on('hidden.bs.modal', function (event) {
        editor.destroy();
    });

    $('#view-configfile').on('show.bs.modal', function (event) {
        editor = ace.edit('preview-content');
        editor.setReadOnly(true);
        editor.getSession().setUseWrapMode(true);

        var extension = previewfile.substr(previewfile.lastIndexOf('.') + 1).toLowerCase();

        if (extension === 'php' || extension === 'ini') {
            editor.getSession().setMode('ace/mode/' + extension);
        } else if (extension === 'yml') {
            editor.getSession().setMode('ace/mode/yaml');
        }
    });

    // FIXME: This seems very wrong
    $('#configfile').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('configFiles.create');

        editor = ace.edit('config-file-content');

        var filename = $('#config-file-path').val();
        var extension = filename.substr(filename.lastIndexOf('.') + 1).toLowerCase();

        if (extension === 'php' || extension === 'ini') {
            editor.getSession().setMode('ace/mode/' + extension);
        } else if (extension === 'yml') {
            editor.getSession().setMode('ace/mode/yaml');
        }

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('configFiles.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#config_file_id').val('');
            $('#config-file-name').val('');
            $('#config-file-path').val('');
            editor.setValue('');
            editor.gotoLine(1);
        }

        modal.find('.modal-title span').text(title);
    });


    // FIXME: This seems very wrong
    $('body').delegate('.configfile-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file = app.ConfigFiles.get($('#model_id').val());

        file.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    // FIXME: This seems very wrong
    $('#configfile button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var config_file_id = $('#config_file_id').val();

        if (config_file_id) {
            var file = app.ConfigFiles.get(config_file_id);
        } else {
            var file = new app.ConfigFile();
        }

        file.save({
            name:       $('#config-file-name').val(),
            path:       $('#config-file-path').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val(),
            content:    editor.getValue()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!config_file_id) {
                    app.ConfigFiles.add(response);
                }

                editor.setValue('');
                editor.gotoLine(1);
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    app.ConfigFile = Backbone.Model.extend({
        urlRoot: '/config-file'
    });

    var ConfigFiles = Backbone.Collection.extend({
        model: app.ConfigFile
    });

    app.ConfigFiles = new ConfigFiles();

    app.ConfigFilesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#configfile_list tbody');

            $('#no_configfiles').show();
            $('#configfile_list').hide();

            this.listenTo(app.ConfigFiles, 'add', this.addOne);
            this.listenTo(app.ConfigFiles, 'reset', this.addAll);
            this.listenTo(app.ConfigFiles, 'remove', this.addAll);
            this.listenTo(app.ConfigFiles, 'all', this.render);

            app.listener.on('configfile:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var file = app.ConfigFiles.get(parseInt(data.model.id));

                if (file) {
                    file.set(data.model);
                }
            });

            app.listener.on('configfile:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    app.ConfigFiles.add(data.model);
                }
            });

            app.listener.on('configfile:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var file = app.ConfigFiles.get(parseInt(data.model.id));

                if (file) {
                    app.ConfigFiles.remove(file);
                }
            });
        },
        render: function () {
            if (app.ConfigFiles.length) {
                $('#no_configfiles').hide();
                $('#configfile_list').show();
            } else {
                $('#no_configfiles').show();
                $('#configfile_list').hide();
            }
        },
        addOne: function (file) {

            var view = new app.ConfigFileView({
                model: file
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.ConfigFiles.each(this.addOne, this);
        }
    });

    app.ConfigFileView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editFile',
            'click .btn-delete': 'trashFile',
            'click .btn-view': 'viewFile'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#config-files-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        viewFile: function() {
            previewfile = this.model.get('path');
            $('#preview-content').text(this.model.get('content'));
        },
        editFile: function() {
            // FIXME: Sure this is wrong?
            $('#config_file_id').val(this.model.id);
            $('#config-file-name').val(this.model.get('name'));
            $('#config-file-path').val(this.model.get('path'));
            $('#config-file-content').text(this.model.get('content'));
        },
        trashFile: function () {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade configfile-trash');
        }
    });

})(jQuery);

var app = app || {};

(function ($) {
    var SUCCESS = 0;
    var FAILED = 1;

    $('#checkurl').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('checkUrls.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('checkUrls.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#url_id').val('');
            $('#title').val('');
            $('#url').val('');
            $('#period_5').prop('checked', true);
            //$('#is_report').prop('checked', false);
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.checkurl-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin').removeClass('ion-trash-a');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var url = app.CheckUrls.get($('#model_id').val());

        url.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin').addClass('ion-trash-a');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin').addClass('ion-trash-a');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#checkurl button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var url_id = $('#url_id').val();

        if (url_id) {
            var url = app.CheckUrls.get(url_id);
        } else {
            var url = new app.CheckUrl();
        }

        url.save({
            title:      $('#title').val(),
            url:        $('#url').val(),
            is_report:  true, // $('#is_report').prop('checked'),
            period:     parseInt($('input[name=period]:checked').val()),
            project_id: $('input[name="project_id"]').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!url_id) {
                    app.CheckUrls.add(response);
                }
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    app.CheckUrl = Backbone.Model.extend({
        urlRoot: '/check-url'
    });

    var CheckUrls = Backbone.Collection.extend({
        model: app.CheckUrl
    });

    app.CheckUrls = new CheckUrls();

    app.CheckUrlsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#checkurl_list tbody');

            $('#no_checkurls').show();
            $('#checkurl_list').hide();

            this.listenTo(app.CheckUrls, 'add', this.addOne);
            this.listenTo(app.CheckUrls, 'reset', this.addAll);
            this.listenTo(app.CheckUrls, 'remove', this.addAll);
            this.listenTo(app.CheckUrls, 'all', this.render);

            app.listener.on('checkurl:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var link = app.CheckUrls.get(parseInt(data.model.id));

                if (link) {
                    link.set(data.model);
                }
            });

            app.listener.on('checkurl:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                if (parseInt(data.model.project_id) === parseInt(app.project_id)) {
                    app.CheckUrls.add(data.model);
                }
            });

            app.listener.on('checkurl:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var link = app.CheckUrls.get(parseInt(data.model.id));

                if (link) {
                    app.CheckUrls.remove(link);
                }
            });
        },
        render: function () {
            if (app.CheckUrls.length) {
                $('#no_checkurls').hide();
                $('#checkurl_list').show();
            } else {
                $('#no_checkurls').show();
                $('#checkurl_list').hide();
            }
        },
        addOne: function (url) {
            var view = new app.CheckUrlView({
                model: url
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.CheckUrls.each(this.addOne, this);
        }
    });

    app.CheckUrlView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editUrl',
            'click .btn-delete': 'trashUrl'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#checkUrls-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.status_css = 'primary';
            data.icon_css   = 'question';
            data.status     = trans('checkUrls.untested');

            if (parseInt(data.last_status) === FAILED) {
                data.status_css = 'danger';
                data.icon_css   = 'warning';
                data.status     = trans('checkUrls.failed');
            } else if (parseInt(data.last_status) === SUCCESS) {
                data.status_css = 'success';
                data.icon_css   = 'check';
                data.status     = trans('checkUrls.successful');
            }

            data.interval_label = data.period + ' ' + trans('checkUrls.length');

            // data.report = trans('app.no');

            // if (data.is_report) {
            //     data.report = trans('app.ues');
            // }

            this.$el.html(this.template(data));

            return this;
        },
        editUrl: function() {
            $('#url_id').val(this.model.id);
            $('#title').val(this.model.get('title'));
            $('#url').val(this.model.get('url'));
            $('#period_' + this.model.get('period')).prop('checked', true);
            $('#is_report').prop('checked', this.model.get('is_report'));
        },
        trashUrl: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade checkurl-trash');
        }
    });

})(jQuery);

var app = app || {};

(function ($) {
   // FIXME: This seems very wrong
    $('#variable').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('variables.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('variables.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#variable_id').val('');
            $('#variable_name').val('');
            $('#variable_value').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('body').delegate('.variable-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var variable = app.Variables.get($('#model_id').val());

        variable.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    // FIXME: This seems very wrong
    $('#variable button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var variable_id = $('#variable_id').val();

        if (variable_id) {
            var variable = app.Variables.get(variable_id);
        } else {
            var variable = new app.Variable();
        }

        variable.save({
            name:            $('#variable_name').val(),
            value:           $('#variable_value').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!variable_id) {
                    app.Variables.add(response);
                }
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    app.Variable = Backbone.Model.extend({
        urlRoot: '/variables',
        initialize: function() {

        }
    });

    var Variables = Backbone.Collection.extend({
        model: app.Variable
    });

    app.Variables = new Variables();

    app.VariablesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#variable_list tbody');

            $('#variable_list').hide();

            this.listenTo(app.Variables, 'add', this.addOne);
            this.listenTo(app.Variables, 'reset', this.addAll);
            this.listenTo(app.Variables, 'remove', this.addAll);
            this.listenTo(app.Variables, 'all', this.render);

            app.listener.on('variable:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                $('#variable_' + data.model.id).html(data.model.name);

                var variable = app.Variables.get(parseInt(data.model.id));

                if (variable) {
                    variable.set(data.model);
                }
            });

            app.listener.on('variable:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    app.Variables.add(data.model);
                }
            });

            app.listener.on('variable:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var variable = app.Variables.get(parseInt(data.model.id));

                if (variable) {
                    app.Variables.remove(variable);
                }
            });
        },
        render: function () {
            if (app.Variables.length) {
                $('#variable_list').show();
            } else {
                $('#variable_list').hide();
            }
        },
        addOne: function (variable) {

            var view = new app.VariableView({
                model: variable
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Variables.each(this.addOne, this);
        }
    });

    app.VariableView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editVariable',
            'click .btn-delete': 'trashVariable'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#variable-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        editVariable: function() {
            $('#variable_id').val(this.model.id);
            $('#variable_name').val(this.model.get('name'));
            $('#variable_value').val(this.model.get('value'));
        },
        trashVariable: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade variable-trash');
        }
    });
})(jQuery);

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

var app = app || {};

(function ($) {
    $('.command-list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('command-id'));
            });

            $.ajax({
                url: '/commands/reorder',
                method: 'POST',
                data: {
                    commands: ids
                }
            });
        }
    });

    var editor;

    $('#command').on('hidden.bs.modal', function (event) {
        editor.destroy();
    });

    // FIXME: This seems very wrong
    $('#command').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('commands.create');

        editor = ace.edit('command_script');
        editor.getSession().setMode('ace/mode/sh');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('commands.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#command_id').val('');
            $('#command_step').val(button.data('step'));
            $('#command_name').val('');
            editor.setValue('');
            editor.gotoLine(1);
            $('#command_user').val('');
            $('#command_optional').prop('checked', false);
            $('#command_default_on').prop('checked', false);
            $('#command_default_on_row').addClass('hide');

            $('.command-server').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('body').delegate('.command-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var command = app.Commands.get($('#model_id').val());

        command.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#command_optional').on('change', function (event) {
        $('#command_default_on_row').addClass('hide');
        if ($(this).is(':checked') === true) {
            $('#command_default_on_row').removeClass('hide');
        }
    });

    // FIXME: This seems very wrong
    $('#command button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find(':input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var command_id = $('#command_id').val();

        if (command_id) {
            var command = app.Commands.get(command_id);
        } else {
            var command = new app.Command();
        }

        var server_ids = [];

        $('.command-server:checked').each(function() {
            server_ids.push($(this).val());
        });

        command.save({
            name:            $('#command_name').val(),
            script:          editor.getValue(),
            user:            $('#command_user').val(),
            step:            $('#command_step').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val(),
            servers:         server_ids,
            optional:        $('#command_optional').is(':checked'),
            default_on:      $('#command_default_on').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find(':input').removeAttr('disabled');

                if (!command_id) {
                    app.Commands.add(response);
                }

                editor.setValue('');
                editor.gotoLine(1);
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find(':input').removeAttr('disabled');
            }
        });
    });

    app.Command = Backbone.Model.extend({
        urlRoot: '/commands',
        defaults: function() {
            return {
                order: app.Commands.nextOrder()
            };
        },
        isAfter: function() {
            return (parseInt(this.get('step')) % 3 === 0);
        }
    });

    var Commands = Backbone.Collection.extend({
        model: app.Command,
        comparator: 'order',
        nextOrder: function() {
            if (!this.length) {
                return 1;
            }

            return this.last().get('order') + 1;
        }
    });

    app.Commands = new Commands();

    app.CommandsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$beforeList = $('#commands-before .command-list tbody');
            this.$afterList = $('#commands-after .command-list tbody');

            $('.no-commands').show();
            $('.command-list').hide();

            this.listenTo(app.Commands, 'add', this.addOne);
            this.listenTo(app.Commands, 'reset', this.addAll);
            this.listenTo(app.Commands, 'remove', this.addAll);
            this.listenTo(app.Commands, 'all', this.render);

            // FIXME: Need to regenerate the order!

            app.listener.on('command:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var command = app.Commands.get(parseInt(data.model.id));

                if (command) {
                    command.set(data.model);
                }
            });

            app.listener.on('command:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                //if (data.model.targetable_type == app.targetable_type && parseInt(data.model.targetable_id) === parseInt(app.targetable_id)) {

                    // Make sure the command is for this action (clone, install, activate, purge)
                    if (parseInt(data.model.step) + 1 === parseInt(app.command_action) || parseInt(data.model.step) - 1 === parseInt(app.command_action)) {
                        app.Commands.add(data.model);
                    }
                }
            });

            app.listener.on('command:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var command = app.Commands.get(parseInt(data.model.id));

                if (command) {
                    app.Commands.remove(command);
                }
            });
        },
        render: function () {
            var before = app.Commands.find(function(model) {
                return !model.isAfter();
            });

            if (typeof before !== 'undefined') {
                $('#commands-before .no-commands').hide();
                $('#commands-before .command-list').show();
            } else {
                $('#commands-before .no-commands').show();
                $('#commands-before .command-list').hide();
            }

            var after = app.Commands.find(function(model) {
                return model.isAfter();
            });

            if (typeof after !== 'undefined') {
                $('#commands-after .no-commands').hide();
                $('#commands-after .command-list').show();
            } else {
                $('#commands-after .no-commands').show();
                $('#commands-after .command-list').hide();
            }
        },
        addOne: function (command) {
            var view = new app.CommandView({
                model: command
            });

            if (command.isAfter()) {
                this.$afterList.append(view.render().el);
            } else {
                this.$beforeList.append(view.render().el);
            }
        },
        addAll: function () {
            this.$beforeList.html('');
            this.$afterList.html('');
            app.Commands.each(this.addOne, this);
        }
    });

    app.CommandView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editCommand',
            'click .btn-delete': 'trashCommand'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#command-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        editCommand: function() {
            // FIXME: Sure this is wrong?
            $('#command_id').val(this.model.id);
            $('#command_step').val(this.model.get('step'));
            $('#command_name').val(this.model.get('name'));
            $('#command_script').text(this.model.get('script'));
            $('#command_user').val(this.model.get('user'));
            $('#command_optional').prop('checked', (this.model.get('optional') === true));
            $('#command_default_on').prop('checked', (this.model.get('default_on') === true));

            $('#command_default_on_row').addClass('hide');
            if (this.model.get('optional') === true) {
                $('#command_default_on_row').removeClass('hide');
            }

            $('.command-server').prop('checked', false);
            $(this.model.get('servers')).each(function (index, server) {
                $('#command_server_' + server.id).prop('checked', true);
            });
        },
        trashCommand: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade command-trash');
        }
    });
})(jQuery);

var app = app || {};

(function ($) {
   // FIXME: This seems very wrong
    $('#user').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('users.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.existing-only', modal).hide();
        $('.new-only', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();


        if (button.hasClass('btn-edit')) {
            title = trans('users.edit');
            $('.btn-danger', modal).show();
            $('.existing-only', modal).show();
        } else {
            $('#user_id').val('');
            $('#user_name').val('');
            $('#user_nickname').val('');
            $('#user_email').val('');
            $('#user_password').val('');
            $('#user_password_confirmation').val('');

            $('.new-only', modal).show();
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('body').delegate('.user-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var user = app.Users.get($('#model_id').val());

        user.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    // FIXME: This seems very wrong
    $('#user button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var user_id = $('#user_id').val();

        if (user_id) {
            var user = app.Users.get(user_id);
        } else {
            var user = new app.User();
        }

        user.save({
            name:                  $('#user_name').val(),
            nickname:              $('#user_nickname').val(),
            email:                 $('#user_email').val(),
            password:              $('#user_password').val(),
            password_confirmation: $('#user_password_confirmation').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!user_id) {
                    app.Users.add(response);
                }
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    app.User = Backbone.Model.extend({
        urlRoot: '/admin/users',
        initialize: function() {

        }
    });

    var Users = Backbone.Collection.extend({
        model: app.User
    });

    app.Users = new Users();

    app.UsersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#user_list tbody');

            this.listenTo(app.Users, 'add', this.addOne);
            this.listenTo(app.Users, 'reset', this.addAll);
            this.listenTo(app.Users, 'remove', this.addAll);
            this.listenTo(app.Users, 'all', this.render);

            app.listener.on('user:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var user = app.Users.get(parseInt(data.model.id));

                if (user) {
                    user.set(data.model);
                }
            });

            app.listener.on('user:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                app.Users.add(data.model);
            });

            app.listener.on('user:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var user = app.Users.get(parseInt(data.model.id));

                if (user) {
                    app.Users.remove(user);
                }
            });
        },
        addOne: function (user) {
            var view = new app.UserView({
                model: user
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Users.each(this.addOne, this);
        }
    });

    app.UserView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editUser',
            'click .btn-delete': 'trashUser'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#user-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.created = moment(data.created_at).format('YYYY-MM-DD HH:mm:ss');

            this.$el.html(this.template(data));

            return this;
        },
        editUser: function() {
            $('#user_id').val(this.model.id);
            $('#user_name').val(this.model.get('name'));
            $('#user_nickname').val(this.model.get('nickname'));
            $('#user_email').val(this.model.get('email'));
        },
        trashUser: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade user-trash');
        }
    });
})(jQuery);

var app = app || {};

(function ($) {
    $('#group_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('group-id'));
            });

            $.ajax({
                url: '/admin/groups/reorder',
                method: 'POST',
                data: {
                    groups: ids
                }
            });
        }
    });

   // FIXME: This seems very wrong
    $('#group').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('groups.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('groups.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#group_id').val('');
            $('#group_name').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('body').delegate('.group-trash button.btn-delete','click', function (event) {

        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var group = app.Groups.get($('#model_id').val());

        group.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    // FIXME: This seems very wrong
    $('#group button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var group_id = $('#group_id').val();

        if (group_id) {
            var group = app.Groups.get(group_id);
        } else {
            var group = new app.Group();
        }

        group.save({
            name: $('#group_name').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!group_id) {
                    app.Groups.add(response);
                }
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    app.Group = Backbone.Model.extend({
        urlRoot: '/admin/groups',
        initialize: function() {

        }
    });

    var Groups = Backbone.Collection.extend({
        model: app.Group
    });

    app.Groups = new Groups();

    app.GroupsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#group_list tbody');

            $('#group_list').hide();
            $('#no_groups').show();

            this.listenTo(app.Groups, 'add', this.addOne);
            this.listenTo(app.Groups, 'reset', this.addAll);
            this.listenTo(app.Groups, 'remove', this.addAll);
            this.listenTo(app.Groups, 'all', this.render);

            app.listener.on('group:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                $('#group_' + data.model.id).html(data.model.name);

                var group = app.Groups.get(parseInt(data.model.id));

                if (group) {
                    group.set(data.model);
                }
            });

            app.listener.on('group:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                app.Groups.add(data.model);
            });

            app.listener.on('group:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var group = app.Groups.get(parseInt(data.model.id));

                if (group) {
                    app.Groups.remove(group);
                }

                $('#group_' + data.model.id).parent('li').remove();

                if (parseInt(data.model.id) === parseInt(app.group_id)) {
                    window.location.href = '/';
                }
            });
        },
        render: function () {
            if (app.Groups.length) {
                $('#no_groups').hide();
                $('#group_list').show();
            } else {
                $('#no_groups').show();
                $('#group_list').hide();
            }
        },
        addOne: function (group) {

            var view = new app.GroupView({
                model: group
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Groups.each(this.addOne, this);
        }
    });

    app.GroupView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editGroup',
            'click .btn-delete': 'trashGroup'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#group-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        editGroup: function() {
            $('#group_id').val(this.model.id);
            $('#group_name').val(this.model.get('name'));
        },
        trashGroup: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade group-trash');
        }

    });
})(jQuery);

var iframeCount = 0;

function Uploader(options) {
  if (!(this instanceof Uploader)) {
    return new Uploader(options);
  }
  if (isString(options)) {
    options = {trigger: options};
  }

  var settings = {
    trigger: null,
    name: null,
    action: null,
    data: null,
    accept: null,
    change: null,
    error: null,
    multiple: true,
    success: null
  };
  if (options) {
    $.extend(settings, options);
  }
  var $trigger = $(settings.trigger);

  settings.action = settings.action || $trigger.data('action') || '/upload';
  settings.name = settings.name || $trigger.attr('name') || $trigger.data('name') || 'file';
  settings.data = settings.data || parse($trigger.data('data'));
  settings.accept = settings.accept || $trigger.data('accept');
  settings.success = settings.success || $trigger.data('success');
  this.settings = settings;

  this.setup();
  this.bind();
}

// initialize
// create input, form, iframe
Uploader.prototype.setup = function() {
  this.form = $(
    '<form method="post" enctype="multipart/form-data"'
    + 'target="" action="' + this.settings.action + '" />'
  );

  this.iframe = newIframe();
  this.form.attr('target', this.iframe.attr('name'));

  var data = this.settings.data;
  this.form.append(createInputs(data));
  if (window.FormData) {
    this.form.append(createInputs({'_uploader_': 'formdata'}));
  } else {
    this.form.append(createInputs({'_uploader_': 'iframe'}));
  }

  var input = document.createElement('input');
  input.type = 'file';
  input.name = this.settings.name;
  if (this.settings.accept) {
    input.accept = this.settings.accept;
  }
  if (this.settings.multiple) {
    input.multiple = true;
    input.setAttribute('multiple', 'multiple');
  }
  this.input = $(input);

  var $trigger = $(this.settings.trigger);
  this.input.attr('hidefocus', true).css({
    position: 'absolute',
    top: 0,
    right: 0,
    opacity: 0,
    outline: 0,
    cursor: 'pointer',
    height: $trigger.outerHeight(),
    fontSize: Math.max(64, $trigger.outerHeight() * 5)
  });
  this.form.append(this.input);
  this.form.css({
    position: 'absolute',
    top: $trigger.offset().top,
    left: $trigger.offset().left,
    overflow: 'hidden',
    width: $trigger.outerWidth(),
    height: $trigger.outerHeight(),
    zIndex: findzIndex($trigger) + 10
  }).appendTo('body');
  return this;
};

// bind events
Uploader.prototype.bind = function() {
  var self = this;
  var $trigger = $(self.settings.trigger);
  $trigger.mouseenter(function() {
    self.form.css({
      top: $trigger.offset().top,
      left: $trigger.offset().left,
      width: $trigger.outerWidth(),
      height: $trigger.outerHeight()
    });
  });
  self.bindInput();
};

Uploader.prototype.bindInput = function() {
  var self = this;
  self.input.change(function(e) {
    // ie9 don't support FileList Object
    // http://stackoverflow.com/questions/12830058/ie8-input-type-file-get-files
    self._files = this.files || [{
      name: e.target.value
    }];
    var file = self.input.val();
    if (self.settings.change) {
      self.settings.change.call(self, self._files);
    } else if (file) {
      return self.submit();
    }
  });
};

// handle submit event
// prepare for submiting form
Uploader.prototype.submit = function() {
  var self = this;
  if (window.FormData && self._files) {
    // build a FormData
    var form = new FormData(self.form.get(0));
    // use FormData to upload
    form.append(self.settings.name, self._files);

    var optionXhr;
    if (self.settings.progress) {
      // fix the progress target file
      var files = self._files;
      optionXhr = function() {
        var xhr = $.ajaxSettings.xhr();
        if (xhr.upload) {
          xhr.upload.addEventListener('progress', function(event) {
            var percent = 0;
            var position = event.loaded || event.position; /*event.position is deprecated*/
            var total = event.total;
            if (event.lengthComputable) {
                percent = Math.ceil(position / total * 100);
            }
            self.settings.progress(event, position, total, percent, files);
          }, false);
        }
        return xhr;
      };
    }
    $.ajax({
      url: self.settings.action,
      type: 'post',
      processData: false,
      contentType: false,
      data: form,
      xhr: optionXhr,
      context: this,
      success: self.settings.success,
      error: self.settings.error
    });
    return this;
  } else {
    // iframe upload
    self.iframe = newIframe();
    self.form.attr('target', self.iframe.attr('name'));
    $('body').append(self.iframe);
    self.iframe.one('load', function() {
      // https://github.com/blueimp/jQuery-File-Upload/blob/9.5.6/js/jquery.iframe-transport.js#L102
      // Fix for IE endless progress bar activity bug
      // (happens on form submits to iframe targets):
      $('<iframe src="javascript:false;"></iframe>')
        .appendTo(self.form)
        .remove();
      var response;
      try {
        response = $(this).contents().find("body").html();
      } catch (e) {
        response = "cross-domain";
      }
      $(this).remove();
      if (!response) {
        if (self.settings.error) {
          self.settings.error(self.input.val());
        }
      } else {
        if (self.settings.success) {
          self.settings.success(response);
        }
      }
    });
    self.form.submit();
  }
  return this;
};

Uploader.prototype.refreshInput = function() {
  //replace the input element, or the same file can not to be uploaded
  var newInput = this.input.clone();
  this.input.before(newInput);
  this.input.off('change');
  this.input.remove();
  this.input = newInput;
  this.bindInput();
};

// handle change event
// when value in file input changed
Uploader.prototype.change = function(callback) {
  if (!callback) {
    return this;
  }
  this.settings.change = callback;
  return this;
};

// handle when upload success
Uploader.prototype.success = function(callback) {
  var me = this;
  this.settings.success = function(response) {
    me.refreshInput();
    if (callback) {
      callback(response);
    }
  };

  return this;
};

// handle when upload success
Uploader.prototype.error = function(callback) {
  var me = this;
  this.settings.error = function(response) {
    if (callback) {
      me.refreshInput();
      callback(response);
    }
  };
  return this;
};

// enable
Uploader.prototype.enable = function(){
  this.input.prop('disabled', false);
  this.input.css('cursor', 'pointer');
};

// disable
Uploader.prototype.disable = function(){
  this.input.prop('disabled', true);
  this.input.css('cursor', 'not-allowed');
};

// Helpers
// -------------

function isString(val) {
  return Object.prototype.toString.call(val) === '[object String]';
}

function createInputs(data) {
  if (!data) return [];

  var inputs = [], i;
  for (var name in data) {
    i = document.createElement('input');
    i.type = 'hidden';
    i.name = name;
    i.value = data[name];
    inputs.push(i);
  }
  return inputs;
}

function parse(str) {
  if (!str) return {};
  var ret = {};

  var pairs = str.split('&');
  var unescape = function(s) {
    return decodeURIComponent(s.replace(/\+/g, ' '));
  };

  for (var i = 0; i < pairs.length; i++) {
    var pair = pairs[i].split('=');
    var key = unescape(pair[0]);
    var val = unescape(pair[1]);
    ret[key] = val;
  }

  return ret;
}

function findzIndex($node) {
  var parents = $node.parentsUntil('body');
  var zIndex = 0;
  for (var i = 0; i < parents.length; i++) {
    var item = parents.eq(i);
    if (item.css('position') !== 'static') {
      zIndex = parseInt(item.css('zIndex'), 10) || zIndex;
    }
  }
  return zIndex;
}

function newIframe() {
  var iframeName = 'iframe-uploader-' + iframeCount;
  var iframe = $('<iframe name="' + iframeName + '" />').hide();
  iframeCount += 1;
  return iframe;
}

function MultipleUploader(options) {
  if (!(this instanceof MultipleUploader)) {
    return new MultipleUploader(options);
  }

  if (isString(options)) {
    options = {trigger: options};
  }
  var $trigger = $(options.trigger);

  var uploaders = [];
  $trigger.each(function(i, item) {
    options.trigger = item;
    uploaders.push(new Uploader(options));
  });
  this._uploaders = uploaders;
}
MultipleUploader.prototype.submit = function() {
  $.each(this._uploaders, function(i, item) {
    item.submit();
  });
  return this;
};
MultipleUploader.prototype.change = function(callback) {
  $.each(this._uploaders, function(i, item) {
    item.change(callback);
  });
  return this;
};
MultipleUploader.prototype.success = function(callback) {
  $.each(this._uploaders, function(i, item) {
    item.success(callback);
  });
  return this;
};
MultipleUploader.prototype.error = function(callback) {
  $.each(this._uploaders, function(i, item) {
    item.error(callback);
  });
  return this;
};
MultipleUploader.prototype.enable = function (){
  $.each(this._uploaders, function (i, item){
    item.enable();
  });
  return this;
};
MultipleUploader.prototype.disable = function (){
  $.each(this._uploaders, function (i, item){
    item.disable();
  });
  return this;
};
MultipleUploader.Uploader = Uploader;
var app = app || {};

(function ($) {

        // FIXME: This seems very wrong
    $('#issue').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('issues.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('issues.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#issue_id').val('');
            $('#issue_title').val('');
            $('#issue_content').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('body').delegate('.issue-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var issue = app.Issues.get($('#model_id').val());

        issue.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

        // FIXME: This seems very wrong
    $('#issue button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var issue_id = $('#issue_id').val();

        if (issue_id) {
            var issue = app.Issues.get(issue_id);
        } else {
            var issue = new app.Issue();
        }

        issue.save({
            title:      $('#issue_title').val(),
            content:    $('#issue_content').val(),
            project_id: $('input[name="project_id"]').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!issue_id) {
                    app.Issues.add(response);
                }
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    app.Issue = Backbone.Model.extend({
        urlRoot: '/issues'
    });

    var Issues = Backbone.Collection.extend({
        model: app.Issue
    });

    app.Issues = new Issues();

    app.IssuesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#issue_list tbody');

            $('#no_issues').show();
            $('#issue_list').hide();

            this.listenTo(app.Issues, 'add', this.addOne);
            this.listenTo(app.Issues, 'reset', this.addAll);
            this.listenTo(app.Issues, 'remove', this.addAll);
            this.listenTo(app.Issues, 'all', this.render);

            app.listener.on('issue:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var issue = app.Issues.get(parseInt(data.model.id));

                if (issue) {
                    issue.set(data.model);
                }
            });

            app.listener.on('issue:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                if (parseInt(data.model.project_id) === parseInt(app.project_id)) {
                    app.Issues.add(data.model);
                }
            });

            app.listener.on('issue:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var issue = app.Issues.get(parseInt(data.model.id));

                if (issue) {
                    app.Issues.remove(issue);
                }
            });
        },
        render: function () {
            if (app.Issues.length) {
                $('#no_issues').hide();
                $('#issue_list').show();
            } else {
                $('#no_issues').show();
                $('#issue_list').hide();
            }
        },
        addOne: function (issue) {

            var view = new app.IssueView({
                model: issue
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Issues.each(this.addOne, this);
        }
    });

    app.IssueView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editIssue',
            'click .btn-delete': 'trashIssue'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#issue-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        editIssue: function() {
            // FIXME: Sure this is wrong?
            $('#issue_id').val(this.model.id);
            $('#issue_title').val(this.model.get('title'));
            $('#issue_content').val(this.model.get('content'));
        },
        trashIssue: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade issue-trash');
        }
    });

})(jQuery);
var app = app || {};

(function ($) {
    // Stop the uploader causing errors on pages it shouldn't be used
    if ($('#upload').length === 0) {
        return;
    }

    $('#skin').on('change', function() {
        $('body').removeClass();
        $("body").addClass('skin-' + $(this).find(':selected').val());
    });

    $('#two-factor-auth').on('change', function () {

        var container = $('.auth-code');

        if ($(this).is(':checked')) {
            container.removeClass('hide');
        } else {
            container.addClass('hide');
        }
    });

    $('#request-change-email').on('click', function() {
        var box = $(this).parents('.box');
        box.children('.overlay').removeClass('hide');
        $.post('/profile/email', function(res) {
            if (res == 'success') {
                box.children('.overlay').addClass('hide');
                box.find('.help-block').removeClass('hide');
            }
        });
    });

    var cropperData = {};
    $('.avatar>img').cropper({
        aspectRatio: 1 / 1,
        preview: '.avatar-preview',
        crop: function(data) {
            cropperData.dataX = Math.round(data.x);
            cropperData.dataY = Math.round(data.y);
            cropperData.dataHeight = Math.round(data.height);
            cropperData.dataWidth = Math.round(data.width);
            cropperData.dataRotate = Math.round(data.rotate);
        },
        built: function() {
            $('#upload-overlay').addClass('hide');
        }
    });

    var uploader = new Uploader({
        trigger: '#upload',
        name: 'file',
        action: '/profile/upload',
        accept: 'image/*',
        data: {
            '_token': $('meta[name="token"]').attr('content')
        },
        multiple: false,
        change: function(){
            $('#upload-overlay').removeClass('hide');
            this.submit();
        },
        error: function(file) {
            if (file.responseJSON.file) {
                alert(file.responseJSON.file.join(''));
            } else if (file.responseJSON.error) {
                alert(file.responseJSON.error.message);
            }

            $('#upload-overlay').addClass('hide');
        },
        success: function(response) {
            if( response.message === 'success') {
                $('.avatar>img').cropper('replace', response.image);
                cropperData.path = response.path;

                $('.current-avatar-preview').addClass('hide');
                $('.avatar-preview').removeClass('hide');
                $('#save-avatar').removeClass('hide');
            }
        }
    });

    $('#save-avatar').on('click', function(){
        $('#upload-overlay').removeClass('hide');
        $('.avatar-message .alert').addClass('hide');
        $.post('/profile/avatar', cropperData).success(function(resp) {
            $('#upload-overlay').addClass('hide');
            if (resp.image) {
                $('.avatar-message .alert.alert-success').removeClass('hide');
                $('#use-gravatar').removeClass('hide');
            } else {
                $('.avatar-message .alert.alert-danger').removeClass('hide');
            }
        });
    });

    $('#use-gravatar').on('click', function () {

        $('#upload-overlay').removeClass('hide');
        $('.avatar-message .alert').addClass('hide');

        $.post('/profile/gravatar').success(function(resp) {

            $('#upload-overlay').addClass('hide');

            // if (resp.image) {
                $('.avatar-message .alert.alert-success').removeClass('hide');
                $('.avatar-preview').addClass('hide');
                $('.current-avatar-preview').removeClass('hide');
                $('.current-avatar-preview').attr('src', resp.image);
                $('#use-gravatar').addClass('hide');
                $('#avatar-save-buttons button').addClass('hide');
            // } else {
            //     $('.avatar-preview').addClass('hide');
            //     $('#use-gravatar').removeClass('hide');
            // }
        });
    });
})(jQuery);
