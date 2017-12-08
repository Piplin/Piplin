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
