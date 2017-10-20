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

    $('body').delegate('.group-trash button.btn-delete','click', function (event) {

        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var group = Fixhub.Groups.get($('#model_id').val());

        group.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('groups.delete_success'));
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#group button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var group_id = $('#group_id').val();

        if (group_id) {
            var group = Fixhub.Groups.get(group_id);
        } else {
            var group = new Fixhub.Group();
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

                var msg = trans('groups.edit_success');
                if (!group_id) {
                    Fixhub.Groups.add(response);
                    msg = trans('groups.create_success');
                }
                Fixhub.toast(msg);
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

    Fixhub.Group = Backbone.Model.extend({
        urlRoot: '/admin/groups',
        initialize: function() {

        }
    });

    var Groups = Backbone.Collection.extend({
        model: Fixhub.Group
    });

    Fixhub.Groups = new Groups();

    Fixhub.GroupsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#group_list tbody');

            $('#group_list').hide();
            $('#no_groups').show();

            this.listenTo(Fixhub.Groups, 'add', this.addOne);
            this.listenTo(Fixhub.Groups, 'reset', this.addAll);
            this.listenTo(Fixhub.Groups, 'remove', this.addAll);
            this.listenTo(Fixhub.Groups, 'all', this.render);

            Fixhub.listener.on('group:' + Fixhub.events.MODEL_CHANGED, function (data) {
                $('#group_' + data.model.id).html(data.model.name);

                var group = Fixhub.Groups.get(parseInt(data.model.id));

                if (group) {
                    group.set(data.model);
                }
            });

            Fixhub.listener.on('group:' + Fixhub.events.MODEL_CREATED, function (data) {
                Fixhub.Groups.add(data.model);
            });

            Fixhub.listener.on('group:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var group = Fixhub.Groups.get(parseInt(data.model.id));

                if (group) {
                    Fixhub.Groups.remove(group);
                }

                $('#group_' + data.model.id).parent('li').remove();

                if (parseInt(data.model.id) === parseInt(Fixhub.group_id)) {
                    window.location.href = '/';
                }
            });
        },
        render: function () {
            if (Fixhub.Groups.length) {
                $('#no_groups').hide();
                $('#group_list').show();
            } else {
                $('#no_groups').show();
                $('#group_list').hide();
            }
        },
        addOne: function (group) {

            var view = new Fixhub.GroupView({
                model: group
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.Groups.each(this.addOne, this);
        }
    });

    Fixhub.GroupView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
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
        edit: function() {
            $('#group_id').val(this.model.id);
            $('#group_name').val(this.model.get('name'));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade group-trash');
        }

    });
})(jQuery);

(function ($) {

    $('#provider_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('provider-id'));
            });

            $.ajax({
                url: '/admin/providers/reorder',
                method: 'POST',
                data: {
                    providers: ids
                }
            });
        }
    });

    $('#provider').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('providers.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('providers.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#provider_id').val('');
            $('#provider_name').val('');
            $('#provider_slug').val('');
            $('#provider_icon').val('');
            $('#provider_description').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.provider-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var provider = Fixhub.Providers.get($('#model_id').val());

        provider.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('providers.delete_success'));
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#provider button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var provider_id = $('#provider_id').val();

        if (provider_id) {
            var provider = Fixhub.Providers.get(provider_id);
        } else {
            var provider = new Fixhub.Provider();
        }

        provider.save({
            name:        $('#provider_name').val(),
            slug:        $('#provider_slug').val(),
            icon:        $('#provider_icon').val(),
            description: $('#provider_description').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('providers.edit_success');
                if (!provider_id) {
                    Fixhub.Providers.add(response);
                    msg = trans('providers.create_success');
                }
                Fixhub.toast(msg);
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


    Fixhub.Provider = Backbone.Model.extend({
        urlRoot: '/admin/providers'
    });

    var Providers = Backbone.Collection.extend({
        model: Fixhub.Provider,
        comparator: function(providerA, providerB) {
            if (providerA.get('name') > providerB.get('name')) {
                return -1; // before
            } else if (providerA.get('name') < providerB.get('name')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    Fixhub.Providers = new Providers();

    Fixhub.ProvidersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#provider_list tbody');

            $('#no_providers').show();
            $('#provider_list').hide();

            this.listenTo(Fixhub.Providers, 'add', this.addOne);
            this.listenTo(Fixhub.Providers, 'reset', this.addAll);
            this.listenTo(Fixhub.Providers, 'remove', this.addAll);
            this.listenTo(Fixhub.Providers, 'all', this.render);

            Fixhub.listener.on('provider:' + Fixhub.events.MODEL_CHANGED, function (data) {
                var provider = Fixhub.providers.get(parseInt(data.model.id));

                if (provider) {
                    provider.set(data.model);
                }
            });

            Fixhub.listener.on('provider:' + Fixhub.events.MODEL_CREATED, function (data) {
                    Fixhub.Providers.add(data.model);
            });

            Fixhub.listener.on('provider:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var provider = Fixhub.Providers.get(parseInt(data.model.id));

                if (provider) {
                    Fixhub.Providers.remove(provider);
                }
            });
        },
        render: function () {
            if (Fixhub.Providers.length) {
                $('#no_providers').hide();
                $('#provider_list').show();
            } else {
                $('#no_providers').show();
                $('#provider_list').hide();
            }
        },
        addOne: function (provider) {

            var view = new Fixhub.ProviderView({
                model: provider
            });

            this.$list.append(view.render().el);

            if (Fixhub.Providers.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.Providers.each(this.addOne, this);
        }
    });

    Fixhub.ProviderView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#provider-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#provider_id').val(this.model.id);
            $('#provider_name').val(this.model.get('name'));
            $('#provider_slug').val(this.model.get('slug'));
            $('#provider_icon').val(this.model.get('icon'));
            $('#provider_description').val(this.model.get('description'));

        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade provider-trash');
        }
    });
})(jQuery);
(function ($) {

    $('#project-clone').on('show.bs.modal', function(event) {
        var modal = $(this);
        $('.callout-danger', modal).hide();

        var project_id = $('#skeleton_id').val();
        $('form', modal).prop('action', '/admin/projects/' + project_id + '/clone');
    });

    $('#project-clone button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');
    });

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
            $('#project_group_id').select2(Fixhub.select2_options)
                                    .val($("#project_group_id option:selected").val())
                                    .trigger('change');
            $('#project_key_id').val($("#project_key_id option:first").val());
            $('#project_builds_to_keep').val(10);
            $('#project_url').val('');
            $('#project_build_url').val('');
            $('#project_allow_other_branch').prop('checked', true);
            $('#project_need_approve').prop('checked', false);
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.project-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var project = Fixhub.Projects.get($('#model_id').val());

        project.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('projects.delete_success'));
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#project button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var project_id = $('#project_id').val();

        if (project_id) {
            var project = Fixhub.Projects.get(project_id);
        } else {
            var project = new Fixhub.Project();
        }

        project.save({
            name:               $('#project_name').val(),
            repository:         $('#project_repository').val(),
            branch:             $('#project_branch').val(),
            group_id:           $('#project_group_id').val(),
            key_id:             $('#project_key_id').val(),
            builds_to_keep:     $('#project_builds_to_keep').val(),
            url:                $('#project_url').val(),
            build_url:          $('#project_build_url').val(),
            template_id:        $('#project_template_id') ? $('#project_template_id').val() : null,
            allow_other_branch: $('#project_allow_other_branch').is(':checked'),
            need_approve:       $('#project_need_approve').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('projects.edit_success');
                if (!project_id) {
                    Fixhub.Projects.add(response);
                    msg = trans('projects.create_success');
                }
                Fixhub.toast(msg);
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

    Fixhub.Project = Backbone.Model.extend({
        urlRoot: '/admin/projects'
    });

    var Projects = Backbone.Collection.extend({
        model: Fixhub.Project
    });

    Fixhub.Projects = new Projects();

    Fixhub.ProjectsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#project_list tbody');

            $('#project_list').hide();
            $('#no_projects').show();

            this.listenTo(Fixhub.Projects, 'add', this.addOne);
            this.listenTo(Fixhub.Projects, 'reset', this.addAll);
            this.listenTo(Fixhub.Projects, 'remove', this.addAll);
            this.listenTo(Fixhub.Projects, 'all', this.render);

            Fixhub.listener.on('project:' + Fixhub.events.MODEL_CHANGED, function (data) {
                var project = Fixhub.Projects.get(parseInt(data.model.id));

                if (project) {
                    if(Fixhub.group_id == undefined || Fixhub.group_id == data.model.group_id) {
                        project.set(data.model);
                    } else {
                        Fixhub.Projects.remove(project);
                    }
                }
            });

            Fixhub.listener.on('project:' + Fixhub.events.MODEL_CREATED, function (data) {
                Fixhub.Projects.add(data.model);
            });

            Fixhub.listener.on('project:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var project = Fixhub.Projects.get(parseInt(data.model.id));

                if (project) {
                    Fixhub.Projects.remove(project);
                }

                $('#project_' + data.model.id).parent('li').remove();

                if (parseInt(data.model.id) === parseInt(Fixhub.project_id)) {
                    window.location.href = '/';
                }
            });
        },
        render: function () {
            if (Fixhub.Projects.length) {
                $('#no_projects').hide();
                $('#project_list').show();
            } else {
                $('#no_projects').show();
                $('#project_list').hide();
            }
        },
        addOne: function (project) {
            var view = new Fixhub.ProjectView({
                model: project
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.Projects.each(this.addOne, this);
        }
    });

    Fixhub.ProjectView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit' : 'edit',
            'click .btn-clone': 'clone',
            'click .btn-trash': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#project-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.deployed = data.last_run ? moment(data.last_run).fromNow() : false;

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#project_id').val(this.model.id);
            $('#project_name').val(this.model.get('name'));
            $('#project_repository').val(this.model.get('repository'));
            $('#project_branch').val(this.model.get('branch'));
            //$('#project_group_id').val(this.model.get('group_id'));
            $('#project_group_id').select2(Fixhub.select2_options)
                                    .val(this.model.get('group_id'))
                                    .trigger('change');
            $('#project_key_id').val(this.model.get('key_id'));
            $('#project_builds_to_keep').val(this.model.get('builds_to_keep'));
            $('#project_url').val(this.model.get('url'));
            $('#project_build_url').val(this.model.get('build_url'));
            $('#project_allow_other_branch').prop('checked', (this.model.get('allow_other_branch') === true));
            $('#project_need_approve').prop('checked', (this.model.get('need_approve') === true));
        },
        clone: function() {
            $('#skeleton_id').val(this.model.id);
            $('#project_clone_name').val(this.model.get('name') + '_Clone');
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade project-trash');
        }
    });
})(jQuery);

(function ($) {

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

    $('body').delegate('.template-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var template = Fixhub.Templates.get($('#model_id').val());

        template.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('templates.delete_success'));
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#template button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var template_id = $('#template_id').val();

        if (template_id) {
            var template = Fixhub.Templates.get(template_id);
        } else {
            var template = new Fixhub.Template();
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

                var msg = trans('templates.edit_success');
                if (!template_id) {
                    Fixhub.Templates.add(response);
                    msg = trans('templates.create_success');
                    //window.location.href = '/admin/templates/' + response.id;
                }
                Fixhub.toast(msg);
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

    Fixhub.Template = Backbone.Model.extend({
        urlRoot: '/admin/templates'
    });

    var Templates = Backbone.Collection.extend({
        model: Fixhub.Template
    });

    Fixhub.Templates = new Templates();

    Fixhub.TemplatesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#template_list tbody');

            $('#template_list').hide();
            $('#no_templates').show();

            this.listenTo(Fixhub.Templates, 'add', this.addOne);
            this.listenTo(Fixhub.Templates, 'reset', this.addAll);
            this.listenTo(Fixhub.Templates, 'remove', this.addAll);
            this.listenTo(Fixhub.Templates, 'all', this.render);

            Fixhub.listener.on('template:' + Fixhub.events.MODEL_CHANGED, function (data) {
                var template = Fixhub.Templates.get(parseInt(data.model.id));

                if (template) {
                    template.set(data.model);
                }
            });

            Fixhub.listener.on('template:' + Fixhub.events.MODEL_CREATED, function (data) {
                Fixhub.Templates.add(data.model);
            });

            Fixhub.listener.on('template:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var template = Fixhub.Templates.get(parseInt(data.model.id));

                if (template) {
                    Fixhub.Templates.remove(template);
                }
            });
        },
        render: function () {
            if (Fixhub.Templates.length) {
                $('#no_templates').hide();
                $('#template_list').show();
            } else {
                $('#no_templates').show();
                $('#template_list').hide();
            }
        },
        addOne: function (template) {
            var view = new Fixhub.TemplateView({
                model: template
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.Templates.each(this.addOne, this);
        }
    });

    Fixhub.TemplateView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
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
        edit: function() {
            $('#template_id').val(this.model.id);
            $('#template_name').val(this.model.get('name'));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade template-trash');
        }
    });
})(jQuery);

(function ($) {

    $('#link_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('link-id'));
            });

            $.ajax({
                url: '/admin/links/reorder',
                method: 'POST',
                data: {
                    links: ids
                }
            });
        }
    });

    $('#link').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('links.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('links.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#link_id').val('');
            $('#link_title').val('');
            $('#link_url').val('');
            $('#link_description').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.link-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var link = Fixhub.Links.get($('#model_id').val());

        link.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('links.delete_success'));
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#link button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var link_id = $('#link_id').val();

        if (link_id) {
            var link = Fixhub.Links.get(link_id);
        } else {
            var link = new Fixhub.Link();
        }

        link.save({
            title:       $('#link_title').val(),
            url:         $('#link_url').val(),
            description: $('#link_description').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('links.edit_success');
                if (!link_id) {
                    Fixhub.Links.add(response);
                    msg = trans('links.create_success');
                }
                Fixhub.toast(msg);
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


    Fixhub.Link = Backbone.Model.extend({
        urlRoot: '/admin/links'
    });

    var Links = Backbone.Collection.extend({
        model: Fixhub.Link,
        comparator: function(linkA, linkB) {
            if (linkA.get('title') > linkB.get('title')) {
                return -1; // before
            } else if (linkA.get('title') < linkB.get('title')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    Fixhub.Links = new Links();

    Fixhub.LinksTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#link_list tbody');

            $('#no_links').show();
            $('#link_list').hide();

            this.listenTo(Fixhub.Links, 'add', this.addOne);
            this.listenTo(Fixhub.Links, 'reset', this.addAll);
            this.listenTo(Fixhub.Links, 'remove', this.addAll);
            this.listenTo(Fixhub.Links, 'all', this.render);

            Fixhub.listener.on('link:' + Fixhub.events.MODEL_CHANGED, function (data) {
                var link = Fixhub.Links.get(parseInt(data.model.id));

                if (link) {
                    link.set(data.model);
                }
            });

            Fixhub.listener.on('link:' + Fixhub.events.MODEL_CREATED, function (data) {
                Fixhub.Links.add(data.model);
            });

            Fixhub.listener.on('link:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var link = Fixhub.Links.get(parseInt(data.model.id));

                if (link) {
                    Fixhub.Links.remove(link);
                }
            });
        },
        render: function () {
            if (Fixhub.Links.length) {
                $('#no_links').hide();
                $('#link_list').show();
            } else {
                $('#no_links').show();
                $('#link_list').hide();
            }
        },
        addOne: function (link) {

            var view = new Fixhub.LinkView({
                model: link
            });

            this.$list.append(view.render().el);

            if (Fixhub.Links.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.Links.each(this.addOne, this);
        }
    });

    Fixhub.LinkView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#link-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#link_id').val(this.model.id);
            $('#link_title').val(this.model.get('title'));
            $('#link_url').val(this.model.get('url'));
            $('#link_description').val(this.model.get('description'));

        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade link-trash');
        }
    });
})(jQuery);
(function ($) {

    $('#tip_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500
    });

    $('#tip').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('tips.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('tips.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#tip_id').val('');
            $('#tip_body').val('');
            $('#tip_status').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.tip-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var tip = Fixhub.Tips.get($('#model_id').val());

        tip.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('tips.delete_success'));
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#tip button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var tip_id = $('#tip_id').val();

        if (tip_id) {
            var tip = Fixhub.Tips.get(tip_id);
        } else {
            var tip = new Fixhub.Tip();
        }

        tip.save({
            body:   $('#tip_body').val(),
            status: $('#tip_status').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('tips.edit_success');
                if (!tip_id) {
                    Fixhub.Tips.add(response);
                    msg = trans('tips.create_success');
                }
                Fixhub.toast(msg);
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input, form textarea', dialog).each(function (index, element) {
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


    Fixhub.Tip = Backbone.Model.extend({
        urlRoot: '/admin/tips'
    });

    var tips = Backbone.Collection.extend({
        model: Fixhub.Tip,
        comparator: function(tipA, tipB) {
            if (tipA.get('id') > tipB.get('id')) {
                return -1; // before
            } else if (tipA.get('id') < tipB.get('id')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    Fixhub.Tips = new tips();

    Fixhub.TipsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#tip_list tbody');

            $('#no_tips').show();
            $('#tip_list').hide();

            this.listenTo(Fixhub.Tips, 'add', this.addOne);
            this.listenTo(Fixhub.Tips, 'reset', this.addAll);
            this.listenTo(Fixhub.Tips, 'remove', this.addAll);
            this.listenTo(Fixhub.Tips, 'all', this.render);

            Fixhub.listener.on('tip:' + Fixhub.events.MODEL_CHANGED, function (data) {
                var tip = Fixhub.Tips.get(parseInt(data.model.id));

                if (tip) {
                    tip.set(data.model);
                }
            });

            Fixhub.listener.on('tip:' + Fixhub.events.MODEL_CREATED, function (data) {
                Fixhub.Tips.add(data.model);
            });

            Fixhub.listener.on('tip:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var tip = Fixhub.Tips.get(parseInt(data.model.id));

                if (tip) {
                    Fixhub.Tips.remove(tip);
                }
            });
        },
        render: function () {
            if (Fixhub.Tips.length) {
                $('#no_tips').hide();
                $('#tip_list').show();
            } else {
                $('#no_tips').show();
                $('#tip_list').hide();
            }
        },
        addOne: function (tip) {

            var view = new Fixhub.TipView({
                model: tip
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.Tips.each(this.addOne, this);
        }
    });

    Fixhub.TipView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-show': 'show',
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#tip-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#tip_id').val(this.model.id);
            $('#tip_body').val(this.model.get('body'));
            $('#tip_status').prop('checked', (this.model.get('status') === true));

        },
        show: function() {
            var data = this.model.toJSON();

            $('#tip_preview').html(data.body);
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade tip-trash');
        }
    });
})(jQuery);
(function ($) {

    $('#key_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('key-id'));
            });

            $.ajax({
                url: '/admin/keys/reorder',
                method: 'POST',
                data: {
                    keys: ids
                }
            });
        }
    });

    $('#key').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('keys.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('keys.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#key_id').val('');
            $('#key_name').val('');
            $('#key_private_key').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.key-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var key = Fixhub.Keys.get($('#model_id').val());

        key.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('keys.delete_success'));
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#key button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var key_id = $('#key_id').val();

        if (key_id) {
            var key = Fixhub.Keys.get(key_id);
        } else {
            var key = new Fixhub.Key();
        }

        key.save({
            name:         $('#key_name').val(),
            private_key:  $('#key_private_key').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('keys.edit_success');
                if (!key_id) {
                    Fixhub.Keys.add(response);
                    msg = trans('keys.create_success');
                }
                Fixhub.toast(msg);
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input, form textarea', dialog).each(function (index, element) {
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


    Fixhub.Key = Backbone.Model.extend({
        urlRoot: '/admin/keys'
    });

    var Keys = Backbone.Collection.extend({
        model: Fixhub.Key,
        comparator: function(keyA, keyB) {
            if (keyA.get('name') > keyB.get('name')) {
                return -1; // before
            } else if (keyA.get('name') < keyB.get('name')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    Fixhub.Keys = new Keys();

    Fixhub.KeysTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#key_list tbody');

            $('#no_keys').show();
            $('#key_list').hide();

            this.listenTo(Fixhub.Keys, 'add', this.addOne);
            this.listenTo(Fixhub.Keys, 'reset', this.addAll);
            this.listenTo(Fixhub.Keys, 'remove', this.addAll);
            this.listenTo(Fixhub.Keys, 'all', this.render);

            Fixhub.listener.on('key:' + Fixhub.events.MODEL_CHANGED, function (data) {
                var key = Fixhub.Keys.get(parseInt(data.model.id));

                if (key) {
                    key.set(data.model);
                }
            });

            Fixhub.listener.on('key:' + Fixhub.events.MODEL_CREATED, function (data) {
                Fixhub.Keys.add(data.model);
            });

            Fixhub.listener.on('key:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var key = Fixhub.Keys.get(parseInt(data.model.id));

                if (key) {
                    Fixhub.Keys.remove(key);
                }
            });
        },
        render: function () {
            if (Fixhub.Keys.length) {
                $('#no_keys').hide();
                $('#key_list').show();
            } else {
                $('#no_keys').show();
                $('#key_list').hide();
            }
        },
        addOne: function (key) {

            var view = new Fixhub.KeyView({
                model: key
            });

            this.$list.append(view.render().el);

            if (Fixhub.Keys.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.Keys.each(this.addOne, this);
        }
    });

    Fixhub.KeyView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-show': 'show',
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#key-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#key_id').val(this.model.id);
            $('#key_name').val(this.model.get('name'));
            $('#key_private_key').val(this.model.get('private_key'));

        },
        show: function() {
            var data = this.model.toJSON();

            $('#log pre').html(data.public_key);
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade key-trash');
        }
    });
})(jQuery);
(function ($) {

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
            //$('#user_level').val($("#user_level option:first").val());
            $('#user_level').select2(Fixhub.select2_options);
            $('#user_nickname').val('');
            $('#user_email').val('');
            $('#user_password').val('');
            $('#user_password_confirmation').val('');

            $('.new-only', modal).show();
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.user-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var user = Fixhub.Users.get($('#model_id').val());

        user.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('users.delete_success'));
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#user button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var user_id = $('#user_id').val();

        if (user_id) {
            var user = Fixhub.Users.get(user_id);
        } else {
            var user = new Fixhub.User();
        }

        user.save({
            name:                  $('#user_name').val(),
            level:                 $('#user_level').val(),
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

                var msg = trans('users.edit_success');
                if (!user_id) {
                    Fixhub.Users.add(response);
                    msg = trans('users.create_success');
                }
                Fixhub.toast(msg);
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

    Fixhub.User = Backbone.Model.extend({
        urlRoot: '/admin/users',
        initialize: function() {

        }
    });

    var Users = Backbone.Collection.extend({
        model: Fixhub.User
    });

    Fixhub.Users = new Users();

    Fixhub.UsersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#user_list tbody');

            this.listenTo(Fixhub.Users, 'add', this.addOne);
            this.listenTo(Fixhub.Users, 'reset', this.addAll);
            this.listenTo(Fixhub.Users, 'remove', this.addAll);
            this.listenTo(Fixhub.Users, 'all', this.render);

            Fixhub.listener.on('user:' + Fixhub.events.MODEL_CHANGED, function (data) {
                var user = Fixhub.Users.get(parseInt(data.model.id));

                if (user) {
                    user.set(data.model);
                }
            });

            Fixhub.listener.on('user:' + Fixhub.events.MODEL_CREATED, function (data) {
                Fixhub.Users.add(data.model);
            });

            Fixhub.listener.on('user:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var user = Fixhub.Users.get(parseInt(data.model.id));

                if (user) {
                    Fixhub.Users.remove(user);
                }
            });
        },
        addOne: function (user) {
            var view = new Fixhub.UserView({
                model: user
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.Users.each(this.addOne, this);
        }
    });

    Fixhub.UserView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
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
        edit: function() {
            $('#user_id').val(this.model.id);
            $('#user_name').val(this.model.get('name'));
            //$('#user_level').val(this.model.get('level'));
            $('#user_level').select2(Fixhub.select2_options)
                                .val(this.model.get('level'))
                                .trigger('change');
            $('#user_nickname').val(this.model.get('nickname'));
            $('#user_email').val(this.model.get('email'));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade user-trash');
        }
    });
})(jQuery);

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

            $('.command-environment').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.command-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var command = Fixhub.Commands.get($('#model_id').val());

        command.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('commands.delete_success'));
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

    //If no `off`, the code below will be executed twice.
    $('#command button.btn-save').off('click').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find(':input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var command_id = $('#command_id').val();

        if (command_id) {
            var command = Fixhub.Commands.get(command_id);
        } else {
            var command = new Fixhub.Command();
        }

        var environment_ids = [];

        $('.command-environment:checked').each(function() {
            environment_ids.push($(this).val());
        });

        command.save({
            name:            $('#command_name').val(),
            script:          editor.getValue(),
            user:            $('#command_user').val(),
            step:            $('#command_step').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val(),
            environments:    environment_ids,
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

                var msg = trans('commands.edit_success');
                if (!command_id) {
                    Fixhub.Commands.add(response);
                    msg = trans('commands.create_success');
                }

                editor.setValue('');
                editor.gotoLine(1);

                Fixhub.toast(msg);
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

    Fixhub.Command = Backbone.Model.extend({
        urlRoot: '/commands',
        defaults: function() {
            return {
                order: Fixhub.Commands.nextOrder()
            };
        },
        isAfter: function() {
            return (parseInt(this.get('step')) % 3 === 0);
        }
    });

    var Commands = Backbone.Collection.extend({
        model: Fixhub.Command,
        comparator: 'order',
        nextOrder: function() {
            if (!this.length) {
                return 1;
            }

            return this.last().get('order') + 1;
        }
    });

    Fixhub.Commands = new Commands();

    Fixhub.CommandsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$beforeList = $('#commands-before .command-list tbody');
            this.$afterList = $('#commands-after .command-list tbody');

            $('.no-commands').show();
            $('.command-list').hide();

            this.listenTo(Fixhub.Commands, 'add', this.addOne);
            this.listenTo(Fixhub.Commands, 'reset', this.addAll);
            this.listenTo(Fixhub.Commands, 'remove', this.addAll);
            this.listenTo(Fixhub.Commands, 'all', this.render);

            Fixhub.listener.on('command:' + Fixhub.events.MODEL_CHANGED, function (data) {
                var command = Fixhub.Commands.get(parseInt(data.model.id));

                if (command) {
                    command.set(data.model);
                }
            });

            Fixhub.listener.on('command:' + Fixhub.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                //if (data.model.targetable_type == Fixhub.targetable_type && parseInt(data.model.targetable_id) === parseInt(Fixhub.targetable_id)) {

                    // Make sure the command is for this action (clone, install, activate, purge)
                    if (parseInt(data.model.step) + 1 === parseInt(Fixhub.command_action) || parseInt(data.model.step) - 1 === parseInt(Fixhub.command_action)) {
                        Fixhub.Commands.add(data.model);
                    }
                }
            });

            Fixhub.listener.on('command:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var command = Fixhub.Commands.get(parseInt(data.model.id));

                if (command) {
                    Fixhub.Commands.remove(command);
                }
            });
        },
        render: function () {
            var before = Fixhub.Commands.find(function(model) {
                return !model.isAfter();
            });

            if (typeof before !== 'undefined') {
                $('#commands-before .no-commands').hide();
                $('#commands-before .command-list').show();
            } else {
                $('#commands-before .no-commands').show();
                $('#commands-before .command-list').hide();
            }

            var after = Fixhub.Commands.find(function(model) {
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
            var view = new Fixhub.CommandView({
                model: command
            });

            if (command.isAfter()) {
                this.$afterList.append(view.render().el);
                if ($('tr', this.$afterList).length < 2) {
                    $('.drag-handle', this.$afterList).hide();
                } else {
                    $('.drag-handle', this.$afterList).show();
                }
            } else {
                this.$beforeList.append(view.render().el);
                if ($('tr', this.$beforeList).length < 2) {
                    $('.drag-handle', this.$beforeList).hide();
                } else {
                    $('.drag-handle', this.$beforeList).show();
                }
            }
        },
        addAll: function () {
            this.$beforeList.html('');
            this.$afterList.html('');
            Fixhub.Commands.each(this.addOne, this);
        }
    });

    Fixhub.CommandView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
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
        edit: function() {
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

            $('.command-environment').prop('checked', false);
            $(this.model.get('environments')).each(function (index, environment) {
                $('#command_environment_' + environment.id).prop('checked', true);
            });
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade command-trash');
        }
    });
})(jQuery);

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

    $('#configfile').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('configFiles.create');

        editor = ace.edit('content');

        var filename = $('#path').val();
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
            $('#file_id').val('');
            $('#name').val('');
            $('#path').val('');
            $('.configfile-environment').prop('checked', true);
            editor.setValue('');
            editor.gotoLine(1);
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.configfile-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file = Fixhub.ConfigFiles.get($('#model_id').val());

        file.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('configFiles.delete_success'));
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#configfile button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var config_file_id = $('#config_file_id').val();

        if (config_file_id) {
            var file = Fixhub.ConfigFiles.get(config_file_id);
        } else {
            var file = new Fixhub.ConfigFile();
        }

        var environment_ids = [];

        $('.configfile-environment:checked').each(function() {
            environment_ids.push($(this).val());
        });

        file.save({
            name:            $('#name').val(),
            path:            $('#path').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val(),
            environments:    environment_ids,
            content:         editor.getValue()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('configFiles.edit_success');
                if (!config_file_id) {
                    Fixhub.ConfigFiles.add(response);
                    trans('configFiles.create_success');
                }

                editor.setValue('');
                editor.gotoLine(1);

                Fixhub.toast(msg);
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

    Fixhub.ConfigFile = Backbone.Model.extend({
        urlRoot: '/config-file'
    });

    var ConfigFiles = Backbone.Collection.extend({
        model: Fixhub.ConfigFile
    });

    Fixhub.ConfigFiles = new ConfigFiles();

    Fixhub.ConfigFilesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#configfile_list tbody');

            $('#no_configfiles').show();
            $('#configfile_list').hide();

            this.listenTo(Fixhub.ConfigFiles, 'add', this.addOne);
            this.listenTo(Fixhub.ConfigFiles, 'reset', this.addAll);
            this.listenTo(Fixhub.ConfigFiles, 'remove', this.addAll);
            this.listenTo(Fixhub.ConfigFiles, 'all', this.render);

            Fixhub.listener.on('configfile:' + Fixhub.events.MODEL_CHANGED, function (data) {
                var file = Fixhub.ConfigFiles.get(parseInt(data.model.id));

                if (file) {
                    file.set(data.model);
                }
            });

            Fixhub.listener.on('configfile:' + Fixhub.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    Fixhub.ConfigFiles.add(data.model);
                }
            });

            Fixhub.listener.on('configfile:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var file = Fixhub.ConfigFiles.get(parseInt(data.model.id));

                if (file) {
                    Fixhub.ConfigFiles.remove(file);
                }
            });
        },
        render: function () {
            if (Fixhub.ConfigFiles.length) {
                $('#no_configfiles').hide();
                $('#configfile_list').show();
            } else {
                $('#no_configfiles').show();
                $('#configfile_list').hide();
            }
        },
        addOne: function (file) {

            var view = new Fixhub.ConfigFileView({
                model: file
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.ConfigFiles.each(this.addOne, this);
        }
    });

    Fixhub.ConfigFileView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash',
            'click .btn-view': 'view'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#configfiles-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        view: function() {
            previewfile = this.model.get('path');
            $('#preview-content').text(this.model.get('content'));
        },
        edit: function() {
            $('#config_file_id').val(this.model.id);
            $('#name').val(this.model.get('name'));
            $('#path').val(this.model.get('path'));
            $('#content').text(this.model.get('content'));

            $('.configfile-environment').prop('checked', false);
            $(this.model.get('environments')).each(function (index, environment) {
                $('#configfile_environment_' + environment.id).prop('checked', true);
            });
        },
        trash: function () {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade configfile-trash');
        }
    });

})(jQuery);

(function ($) {

    //Fix me please
    var FINISHED     = 0;
    var PENDING      = 1;
    var DEPLOYING    = 2;
    var FAILED       = 3;
    var NOT_DEPLOYED = 4;

    $('#environment_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('environment-id'));
            });

            $.ajax({
                url: '/environments/reorder',
                method: 'POST',
                data: {
                    environments: ids
                }
            });
        }
    });

    $('#environment').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('environments.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();
        $('#add-environment-command', modal).hide();

        if (button.hasClass('btn-edit')) {
            title = trans('environments.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#environment_id').val('');
            $('#environment_name').val('');
            $('#environment_description').val('');
            $('#environment_default_on').prop('checked', true);
            $('#add-environment-command', modal).show();
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.environment-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var environment = Fixhub.Environments.get($('#model_id').val());

        environment.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('environments.delete_success'));
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#environment button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var environment_id = $('#environment_id').val();

        if (environment_id) {
            var environment = Fixhub.Environments.get(environment_id);
        } else {
            var environment = new Fixhub.Environment();
        }

        environment.save({
            name:            $('#environment_name').val(),
            description:     $('#environment_description').val(),
            default_on:      $('#environment_default_on').is(':checked'),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val(),
            add_commands:    $('#environment_commands').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('environments.edit_success');
                if (!environment_id) {
                    Fixhub.Environments.add(response);
                    msg = trans('environments.create_success');
                }
                Fixhub.toast(msg);
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

    Fixhub.Environment = Backbone.Model.extend({
        urlRoot: '/environments',
        initialize: function() {

        }
    });

    var Environments = Backbone.Collection.extend({
        model: Fixhub.Environment
    });

    Fixhub.Environments = new Environments();

    Fixhub.EnvironmentsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#environment_list tbody');

            $('#no_environments').show();
            $('#environment_list').hide();

            this.listenTo(Fixhub.Environments, 'add', this.addOne);
            this.listenTo(Fixhub.Environments, 'reset', this.addAll);
            this.listenTo(Fixhub.Environments, 'remove', this.addAll);
            this.listenTo(Fixhub.Environments, 'all', this.render);

            Fixhub.listener.on('environment:' + Fixhub.events.MODEL_CHANGED, function (data) {
                $('#environment_' + data.model.id).html(data.model.name);

                var environment = Fixhub.Environments.get(parseInt(data.model.id));

                if (environment) {
                    environment.set(data.model);
                }
            });

            Fixhub.listener.on('environment:' + Fixhub.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    Fixhub.Environments.add(data.model);
                }
            });

            Fixhub.listener.on('environment:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var environment = Fixhub.Environments.get(parseInt(data.model.id));

                if (environment) {
                    Fixhub.Environments.remove(environment);
                }
            });
        },
        render: function () {
            if (Fixhub.Environments.length) {
                $('#no_environments').hide();
                $('#environment_list').show();
            } else {
                $('#no_environments').show();
                $('#environment_list').hide();
            }
        },
        addOne: function (environment) {

            var view = new Fixhub.EnvironmentView({
                model: environment
            });

            this.$list.append(view.render().el);

            if (Fixhub.Environments.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.Environments.each(this.addOne, this);
        }
    });

    Fixhub.EnvironmentView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#environment-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            var parse_data = Fixhub.formatProjectStatus(parseInt(this.model.get('status')));
            data = $.extend(data, parse_data);

            data.last_run = data.last_run != null ? moment(data.last_run).fromNow() : trans('app.never');

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#environment_id').val(this.model.id);
            $('#environment_name').val(this.model.get('name'));
            $('#environment_description').val(this.model.get('description'));
            $('#environment_default_on').prop('checked', (this.model.get('default_on') === true));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade environment-trash');
        }
    });
})(jQuery);

(function ($) {

    $('#sharedfile').on('show.bs.modal', function (event) {
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
            $('#sharedfile_id').val('');
            $('#name').val('');
            $('#file').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.sharedfile-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file = Fixhub.SharedFiles.get($('#model_id').val());

        file.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('sharedFiles.delete_success'));
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#sharedfile button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file_id = $('#sharedfile_id').val();

        if (file_id) {
            var file = Fixhub.SharedFiles.get(file_id);
        } else {
            var file = new Fixhub.SharedFile();
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

                var msg = trans('sharedFiles.edit_success');
                if (!file_id) {
                    Fixhub.SharedFiles.add(response);
                    trans('sharedFiles.create_success');
                }

                Fixhub.toast(msg);
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

    Fixhub.SharedFile = Backbone.Model.extend({
        urlRoot: '/shared-files'
    });

    var SharedFiles = Backbone.Collection.extend({
        model: Fixhub.SharedFile
    });

    Fixhub.SharedFiles = new SharedFiles();

    Fixhub.SharedFilesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#sharedfile_list tbody');

            $('#no_sharedfiles').show();
            $('#sharedfile_list').hide();

            this.listenTo(Fixhub.SharedFiles, 'add', this.addOne);
            this.listenTo(Fixhub.SharedFiles, 'reset', this.addAll);
            this.listenTo(Fixhub.SharedFiles, 'remove', this.addAll);
            this.listenTo(Fixhub.SharedFiles, 'all', this.render);

            Fixhub.listener.on('sharedfile:' + Fixhub.events.MODEL_CHANGED, function (data) {
                var share = Fixhub.SharedFiles.get(parseInt(data.model.id));

                if (share) {
                    share.set(data.model);
                }
            });

            Fixhub.listener.on('sharedfile:' + Fixhub.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    Fixhub.SharedFiles.add(data.model);
                }
            });

            Fixhub.listener.on('sharedfile:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var share = Fixhub.SharedFiles.get(parseInt(data.model.id));

                if (share) {
                    Fixhub.SharedFiles.remove(share);
                }
            });
        },
        render: function () {
            if (Fixhub.SharedFiles.length) {
                $('#no_sharedfiles').hide();
                $('#sharedfile_list').show();
            } else {
                $('#no_sharedfiles').show();
                $('#sharedfile_list').hide();
            }
        },
        addOne: function (file) {

            var view = new Fixhub.SharedFileView({
                model: file
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.SharedFiles.each(this.addOne, this);
        }
    });

    Fixhub.SharedFileView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#sharedfile-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#sharedfile_id').val(this.model.id);
            $('#name').val(this.model.get('name'));
            $('#file').val(this.model.get('file'));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade sharedfile-trash');
        }
    });

})(jQuery);

(function ($) {

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

    $('body').delegate('.variable-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var variable = Fixhub.Variables.get($('#model_id').val());

        variable.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('variables.delete_success'));
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#variable button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var variable_id = $('#variable_id').val();

        if (variable_id) {
            var variable = Fixhub.Variables.get(variable_id);
        } else {
            var variable = new Fixhub.Variable();
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

                var msg = trans('variables.edit_success');
                if (!variable_id) {
                    Fixhub.Variables.add(response);
                    msg = trans('variables.create_success');
                }
                Fixhub.toast(msg);
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

    Fixhub.Variable = Backbone.Model.extend({
        urlRoot: '/variables',
        initialize: function() {

        }
    });

    var Variables = Backbone.Collection.extend({
        model: Fixhub.Variable
    });

    Fixhub.Variables = new Variables();

    Fixhub.VariablesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#variable_list tbody');

            $('#no_variables').show();
            $('#variable_list').hide();

            this.listenTo(Fixhub.Variables, 'add', this.addOne);
            this.listenTo(Fixhub.Variables, 'reset', this.addAll);
            this.listenTo(Fixhub.Variables, 'remove', this.addAll);
            this.listenTo(Fixhub.Variables, 'all', this.render);

            Fixhub.listener.on('variable:' + Fixhub.events.MODEL_CHANGED, function (data) {
                $('#variable_' + data.model.id).html(data.model.name);

                var variable = Fixhub.Variables.get(parseInt(data.model.id));

                if (variable) {
                    variable.set(data.model);
                }
            });

            Fixhub.listener.on('variable:' + Fixhub.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    Fixhub.Variables.add(data.model);
                }
            });

            Fixhub.listener.on('variable:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var variable = Fixhub.Variables.get(parseInt(data.model.id));

                if (variable) {
                    Fixhub.Variables.remove(variable);
                }
            });
        },
        render: function () {
            if (Fixhub.Variables.length) {
                $('#no_variables').hide();
                $('#variable_list').show();
            } else {
                $('#no_variables').show();
                $('#variable_list').hide();
            }
        },
        addOne: function (variable) {

            var view = new Fixhub.VariableView({
                model: variable
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.Variables.each(this.addOne, this);
        }
    });

    Fixhub.VariableView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
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
        edit: function() {
            $('#variable_id').val(this.model.id);
            $('#variable_name').val(this.model.get('name'));
            $('#variable_value').val(this.model.get('value'));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade variable-trash');
        }
    });
})(jQuery);
