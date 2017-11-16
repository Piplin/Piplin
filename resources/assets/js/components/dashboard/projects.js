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

                Fixhub.Projects.reset(data);

                $('#project_id').val(data.id);
                $('#project_name').val(data.name);
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
            deploy_path:        $('#project_deploy_path').val(),
            allow_other_branch: $('#project_allow_other_branch').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('fixhub fixhub-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.Projects.reset(response);
                var msg = trans('projects.edit_success');
                if (!project_id) {
                     msg = trans('projects.create_success');
                }
                Fixhub.toast(msg, '', 'success');
                window.location.href = '/projects/' + response.id;
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
                Fixhub.Projects.reset(data);
            });
        }
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
                window.location.href = '/';
            },
            error: function() {
                icon.removeClass().addClass('fixhub fixhub-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    Fixhub.Project = Backbone.Model.extend({
        urlRoot: '/projects'
    });

    var Projects = Backbone.Collection.extend({
        model: Fixhub.Project
    });

    Fixhub.Projects = new Projects();

    $('#deploy').on('show.bs.modal', function (event) {
        var modal = $(this);
        $('.callout-danger', modal).hide();
    });

    $('.deployment-source:radio').on('change', function (event) {
        var target = $(event.currentTarget);

        $('div.deployment-source-container').hide();
        if (target.val() === 'branch') {
            $('#deployment_branch').parent('div').show();
        } else if (target.val() === 'tag') {
            $('#deployment_tag').parent('div').show();
        } else if (target.val() === 'commit') {
            $('#deployment_commit').parent('div').show();
        }
    });

    $('#deploy button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');
        var source = $('input[name=source]:checked').val();

        $('.has-error', source).removeClass('has-error');

        if (source === 'branch' || source === 'tag' || source === 'commit') {
            if ($('#deployment_' + source).val() === '') {
                $('#deployment_' + source).parentsUntil('div').addClass('has-error');

                $('.callout-danger', dialog).show();
                event.stopPropagation();
                return;
            }
        }

        icon.removeClass().addClass('fixhub fixhub-load fixhub-spin');
        $('button.close', dialog).hide();
    });

    $('#new_webhook').on('click', function(event) {
        var target = $(event.currentTarget);
        var project_id = target.data('project-id');
        var icon = $('i', target);
        var interval = 3000;

        if ($('.fixhub-spin', target).length > 0) {
            return;
        }

        target.attr('disabled', 'disabled');

        icon.addClass('fixhub-spin');
        $('#webhook').fadeOut(interval);

        $.ajax({
            type: 'GET',
            url: '/webhook/' + project_id + '/refresh'
        }).fail(function (response) {

        }).done(function (data) {
            $('#webhook').fadeIn(interval).val(data.url);
        }).always(function () {
            icon.removeClass('fixhub-spin');
            target.removeAttr('disabled');
        });
    });
})(jQuery);
