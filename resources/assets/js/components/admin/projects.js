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
            $('#project_targetable_id').select2(Fixhub.select2_options)
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

        icon.removeClass().addClass('fixhub fixhub-load fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var project = Fixhub.Projects.get($('#model_id').val());

        project.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('fixhub fixhub-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('projects.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('fixhub fixhub-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#project button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('fixhub fixhub-load fixhub-spin');
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
            targetable_id:      $('#project_targetable_id').val(),
            key_id:             $('#project_key_id').val(),
            deploy_path:        $('#project_deploy_path').val(),
            builds_to_keep:     $('#project_builds_to_keep').val(),
            url:                $('#project_url').val(),
            build_url:          $('#project_build_url').val(),
            template_id:        $('#project_template_id') ? $('#project_template_id').val() : null,
            allow_other_branch: $('#project_allow_other_branch').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('fixhub fixhub-save');
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

                icon.removeClass().addClass('fixhub fixhub-save');
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
                    if(Fixhub.group_id == undefined || Fixhub.group_id == data.model.targetable_id) {
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
            $('#project_targetable_id').select2(Fixhub.select2_options)
                                    .val(this.model.get('targetable_id'))
                                    .trigger('change');
            $('#project_key_id').val(this.model.get('key_id'));
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
