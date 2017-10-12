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

            app.listener.on('group:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                $('#group_' + data.model.id).html(data.model.name);

                var group = app.Groups.get(parseInt(data.model.id));

                if (group) {
                    group.set(data.model);
                }
            });

            app.listener.on('group:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                app.Groups.add(data.model);
            });

            app.listener.on('group:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
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

var app = app || {};

(function ($) {
    var SUCCESSFUL = 0;
    var UNTESTED   = 1;
    var FAILED     = 2;
    var TESTING    = 3;

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

        var key = app.Keys.get($('#model_id').val());

        key.destroy({
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

    $('#key button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var key_id = $('#key_id').val();

        if (key_id) {
            var key = app.Keys.get(key_id);
        } else {
            var key = new app.Key();
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

                if (!key_id) {
                    app.Keys.add(response);
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


    app.Key = Backbone.Model.extend({
        urlRoot: '/admin/keys'
    });

    var Keys = Backbone.Collection.extend({
        model: app.Key,
        comparator: function(keyA, keyB) {
            if (keyA.get('name') > keyB.get('name')) {
                return -1; // before
            } else if (keyA.get('name') < keyB.get('name')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    app.Keys = new Keys();

    app.KeysTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#key_list tbody');

            $('#no_keys').show();
            $('#key_list').hide();

            this.listenTo(app.Keys, 'add', this.addOne);
            this.listenTo(app.Keys, 'reset', this.addAll);
            this.listenTo(app.Keys, 'remove', this.addAll);
            this.listenTo(app.Keys, 'all', this.render);

            app.listener.on('key:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var key = app.Keys.get(parseInt(data.model.id));

                if (key) {
                    key.set(data.model);
                }
            });

            app.listener.on('key:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                app.Keys.add(data.model);
            });

            app.listener.on('key:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var key = app.Keys.get(parseInt(data.model.id));

                if (key) {
                    app.Keys.remove(key);
                }
            });
        },
        render: function () {
            if (app.Keys.length) {
                $('#no_keys').hide();
                $('#key_list').show();
            } else {
                $('#no_keys').show();
                $('#key_list').hide();
            }
        },
        addOne: function (key) {

            var view = new app.KeyView({
                model: key
            });

            this.$list.append(view.render().el);

            if (app.Keys.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            app.Keys.each(this.addOne, this);
        }
    });

    app.KeyView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-show': 'showKey',
            'click .btn-edit': 'editKey',
            'click .btn-delete': 'trashKey'
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
        editKey: function() {
            $('#key_id').val(this.model.id);
            $('#key_name').val(this.model.get('name'));
            $('#key_private_key').val(this.model.get('private_key'));

        },
        showKey: function() {
            var data = this.model.toJSON();

            $('#log pre').html(data.public_key);
        },
        trashKey: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade key-trash');
        }
    });
})(jQuery);
var app = app || {};

(function ($) {
    var SUCCESSFUL = 0;
    var UNTESTED   = 1;
    var FAILED     = 2;
    var TESTING    = 3;

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

        var link = app.Links.get($('#model_id').val());

        link.destroy({
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

    $('#link button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var link_id = $('#link_id').val();

        if (link_id) {
            var link = app.Links.get(link_id);
        } else {
            var link = new app.Link();
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

                if (!link_id) {
                    app.Links.add(response);
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


    app.Link = Backbone.Model.extend({
        urlRoot: '/admin/links'
    });

    var Links = Backbone.Collection.extend({
        model: app.Link,
        comparator: function(linkA, linkB) {
            if (linkA.get('title') > linkB.get('title')) {
                return -1; // before
            } else if (linkA.get('title') < linkB.get('title')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    app.Links = new Links();

    app.LinksTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#link_list tbody');

            $('#no_links').show();
            $('#link_list').hide();

            this.listenTo(app.Links, 'add', this.addOne);
            this.listenTo(app.Links, 'reset', this.addAll);
            this.listenTo(app.Links, 'remove', this.addAll);
            this.listenTo(app.Links, 'all', this.render);

            app.listener.on('link:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var link = app.Links.get(parseInt(data.model.id));

                if (link) {
                    link.set(data.model);
                }
            });

            app.listener.on('link:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                app.Links.add(data.model);
            });

            app.listener.on('link:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var link = app.Links.get(parseInt(data.model.id));

                if (link) {
                    app.Links.remove(link);
                }
            });
        },
        render: function () {
            if (app.Links.length) {
                $('#no_links').hide();
                $('#link_list').show();
            } else {
                $('#no_links').show();
                $('#link_list').hide();
            }
        },
        addOne: function (link) {

            var view = new app.LinkView({
                model: link
            });

            this.$list.append(view.render().el);

            if (app.Links.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            app.Links.each(this.addOne, this);
        }
    });

    app.LinkView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editLink',
            'click .btn-delete': 'trashLink'
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
        editLink: function() {
            $('#link_id').val(this.model.id);
            $('#link_title').val(this.model.get('title'));
            $('#link_url').val(this.model.get('url'));
            $('#link_description').val(this.model.get('description'));

        },
        trashLink: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade link-trash');
        }
    });
})(jQuery);
var app = app || {};

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
            $('#project_group_id').val($("#project_group_id option:selected").val());
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

            app.listener.on('project:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var project = app.Projects.get(parseInt(data.model.id));

                if (project) {
                    if(app.group_id == undefined || app.group_id == data.model.group_id) {
                        project.set(data.model);
                    } else {
                        app.Projects.remove(project);
                    }
                }
            });

            app.listener.on('project:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                app.Projects.add(data.model);
            });

            app.listener.on('project:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
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
            'click .btn-clone': 'cloneProject',
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
            $('#project_key_id').val(this.model.get('key_id'));
            $('#project_builds_to_keep').val(this.model.get('builds_to_keep'));
            $('#project_url').val(this.model.get('url'));
            $('#project_build_url').val(this.model.get('build_url'));
            $('#project_allow_other_branch').prop('checked', (this.model.get('allow_other_branch') === true));
            $('#project_need_approve').prop('checked', (this.model.get('need_approve') === true));
        },
        cloneProject: function() {
            $('#skeleton_id').val(this.model.id);
            $('#project_clone_name').val(this.model.get('name') + '_Clone');
        },
        trashProject: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade project-trash');
        }
    });
})(jQuery);

var app = app || {};

(function ($) {
    var SUCCESSFUL = 0;
    var UNTESTED   = 1;
    var FAILED     = 2;
    var TESTING    = 3;

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

        var provider = app.Providers.get($('#model_id').val());

        provider.destroy({
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

    $('#provider button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var provider_id = $('#provider_id').val();

        if (provider_id) {
            var provider = app.Providers.get(provider_id);
        } else {
            var provider = new app.Provider();
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

                if (!provider_id) {
                    app.Providers.add(response);
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


    app.Provider = Backbone.Model.extend({
        urlRoot: '/admin/providers'
    });

    var Providers = Backbone.Collection.extend({
        model: app.Provider,
        comparator: function(providerA, providerB) {
            if (providerA.get('name') > providerB.get('name')) {
                return -1; // before
            } else if (providerA.get('name') < providerB.get('name')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    app.Providers = new Providers();

    app.ProvidersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#provider_list tbody');

            $('#no_providers').show();
            $('#provider_list').hide();

            this.listenTo(app.Providers, 'add', this.addOne);
            this.listenTo(app.Providers, 'reset', this.addAll);
            this.listenTo(app.Providers, 'remove', this.addAll);
            this.listenTo(app.Providers, 'all', this.render);

            app.listener.on('provider:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var provider = app.providers.get(parseInt(data.model.id));

                if (provider) {
                    provider.set(data.model);
                }
            });

            app.listener.on('provider:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                    app.Providers.add(data.model);
            });

            app.listener.on('provider:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var provider = app.Providers.get(parseInt(data.model.id));

                if (provider) {
                    app.Providers.remove(provider);
                }
            });
        },
        render: function () {
            if (app.Providers.length) {
                $('#no_providers').hide();
                $('#provider_list').show();
            } else {
                $('#no_providers').show();
                $('#provider_list').hide();
            }
        },
        addOne: function (provider) {

            var view = new app.ProviderView({
                model: provider
            });

            this.$list.append(view.render().el);

            if (app.Providers.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            app.Providers.each(this.addOne, this);
        }
    });

    app.ProviderView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editProvider',
            'click .btn-delete': 'trashProvider'
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
        editProvider: function() {
            $('#provider_id').val(this.model.id);
            $('#provider_name').val(this.model.get('name'));
            $('#provider_slug').val(this.model.get('slug'));
            $('#provider_icon').val(this.model.get('icon'));
            $('#provider_description').val(this.model.get('description'));

        },
        trashProvider: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade provider-trash');
        }
    });
})(jQuery);
var app = app || {};

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

            app.listener.on('template:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var template = app.Templates.get(parseInt(data.model.id));

                if (template) {
                    template.set(data.model);
                }
            });

            app.listener.on('template:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                app.Templates.add(data.model);
            });

            app.listener.on('template:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
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

        var tip = app.Tips.get($('#model_id').val());

        tip.destroy({
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

    $('#tip button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var tip_id = $('#tip_id').val();

        if (tip_id) {
            var tip = app.Tips.get(tip_id);
        } else {
            var tip = new app.Tip();
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

                if (!tip_id) {
                    app.Tips.add(response);
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


    app.Tip = Backbone.Model.extend({
        urlRoot: '/admin/tips'
    });

    var tips = Backbone.Collection.extend({
        model: app.Tip,
        comparator: function(tipA, tipB) {
            if (tipA.get('id') > tipB.get('id')) {
                return -1; // before
            } else if (tipA.get('id') < tipB.get('id')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    app.Tips = new tips();

    app.TipsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#tip_list tbody');

            $('#no_tips').show();
            $('#tip_list').hide();

            this.listenTo(app.Tips, 'add', this.addOne);
            this.listenTo(app.Tips, 'reset', this.addAll);
            this.listenTo(app.Tips, 'remove', this.addAll);
            this.listenTo(app.Tips, 'all', this.render);

            app.listener.on('tip:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var tip = app.Tips.get(parseInt(data.model.id));

                if (tip) {
                    tip.set(data.model);
                }
            });

            app.listener.on('tip:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                app.Tips.add(data.model);
            });

            app.listener.on('tip:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var tip = app.Tips.get(parseInt(data.model.id));

                if (tip) {
                    app.Tips.remove(tip);
                }
            });
        },
        render: function () {
            if (app.Tips.length) {
                $('#no_tips').hide();
                $('#tip_list').show();
            } else {
                $('#no_tips').show();
                $('#tip_list').hide();
            }
        },
        addOne: function (tip) {

            var view = new app.TipView({
                model: tip
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Tips.each(this.addOne, this);
        }
    });

    app.TipView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-show': 'showTip',
            'click .btn-edit': 'editTip',
            'click .btn-delete': 'trashTip'
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
        editTip: function() {
            $('#tip_id').val(this.model.id);
            $('#tip_body').val(this.model.get('body'));
            $('#tip_status').prop('checked', (this.model.get('status') === true));

        },
        showTip: function() {
            var data = this.model.toJSON();

            $('#tip_preview').html(data.body);
        },
        trashTip: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade tip-trash');
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

            app.listener.on('command:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var command = app.Commands.get(parseInt(data.model.id));

                if (command) {
                    command.set(data.model);
                }
            });

            app.listener.on('command:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
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

            app.listener.on('command:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
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
            app.Commands.each(this.addOne, this);
        }
    });

    app.CommandView = Backbone.View.extend({
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
            name:       $('#name').val(),
            path:       $('#path').val(),
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

            app.listener.on('configfile:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var file = app.ConfigFiles.get(parseInt(data.model.id));

                if (file) {
                    file.set(data.model);
                }
            });

            app.listener.on('configfile:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    app.ConfigFiles.add(data.model);
                }
            });

            app.listener.on('configfile:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
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
        },
        trash: function () {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade configfile-trash');
        }
    });

})(jQuery);

var app = app || {};

(function ($) {
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

        var environment = app.Environments.get($('#model_id').val());

        environment.destroy({
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

    $('#environment button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var environment_id = $('#environment_id').val();

        if (environment_id) {
            var environment = app.Environments.get(environment_id);
        } else {
            var environment = new app.Environment();
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

                if (!environment_id) {
                    app.Environments.add(response);
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

    app.Environment = Backbone.Model.extend({
        urlRoot: '/environments',
        initialize: function() {

        }
    });

    var Environments = Backbone.Collection.extend({
        model: app.Environment
    });

    app.Environments = new Environments();

    app.EnvironmentsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#environment_list tbody');

            $('#no_environments').show();
            $('#environment_list').hide();

            this.listenTo(app.Environments, 'add', this.addOne);
            this.listenTo(app.Environments, 'reset', this.addAll);
            this.listenTo(app.Environments, 'remove', this.addAll);
            this.listenTo(app.Environments, 'all', this.render);

            app.listener.on('environment:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                $('#environment_' + data.model.id).html(data.model.name);

                var environment = app.Environments.get(parseInt(data.model.id));

                if (environment) {
                    environment.set(data.model);
                }
            });

            app.listener.on('environment:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    app.Environments.add(data.model);
                }
            });

            app.listener.on('environment:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var environment = app.Environments.get(parseInt(data.model.id));

                if (environment) {
                    app.Environments.remove(environment);
                }
            });
        },
        render: function () {
            if (app.Environments.length) {
                $('#no_environments').hide();
                $('#environment_list').show();
            } else {
                $('#no_environments').show();
                $('#environment_list').hide();
            }
        },
        addOne: function (environment) {

            var view = new app.EnvironmentView({
                model: environment
            });

            this.$list.append(view.render().el);

            if (app.Environments.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            app.Environments.each(this.addOne, this);
        }
    });

    app.EnvironmentView = Backbone.View.extend({
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

var app = app || {};

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

    $('#sharedfile button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file_id = $('#sharedfile_id').val();

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
            this.$list = $('#sharedfile_list tbody');

            $('#no_sharedfiles').show();
            $('#sharedfile_list').hide();

            this.listenTo(app.SharedFiles, 'add', this.addOne);
            this.listenTo(app.SharedFiles, 'reset', this.addAll);
            this.listenTo(app.SharedFiles, 'remove', this.addAll);
            this.listenTo(app.SharedFiles, 'all', this.render);

            app.listener.on('sharedfile:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var share = app.SharedFiles.get(parseInt(data.model.id));

                if (share) {
                    share.set(data.model);
                }
            });

            app.listener.on('sharedfile:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    app.SharedFiles.add(data.model);
                }
            });

            app.listener.on('sharedfile:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var share = app.SharedFiles.get(parseInt(data.model.id));

                if (share) {
                    app.SharedFiles.remove(share);
                }
            });
        },
        render: function () {
            if (app.SharedFiles.length) {
                $('#no_sharedfiles').hide();
                $('#sharedfile_list').show();
            } else {
                $('#no_sharedfiles').show();
                $('#sharedfile_list').hide();
            }
        },
        addOne: function (file) {

            var view = new app.SharedFileView({
                model: file
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.SharedFiles.each(this.addOne, this);
        }
    });

    app.SharedFileView = Backbone.View.extend({
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

var app = app || {};

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

            $('#no_variables').show();
            $('#variable_list').hide();

            this.listenTo(app.Variables, 'add', this.addOne);
            this.listenTo(app.Variables, 'reset', this.addAll);
            this.listenTo(app.Variables, 'remove', this.addAll);
            this.listenTo(app.Variables, 'all', this.render);

            app.listener.on('variable:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                $('#variable_' + data.model.id).html(data.model.name);

                var variable = app.Variables.get(parseInt(data.model.id));

                if (variable) {
                    variable.set(data.model);
                }
            });

            app.listener.on('variable:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    app.Variables.add(data.model);
                }
            });

            app.listener.on('variable:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var variable = app.Variables.get(parseInt(data.model.id));

                if (variable) {
                    app.Variables.remove(variable);
                }
            });
        },
        render: function () {
            if (app.Variables.length) {
                $('#no_variables').hide();
                $('#variable_list').show();
            } else {
                $('#no_variables').show();
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
