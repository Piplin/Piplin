(function ($) {

    $('#project_create').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('projects.create');

        var project_id = button.data('project-id');

        if (button.hasClass('btn-edit')) {
            title = trans('projects.edit');
            $.ajax({
                type: 'POST',
                url: '/api/projects',
                data: {
                    project_id: project_id
                }
            }).done(function (data) {

                Piplin.Projects.reset(data);

                $('#project_id').val(data.id);
                $('#project_name').val(data.name);
                $('#project_description').val(data.description);
                $('#project_repository').val(data.repository);
                $('#project_branch').val(data.branch);
                $('#project_deploy_path').val(data.deploy_path);
                $('#project_allow_other_branch').prop('checked', (data.allow_other_branch === true));
            });
        }

        modal.find('.modal-title span').text(title);
        $('.callout-danger', modal).hide();
    });

    $('#project_create button.btn-save').on('click', function (event) {
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
            description:        $('#project_description').val(),
            repository:         $('#project_repository').val(),
            branch:             $('#project_branch').val(), 
            deploy_path:        $('#project_deploy_path').val(),
            allow_other_branch: $('#project_allow_other_branch').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.Projects.reset(response);
                var msg = trans('projects.edit_success');
                if (!project_id) {
                     msg = trans('projects.create_success');
                }
                Piplin.toast(msg, '', 'success');
                window.location.href = '/project/' + response.id;
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

    $('#model-trash').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('projects.delete');

        var project_id = button.data('project-id');

        if (button.hasClass('project-delete')) {
            var target = $('#model_id');
            target.val(project_id);
            target.parents('.modal').removeClass().addClass('modal fade project-trash');
            $.ajax({
                type: 'POST',
                url: '/api/projects',
                data: {
                    project_id: project_id
                }
            }).done(function (data) {
                Piplin.Projects.reset(data);
            });
        }
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
                window.location.href = '/';
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#project-recover button.btn-recover').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        $.ajax({
            url: '/project/' + $('input[name="project_id"]', dialog).val() + '/recover',
            method: 'POST'
        }).done(function (data) {
            dialog.modal('hide');
            $('.callout-danger', dialog).hide();
            Piplin.toast(trans('projects.recover_success'));

            var status_data = Piplin.formatProjectStatus(data.status);
            var status_bar = $('td.project-status span');

            status_bar.attr('class', 'text-' + status_data.label_class)
            $('i', status_bar).attr('class', 'piplin piplin-' + status_data.icon_class);
            $('span', status_bar).text(status_data.label);

            icon.removeClass().addClass('piplin piplin-save');
            $('button.close', dialog).show();

        });
    });

    Piplin.Project = Backbone.Model.extend({
        urlRoot: '/projects'
    });

    var Projects = Backbone.Collection.extend({
        model: Piplin.Project
    });

    Piplin.Projects = new Projects();

    $('#new_webhook').on('click', function(event) {
        var target = $(event.currentTarget);
        var project_id = target.data('project-id');
        var type = target.data('type');
        var icon = $('i', target);
        var interval = 3000;

        var url = '/webhook/' + project_id + '/refresh';

        if (type == 'build') {
            url += '/build';
        }


        if ($('.piplin-spin', target).length > 0) {
            return;
        }

        target.attr('disabled', 'disabled');

        icon.addClass('piplin-spin');
        $('#webhook').fadeOut(interval);

        $.ajax({
            type: 'GET',
            url: url
        }).fail(function (response) {

        }).done(function (data) {
            $('#webhook').fadeIn(interval).val(data.url);
        }).always(function () {
            icon.removeClass('piplin-spin');
            target.removeAttr('disabled');
        });
    });
})(jQuery);
