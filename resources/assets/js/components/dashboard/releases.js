(function ($) {

    $('#release').on('show.bs.modal', function (event) {
        var modal = $(this);
        $('.callout-danger', modal).hide();
    });

    $('#release button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var release = new Piplin.Release();
        console.log('create release');

        release.save({
            project_id: $('input[name="project_id"]').val(),
            task_id:    $('input[name="task_id"]').val(),
            name:       $('#release_name').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('releases.create_success');
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

    Piplin.Release = Backbone.Model.extend({
        urlRoot: '/releases'
    });

})(jQuery);