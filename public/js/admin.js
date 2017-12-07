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

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var group = Piplin.Groups.get($('#model_id').val());

        group.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('groups.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#group button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var group_id = $('#group_id').val();

        if (group_id) {
            var group = Piplin.Groups.get(group_id);
        } else {
            var group = new Piplin.Group();
        }

        group.save({
            name: $('#group_name').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('groups.edit_success');
                if (!group_id) {
                    Piplin.Groups.add(response);
                    msg = trans('groups.create_success');
                }
                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    Piplin.Group = Backbone.Model.extend({
        urlRoot: '/admin/groups',
        initialize: function() {

        }
    });

    var Groups = Backbone.Collection.extend({
        model: Piplin.Group
    });

    Piplin.Groups = new Groups();

    Piplin.GroupsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#group_list tbody');

            $('#group_list').hide();
            $('#no_groups').show();

            this.listenTo(Piplin.Groups, 'add', this.addOne);
            this.listenTo(Piplin.Groups, 'reset', this.addAll);
            this.listenTo(Piplin.Groups, 'remove', this.addAll);
            this.listenTo(Piplin.Groups, 'all', this.render);

            Piplin.listener.on('projectgroup:' + Piplin.events.MODEL_CHANGED, function (data) {
                $('#group_' + data.model.id).html(data.model.name);

                var group = Piplin.Groups.get(parseInt(data.model.id));

                if (group) {
                    group.set(data.model);
                }
            });

            Piplin.listener.on('projectgroup:' + Piplin.events.MODEL_CREATED, function (data) {
                Piplin.Groups.add(data.model);
            });

            Piplin.listener.on('projectgroup:' + Piplin.events.MODEL_TRASHED, function (data) {
                var group = Piplin.Groups.get(parseInt(data.model.id));

                if (group) {
                    Piplin.Groups.remove(group);
                }

                $('#group_' + data.model.id).parent('li').remove();

                if (parseInt(data.model.id) === parseInt(Piplin.group_id)) {
                    window.location.href = '/';
                }
            });
        },
        render: function () {
            if (Piplin.Groups.length) {
                $('#no_groups').hide();
                $('#group_list').show();
            } else {
                $('#no_groups').show();
                $('#group_list').hide();
            }
        },
        addOne: function (group) {

            var view = new Piplin.GroupView({
                model: group
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Groups.each(this.addOne, this);
        }
    });

    Piplin.GroupView = Backbone.View.extend({
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

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var provider = Piplin.Providers.get($('#model_id').val());

        provider.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('providers.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#provider button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var provider_id = $('#provider_id').val();

        if (provider_id) {
            var provider = Piplin.Providers.get(provider_id);
        } else {
            var provider = new Piplin.Provider();
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('providers.edit_success');
                if (!provider_id) {
                    Piplin.Providers.add(response);
                    msg = trans('providers.create_success');
                }
                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });


    Piplin.Provider = Backbone.Model.extend({
        urlRoot: '/admin/providers'
    });

    var Providers = Backbone.Collection.extend({
        model: Piplin.Provider,
        comparator: function(providerA, providerB) {
            if (providerA.get('name') > providerB.get('name')) {
                return -1; // before
            } else if (providerA.get('name') < providerB.get('name')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    Piplin.Providers = new Providers();

    Piplin.ProvidersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#provider_list tbody');

            $('#no_providers').show();
            $('#provider_list').hide();

            this.listenTo(Piplin.Providers, 'add', this.addOne);
            this.listenTo(Piplin.Providers, 'reset', this.addAll);
            this.listenTo(Piplin.Providers, 'remove', this.addAll);
            this.listenTo(Piplin.Providers, 'all', this.render);

            Piplin.listener.on('provider:' + Piplin.events.MODEL_CHANGED, function (data) {
                var provider = Piplin.providers.get(parseInt(data.model.id));

                if (provider) {
                    provider.set(data.model);
                }
            });

            Piplin.listener.on('provider:' + Piplin.events.MODEL_CREATED, function (data) {
                    Piplin.Providers.add(data.model);
            });

            Piplin.listener.on('provider:' + Piplin.events.MODEL_TRASHED, function (data) {
                var provider = Piplin.Providers.get(parseInt(data.model.id));

                if (provider) {
                    Piplin.Providers.remove(provider);
                }
            });
        },
        render: function () {
            if (Piplin.Providers.length) {
                $('#no_providers').hide();
                $('#provider_list').show();
            } else {
                $('#no_providers').show();
                $('#provider_list').hide();
            }
        },
        addOne: function (provider) {

            var view = new Piplin.ProviderView({
                model: provider
            });

            this.$list.append(view.render().el);

            if (Piplin.Providers.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Providers.each(this.addOne, this);
        }
    });

    Piplin.ProviderView = Backbone.View.extend({
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

        $('.nav-tabs a:first', modal).tab('show');

        if (button.hasClass('btn-edit')) {
            title = trans('projects.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#project_id').val('');
            $('#project_name').val('');
            $('#project_repository').val('');
            $('#project_branch').val('master');
            $('#project_targetable_id').select2(Piplin.select2_options)
                                    .val($("#project_targetable_id option:selected").val())
                                    .trigger('change');
            $('#project_key_id').val($("#project_key_id option:first").val());
            $('#project_deploy_path').val('');
            $('#project_builds_to_keep').val(10);
            $('#project_url').val('');
            $('#project_build_url').val('');
            $('#project_allow_other_branch').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.project-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var project = Piplin.Projects.get($('#model_id').val());

        project.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('projects.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#project button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var project_id = $('#project_id').val();

        if (project_id) {
            var project = Piplin.Projects.get(project_id);
        } else {
            var project = new Piplin.Project();
        }

        project.save({
            name:               $('#project_name').val(),
            repository:         $('#project_repository').val(),
            branch:             $('#project_branch').val(), 
            targetable_id:      $('#project_targetable_id').val(),
            key_id:             $('#project_key_id').val(),
            deploy_path:        $('#project_deploy_path').val(),
            builds_to_keep:     $('#project_builds_to_keep').val(),
            url:                $('#project_url').val(),
            build_url:          $('#project_build_url').val(),
            allow_other_branch: $('#project_allow_other_branch').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('projects.edit_success');
                if (!project_id) {
                    Piplin.Projects.add(response);
                    msg = trans('projects.create_success');
                }
                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    Piplin.Project = Backbone.Model.extend({
        urlRoot: '/admin/projects'
    });

    var Projects = Backbone.Collection.extend({
        model: Piplin.Project
    });

    Piplin.Projects = new Projects();

    Piplin.ProjectsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#project_list tbody');

            $('#project_list').hide();
            $('#no_projects').show();

            this.listenTo(Piplin.Projects, 'add', this.addOne);
            this.listenTo(Piplin.Projects, 'reset', this.addAll);
            this.listenTo(Piplin.Projects, 'remove', this.addAll);
            this.listenTo(Piplin.Projects, 'all', this.render);

            Piplin.listener.on('project:' + Piplin.events.MODEL_CHANGED, function (data) {
                var project = Piplin.Projects.get(parseInt(data.model.id));

                if (project) {
                    if(Piplin.group_id == undefined || Piplin.group_id == data.model.targetable_id) {
                        project.set(data.model);
                    } else {
                        Piplin.Projects.remove(project);
                    }
                }
            });

            Piplin.listener.on('project:' + Piplin.events.MODEL_CREATED, function (data) {
                Piplin.Projects.add(data.model);
            });

            Piplin.listener.on('project:' + Piplin.events.MODEL_TRASHED, function (data) {
                var project = Piplin.Projects.get(parseInt(data.model.id));

                if (project) {
                    Piplin.Projects.remove(project);
                }

                $('#project_' + data.model.id).parent('li').remove();

                if (parseInt(data.model.id) === parseInt(Piplin.project_id)) {
                    window.location.href = '/';
                }
            });
        },
        render: function () {
            if (Piplin.Projects.length) {
                $('#no_projects').hide();
                $('#project_list').show();
            } else {
                $('#no_projects').show();
                $('#project_list').hide();
            }
        },
        addOne: function (project) {
            var view = new Piplin.ProjectView({
                model: project
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Projects.each(this.addOne, this);
        }
    });

    Piplin.ProjectView = Backbone.View.extend({
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
            $('#project_targetable_id').select2(Piplin.select2_options)
                                    .val(this.model.get('targetable_id'))
                                    .trigger('change');
            $('#project_key_id').select2(Piplin.select2_options)
                                    .val(this.model.get('key_id'))
                                    .trigger('change');
            $('#project_deploy_path').val(this.model.get('deploy_path'));
            $('#project_builds_to_keep').val(this.model.get('builds_to_keep'));
            $('#project_url').val(this.model.get('url'));
            $('#project_build_url').val(this.model.get('build_url'));
            $('#project_allow_other_branch').prop('checked', (this.model.get('allow_other_branch') === true));
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

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var key = Piplin.Keys.get($('#model_id').val());

        key.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('keys.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#key button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var key_id = $('#key_id').val();

        if (key_id) {
            var key = Piplin.Keys.get(key_id);
        } else {
            var key = new Piplin.Key();
        }

        key.save({
            name:         $('#key_name').val(),
            private_key:  $('#key_private_key').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('keys.edit_success');
                if (!key_id) {
                    Piplin.Keys.add(response);
                    msg = trans('keys.create_success');
                }
                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });


    Piplin.Key = Backbone.Model.extend({
        urlRoot: '/admin/keys'
    });

    var Keys = Backbone.Collection.extend({
        model: Piplin.Key,
        comparator: function(keyA, keyB) {
            if (keyA.get('name') > keyB.get('name')) {
                return -1; // before
            } else if (keyA.get('name') < keyB.get('name')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    Piplin.Keys = new Keys();

    Piplin.KeysTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#key_list tbody');

            $('#no_keys').show();
            $('#key_list').hide();

            this.listenTo(Piplin.Keys, 'add', this.addOne);
            this.listenTo(Piplin.Keys, 'reset', this.addAll);
            this.listenTo(Piplin.Keys, 'remove', this.addAll);
            this.listenTo(Piplin.Keys, 'all', this.render);

            Piplin.listener.on('key:' + Piplin.events.MODEL_CHANGED, function (data) {
                var key = Piplin.Keys.get(parseInt(data.model.id));

                if (key) {
                    key.set(data.model);
                }
            });

            Piplin.listener.on('key:' + Piplin.events.MODEL_CREATED, function (data) {
                Piplin.Keys.add(data.model);
            });

            Piplin.listener.on('key:' + Piplin.events.MODEL_TRASHED, function (data) {
                var key = Piplin.Keys.get(parseInt(data.model.id));

                if (key) {
                    Piplin.Keys.remove(key);
                }
            });
        },
        render: function () {
            if (Piplin.Keys.length) {
                $('#no_keys').hide();
                $('#key_list').show();
            } else {
                $('#no_keys').show();
                $('#key_list').hide();
            }
        },
        addOne: function (key) {

            var view = new Piplin.KeyView({
                model: key
            });

            this.$list.append(view.render().el);

            if (Piplin.Keys.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Keys.each(this.addOne, this);
        }
    });

    Piplin.KeyView = Backbone.View.extend({
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

            $('#key_log pre').html(data.public_key);
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade key-trash');
        }
    });
})(jQuery);
(function ($) {

    $('#cabinet_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('cabinet-id'));
            });

            $.ajax({
                url: '/admin/cabinets/reorder',
                method: 'POST',
                data: {
                    cabinets: ids
                }
            });
        }
    });

    $('#cabinet').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('cabinets.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('cabinets.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#cabinet_id').val('');
            $('#cabinet_name').val('');
            $('#cabinet_description').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.cabinet-trash button.btn-delete','click', function (event) {

        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var cabinet = Piplin.Cabinets.get($('#model_id').val());

        cabinet.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('cabinets.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#cabinet button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var cabinet_id = $('#cabinet_id').val();

        if (cabinet_id) {
            var cabinet = Piplin.Cabinets.get(cabinet_id);
        } else {
            var cabinet = new Piplin.Cabinet();
        }

        cabinet.save({
            name:        $('#cabinet_name').val(),
            description: $('#cabinet_description').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('cabinets.edit_success');
                if (!cabinet_id) {
                    Piplin.Cabinets.add(response);
                    msg = trans('cabinets.create_success');
                }
                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    Piplin.Cabinet = Backbone.Model.extend({
        urlRoot: '/admin/cabinets',
        initialize: function() {

        }
    });

    var Cabinets = Backbone.Collection.extend({
        model: Piplin.Cabinet
    });

    Piplin.Cabinets = new Cabinets();

    Piplin.CabinetsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#cabinet_list tbody');

            $('#cabinet_list').hide();
            $('#no_cabinets').show();

            this.listenTo(Piplin.Cabinets, 'add', this.addOne);
            this.listenTo(Piplin.Cabinets, 'reset', this.addAll);
            this.listenTo(Piplin.Cabinets, 'remove', this.addAll);
            this.listenTo(Piplin.Cabinets, 'all', this.render);

            Piplin.listener.on('cabinet:' + Piplin.events.MODEL_CHANGED, function (data) {
                $('#cabinet_' + data.model.id).html(data.model.name);

                var cabinet = Piplin.Cabinets.get(parseInt(data.model.id));

                if (cabinet) {
                    cabinet.set(data.model);
                }
            });

            Piplin.listener.on('cabinet:' + Piplin.events.MODEL_CREATED, function (data) {
                Piplin.Cabinets.add(data.model);
            });

            Piplin.listener.on('cabinet:' + Piplin.events.MODEL_TRASHED, function (data) {
                var cabinet = Piplin.Cabinets.get(parseInt(data.model.id));

                if (cabinet) {
                    Piplin.Cabinets.remove(cabinet);
                }

                $('#cabinet_' + data.model.id).parent('li').remove();

                if (parseInt(data.model.id) === parseInt(Piplin.cabinet_id)) {
                    window.location.href = '/';
                }
            });
        },
        render: function () {
            if (Piplin.Cabinets.length) {
                $('#no_cabinets').hide();
                $('#cabinet_list').show();
            } else {
                $('#no_cabinets').show();
                $('#cabinet_list').hide();
            }
        },
        addOne: function (cabinet) {

            var view = new Piplin.CabinetView({
                model: cabinet
            });

            this.$list.append(view.render().el);

            $('.server-names', this.$list).tooltip();
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Cabinets.each(this.addOne, this);
        }
    });

    Piplin.CabinetView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#cabinet-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#cabinet_id').val(this.model.id);
            $('#cabinet_name').val(this.model.get('name'));
            $('#cabinet_description').val(this.model.get('description'));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade cabinet-trash');
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
            $('#user_level').select2(Piplin.select2_options);
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

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var user = Piplin.Users.get($('#model_id').val());

        user.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('users.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#user button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var user_id = $('#user_id').val();

        if (user_id) {
            var user = Piplin.Users.get(user_id);
        } else {
            var user = new Piplin.User();
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('users.edit_success');
                if (!user_id) {
                    Piplin.Users.add(response);
                    msg = trans('users.create_success');
                }
                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    Piplin.User = Backbone.Model.extend({
        urlRoot: '/admin/users',
        initialize: function() {

        }
    });

    var Users = Backbone.Collection.extend({
        model: Piplin.User
    });

    Piplin.Users = new Users();

    Piplin.UsersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#user_list tbody');

            this.listenTo(Piplin.Users, 'add', this.addOne);
            this.listenTo(Piplin.Users, 'reset', this.addAll);
            this.listenTo(Piplin.Users, 'remove', this.addAll);
            this.listenTo(Piplin.Users, 'all', this.render);

            Piplin.listener.on('user:' + Piplin.events.MODEL_CHANGED, function (data) {
                var user = Piplin.Users.get(parseInt(data.model.id));

                if (user) {
                    user.set(data.model);
                }
            });

            Piplin.listener.on('user:' + Piplin.events.MODEL_CREATED, function (data) {
                Piplin.Users.add(data.model);
            });

            Piplin.listener.on('user:' + Piplin.events.MODEL_TRASHED, function (data) {
                var user = Piplin.Users.get(parseInt(data.model.id));

                if (user) {
                    Piplin.Users.remove(user);
                }
            });
        },
        addOne: function (user) {
            var view = new Piplin.UserView({
                model: user
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Users.each(this.addOne, this);
        }
    });

    Piplin.UserView = Backbone.View.extend({
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
            $('#user_level').select2(Piplin.select2_options)
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
            $('.command-pattern').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.command-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var command = Piplin.Commands.get($('#model_id').val());

        command.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('commands.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
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

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find(':input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var command_id = $('#command_id').val();
        if (command_id) {
            var command = Piplin.Commands.get(command_id);
        } else {
            var command = new Piplin.Command();
        }

        var environment_ids = [];
        $('.command-environment:checked').each(function() {
            environment_ids.push($(this).val());
        });

        var pattern_ids = [];
        $('.command-pattern:checked').each(function() {
            pattern_ids.push($(this).val());
        });

        command.save({
            name:            $('#command_name').val(),
            script:          editor.getValue(),
            user:            $('#command_user').val(),
            step:            $('#command_step').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val(),
            environments:    environment_ids,
            patterns:        pattern_ids,
            optional:        $('#command_optional').is(':checked'),
            default_on:      $('#command_default_on').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find(':input').removeAttr('disabled');

                var msg = trans('commands.edit_success');
                if (!command_id) {
                    Piplin.Commands.add(response);
                    msg = trans('commands.create_success');
                }

                editor.setValue('');
                editor.gotoLine(1);

                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find(':input').removeAttr('disabled');
            }
        });
    });

    Piplin.Command = Backbone.Model.extend({
        urlRoot: '/commands',
        defaults: function() {
            return {
                order: Piplin.Commands.nextOrder()
            };
        },
        isAfter: function() {
            return (parseInt(this.get('step')) % 3 === 0);
        }
    });

    var Commands = Backbone.Collection.extend({
        model: Piplin.Command,
        comparator: 'order',
        nextOrder: function() {
            if (!this.length) {
                return 1;
            }

            return this.last().get('order') + 1;
        }
    });

    Piplin.Commands = new Commands();

    Piplin.CommandsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$beforeList = $('#commands-before .command-list tbody');
            this.$afterList = $('#commands-after .command-list tbody');

            $('.no-commands').show();
            $('.command-list').hide();

            this.listenTo(Piplin.Commands, 'add', this.addOne);
            this.listenTo(Piplin.Commands, 'reset', this.addAll);
            this.listenTo(Piplin.Commands, 'remove', this.addAll);
            this.listenTo(Piplin.Commands, 'all', this.render);

            Piplin.listener.on('command:' + Piplin.events.MODEL_CHANGED, function (data) {
                var command = Piplin.Commands.get(parseInt(data.model.id));

                if (command) {
                    command.set(data.model);
                }
            });

            Piplin.listener.on('command:' + Piplin.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                //if (data.model.targetable_type == Piplin.targetable_type && parseInt(data.model.targetable_id) === parseInt(Piplin.targetable_id)) {

                    // Make sure the command is for this action (clone, install, activate, purge)
                    if (parseInt(data.model.step) + 1 === parseInt(Piplin.command_action) || parseInt(data.model.step) - 1 === parseInt(Piplin.command_action)) {
                        Piplin.Commands.add(data.model);
                    }
                }
            });

            Piplin.listener.on('command:' + Piplin.events.MODEL_TRASHED, function (data) {
                var command = Piplin.Commands.get(parseInt(data.model.id));

                if (command) {
                    Piplin.Commands.remove(command);
                }
            });
        },
        render: function () {
            var before = Piplin.Commands.find(function(model) {
                return !model.isAfter();
            });

            if (typeof before !== 'undefined') {
                $('#commands-before .no-commands').hide();
                $('#commands-before .command-list').show();
            } else {
                $('#commands-before .no-commands').show();
                $('#commands-before .command-list').hide();
            }

            var after = Piplin.Commands.find(function(model) {
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
            var view = new Piplin.CommandView({
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
            Piplin.Commands.each(this.addOne, this);
        }
    });

    Piplin.CommandView = Backbone.View.extend({
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

            $('.command-pattern').prop('checked', false);
            $(this.model.get('patterns')).each(function (index, pattern) {
                $('#command_pattern_' + pattern.id).prop('checked', true);
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

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file = Piplin.ConfigFiles.get($('#model_id').val());

        file.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('configFiles.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#configfile button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var config_file_id = $('#config_file_id').val();

        if (config_file_id) {
            var file = Piplin.ConfigFiles.get(config_file_id);
        } else {
            var file = new Piplin.ConfigFile();
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('configFiles.edit_success');
                if (!config_file_id) {
                    Piplin.ConfigFiles.add(response);
                    trans('configFiles.create_success');
                }

                editor.setValue('');
                editor.gotoLine(1);

                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#sync-configfile button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var config_file_id = $('#sync-configfile_id').val();

        if (config_file_id) {
            var file = Piplin.ConfigFiles.get(config_file_id);
        } else {
            var file = new Piplin.ConfigFile();
        }

        var environment_ids = [];

        $('.sync-environment:checked').each(function() {
            environment_ids.push($(this).val());
        });
        var post_commands = $('#sync-post_commands').val();

        console.log(post_commands);

    });

    Piplin.ConfigFile = Backbone.Model.extend({
        urlRoot: '/config-files'
    });

    var ConfigFiles = Backbone.Collection.extend({
        model: Piplin.ConfigFile
    });

    Piplin.ConfigFiles = new ConfigFiles();

    Piplin.ConfigFilesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#configfile_list tbody');

            $('#no_configfiles').show();
            $('#configfile_list').hide();

            this.listenTo(Piplin.ConfigFiles, 'add', this.addOne);
            this.listenTo(Piplin.ConfigFiles, 'reset', this.addAll);
            this.listenTo(Piplin.ConfigFiles, 'remove', this.addAll);
            this.listenTo(Piplin.ConfigFiles, 'all', this.render);

            Piplin.listener.on('configfile:' + Piplin.events.MODEL_CHANGED, function (data) {
                var file = Piplin.ConfigFiles.get(parseInt(data.model.id));

                if (file) {
                    file.set(data.model);
                }
            });

            Piplin.listener.on('configfile:' + Piplin.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    Piplin.ConfigFiles.add(data.model);
                }
            });

            Piplin.listener.on('configfile:' + Piplin.events.MODEL_TRASHED, function (data) {
                var file = Piplin.ConfigFiles.get(parseInt(data.model.id));

                if (file) {
                    Piplin.ConfigFiles.remove(file);
                }
            });
        },
        render: function () {
            if (Piplin.ConfigFiles.length) {
                $('#no_configfiles').hide();
                $('#configfile_list').show();
            } else {
                $('#no_configfiles').show();
                $('#configfile_list').hide();
            }
        },
        addOne: function (file) {

            var view = new Piplin.ConfigFileView({
                model: file
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.ConfigFiles.each(this.addOne, this);
        }
    });

    Piplin.ConfigFileView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash',
            'click .btn-view': 'view',
            'click .btn-sync': 'sync'
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
        sync: function() {
            console.log('yes');
            console.log(this.model.id);
            $('.sync-environment').prop('checked', false).prop('disabled', true).parent().attr('class', 'text-gray');
            $(this.model.get('environments')).each(function (index, environment) {
                $('#sync_environment_' + environment.id).prop('checked', true).prop('disabled', false).parent().removeClass('text-gray');
            });
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
    var FINISHED = 0;
    var PENDING  = 1;
    var RUNNING  = 2;
    var FAILED   = 3;
    var INITIAL  = 4;

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

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var environment = Piplin.Environments.get($('#model_id').val());

        environment.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('environments.delete_success'));
            },
            error: function() {
               icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#environment button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var environment_id = $('#environment_id').val();

        if (environment_id) {
            var environment = Piplin.Environments.get(environment_id);
        } else {
            var environment = new Piplin.Environment();
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('environments.edit_success');
                if (!environment_id) {
                    Piplin.Environments.add(response);
                    msg = trans('environments.create_success');
                }
                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    Piplin.Environment = Backbone.Model.extend({
        urlRoot: '/environments',
        initialize: function() {

        }
    });

    var Environments = Backbone.Collection.extend({
        model: Piplin.Environment
    });

    Piplin.Environments = new Environments();

    Piplin.EnvironmentsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#environment_list tbody');

            $('#no_environments').show();
            $('#environment_list').hide();

            this.listenTo(Piplin.Environments, 'add', this.addOne);
            this.listenTo(Piplin.Environments, 'reset', this.addAll);
            this.listenTo(Piplin.Environments, 'remove', this.addAll);
            this.listenTo(Piplin.Environments, 'all', this.render);

            Piplin.listener.on('environment:' + Piplin.events.MODEL_CHANGED, function (data) {
                $('#environment_' + data.model.id).html(data.model.name);

                var environment = Piplin.Environments.get(parseInt(data.model.id));

                if (environment) {
                    environment.set(data.model);
                }
            });

            Piplin.listener.on('environment:' + Piplin.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    Piplin.Environments.add(data.model);
                }
            });

            Piplin.listener.on('environment:' + Piplin.events.MODEL_TRASHED, function (data) {
                var environment = Piplin.Environments.get(parseInt(data.model.id));

                if (environment) {
                    Piplin.Environments.remove(environment);
                }
            });
        },
        render: function () {
            if (Piplin.Environments.length) {
                $('#no_environments').hide();
                $('#environment_list').show();
            } else {
                $('#no_environments').show();
                $('#environment_list').hide();
            }
        },
        addOne: function (environment) {

            var view = new Piplin.EnvironmentView({
                model: environment
            });

            this.$list.append(view.render().el);

            $('.server-names', this.$list).tooltip();
            
            if (Piplin.Environments.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Environments.each(this.addOne, this);
        }
    });

    Piplin.EnvironmentView = Backbone.View.extend({
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

            var parse_data = Piplin.formatProjectStatus(parseInt(this.model.get('status')));
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

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file = Piplin.SharedFiles.get($('#model_id').val());

        file.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('sharedFiles.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#sharedfile button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file_id = $('#sharedfile_id').val();

        if (file_id) {
            var file = Piplin.SharedFiles.get(file_id);
        } else {
            var file = new Piplin.SharedFile();
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('sharedFiles.edit_success');
                if (!file_id) {
                    Piplin.SharedFiles.add(response);
                    trans('sharedFiles.create_success');
                }

                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    Piplin.SharedFile = Backbone.Model.extend({
        urlRoot: '/shared-files'
    });

    var SharedFiles = Backbone.Collection.extend({
        model: Piplin.SharedFile
    });

    Piplin.SharedFiles = new SharedFiles();

    Piplin.SharedFilesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#sharedfile_list tbody');

            $('#no_sharedfiles').show();
            $('#sharedfile_list').hide();

            this.listenTo(Piplin.SharedFiles, 'add', this.addOne);
            this.listenTo(Piplin.SharedFiles, 'reset', this.addAll);
            this.listenTo(Piplin.SharedFiles, 'remove', this.addAll);
            this.listenTo(Piplin.SharedFiles, 'all', this.render);

            Piplin.listener.on('sharedfile:' + Piplin.events.MODEL_CHANGED, function (data) {
                var share = Piplin.SharedFiles.get(parseInt(data.model.id));

                if (share) {
                    share.set(data.model);
                }
            });

            Piplin.listener.on('sharedfile:' + Piplin.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    Piplin.SharedFiles.add(data.model);
                }
            });

            Piplin.listener.on('sharedfile:' + Piplin.events.MODEL_TRASHED, function (data) {
                var share = Piplin.SharedFiles.get(parseInt(data.model.id));

                if (share) {
                    Piplin.SharedFiles.remove(share);
                }
            });
        },
        render: function () {
            if (Piplin.SharedFiles.length) {
                $('#no_sharedfiles').hide();
                $('#sharedfile_list').show();
            } else {
                $('#no_sharedfiles').show();
                $('#sharedfile_list').hide();
            }
        },
        addOne: function (file) {

            var view = new Piplin.SharedFileView({
                model: file
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.SharedFiles.each(this.addOne, this);
        }
    });

    Piplin.SharedFileView = Backbone.View.extend({
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

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var variable = Piplin.Variables.get($('#model_id').val());

        variable.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('variables.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#variable button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var variable_id = $('#variable_id').val();

        if (variable_id) {
            var variable = Piplin.Variables.get(variable_id);
        } else {
            var variable = new Piplin.Variable();
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('variables.edit_success');
                if (!variable_id) {
                    Piplin.Variables.add(response);
                    msg = trans('variables.create_success');
                }
                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    Piplin.Variable = Backbone.Model.extend({
        urlRoot: '/variables',
        initialize: function() {

        }
    });

    var Variables = Backbone.Collection.extend({
        model: Piplin.Variable
    });

    Piplin.Variables = new Variables();

    Piplin.VariablesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#variable_list tbody');

            $('#no_variables').show();
            $('#variable_list').hide();

            this.listenTo(Piplin.Variables, 'add', this.addOne);
            this.listenTo(Piplin.Variables, 'reset', this.addAll);
            this.listenTo(Piplin.Variables, 'remove', this.addAll);
            this.listenTo(Piplin.Variables, 'all', this.render);

            Piplin.listener.on('variable:' + Piplin.events.MODEL_CHANGED, function (data) {
                $('#variable_' + data.model.id).html(data.model.name);

                var variable = Piplin.Variables.get(parseInt(data.model.id));

                if (variable) {
                    variable.set(data.model);
                }
            });

            Piplin.listener.on('variable:' + Piplin.events.MODEL_CREATED, function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    Piplin.Variables.add(data.model);
                }
            });

            Piplin.listener.on('variable:' + Piplin.events.MODEL_TRASHED, function (data) {
                var variable = Piplin.Variables.get(parseInt(data.model.id));

                if (variable) {
                    Piplin.Variables.remove(variable);
                }
            });
        },
        render: function () {
            if (Piplin.Variables.length) {
                $('#no_variables').hide();
                $('#variable_list').show();
            } else {
                $('#no_variables').show();
                $('#variable_list').hide();
            }
        },
        addOne: function (variable) {

            var view = new Piplin.VariableView({
                model: variable
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Variables.each(this.addOne, this);
        }
    });

    Piplin.VariableView = Backbone.View.extend({
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

    $('#server').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('servers.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('servers.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#server_id').val('');
            $('#server_name').val('');
            $('#server_enabled').prop('checked', true);
            $('#server_address').val('');
            $('#server_port').val('22');
            $('#server_user').val('');
            $('#server_targetable_id').val($("#server_targetable_id option:selected").val());
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.server-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var server = Piplin.Servers.get($('#model_id').val());

        server.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('servers.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#server button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var server_id = $('#server_id').val();

        if (server_id) {
            var server = Piplin.Servers.get(server_id);
        } else {
            var server = new Piplin.Server();
        }

        server.save({
            name:            $('#server_name').val(),
            ip_address:      $('#server_address').val(),
            enabled:         $('#server_enabled').is(':checked'),
            port:            $('#server_port').val(),
            user:            $('#server_user').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   parseInt($('#server_targetable_id').val())
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('servers.edit_success');
                if (!server_id) {
                    Piplin.Servers.add(response);
                    msg = trans('servers.create_success');
                }
                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });


    Piplin.Server = Backbone.Model.extend({
        urlRoot: '/servers'
    });

    var Servers = Backbone.Collection.extend({
        model: Piplin.Server,
        comparator: function(serverA, serverB) {
            if (serverA.get('name') > serverB.get('name')) {
                return -1; // before
            } else if (serverA.get('name') < serverB.get('name')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    Piplin.Servers = new Servers();

    Piplin.ServersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#server_list tbody');

            $('#no_servers').show();
            $('#server_list').hide();

            this.listenTo(Piplin.Servers, 'add', this.addOne);
            this.listenTo(Piplin.Servers, 'reset', this.addAll);
            this.listenTo(Piplin.Servers, 'remove', this.addAll);
            this.listenTo(Piplin.Servers, 'all', this.render);

            Piplin.listener.on('server:' + Piplin.events.MODEL_CHANGED, function (data) {
                var server = Piplin.Servers.get(parseInt(data.model.id));

                if (server) {
                    // Fix me - targetable_type
                    if(Piplin.targetable_id == data.model.targetable_id) {
                        server.set(data.model);
                    } else {
                        Piplin.Servers.remove(server);
                    }
                }
            });

            Piplin.listener.on('server:' + Piplin.events.MODEL_CREATED, function (data) {
                if (parseInt(data.model.targetable_id) === parseInt(Piplin.targetable_id)) {
                    Piplin.Servers.add(data.model);
                }
            });

            Piplin.listener.on('server:' + Piplin.events.MODEL_TRASHED, function (data) {
                var server = Piplin.Servers.get(parseInt(data.model.id));

                if (server) {
                    Piplin.Servers.remove(server);
                }
            });
        },
        render: function () {
            if (Piplin.Servers.length) {
                $('#no_servers').hide();
                $('#server_list').show();
            } else {
                $('#no_servers').show();
                $('#server_list').hide();
            }
        },
        addOne: function (server) {

            var view = new Piplin.ServerView({
                model: server
            });

            this.$list.append(view.render().el);

            if (Piplin.Servers.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Servers.each(this.addOne, this);
        }
    });

    Piplin.ServerView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-test': 'testConnection',
            'click .btn-show': 'showLog',
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#server-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.status_css = 'orange';
            data.icon_css   = 'circle';
            data.status     = trans('servers.untested');

            if (parseInt(this.model.get('status')) === SUCCESSFUL) {
                data.status_css = 'success';
                data.status     = trans('servers.successful');
            } else if (parseInt(this.model.get('status')) === TESTING) {
                data.status_css = 'purple';
                data.icon_css   = 'load piplin-spin';
                data.status     = trans('servers.testing');
            } else if (parseInt(this.model.get('status')) === FAILED) {
                data.status_css = 'danger';
                data.status     = trans('servers.failed');
            }

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#server_id').val(this.model.id);
            $('#server_name').val(this.model.get('name'));
            $('#server_targetable_id')
                .select2(Piplin.select2_options)
                .val(this.model.get('targetable_id'))
                .trigger('change');
            $('#server_enabled').prop('checked', (this.model.get('enabled') === true));
            $('#server_address').val(this.model.get('ip_address'));
            $('#server_port').val(this.model.get('port'));
            $('#server_user').val(this.model.get('user'));
        },
        showLog: function() {
            var data = this.model.toJSON();

            $('#log pre').html(data.output);
        },
        trash: function() {
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
