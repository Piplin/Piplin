(function ($) {

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
            $('.link-environment').prop('checked', false);
        } else {
            //$('#link_id').val('');
            $('.link-environment').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    $('#link button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('fixhub fixhub-load fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();


        var environment_link = new Fixhub.EnvironmentLink();

        var environment_ids = [];

        $('.link-environment:checked').each(function() {
            environment_ids.push($(this).val());
        });

        //console.log(environment_link);
        //console.log(environment_ids);

        var interval = 3000;
        $('.opposite-environments').fadeOut(interval);

        environment_link.save({
            environment_id:   $('input[name="environment_id"]').val(),
            link_id:        $('#link_id').val(),
            environments:   environment_ids
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('fixhub fixhub-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var str = [];
                $.each(response, function(index, content) {
                    str.push(content.name)
                });

                $('.opposite-environments').fadeIn(interval).html(str.join(','));

                Fixhub.toast(trans('environmentLinks.edit_success'));
            },
            error: function(model, response, options) {
                icon.removeClass().addClass('fixhub fixhub-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });

    });

    Fixhub.EnvironmentLink = Backbone.Model.extend({
        urlRoot: '/environment-links'
    });

    var EnvironmentLinks = Backbone.Collection.extend({
        model: Fixhub.EnvironmentLink
    });

    Fixhub.EnvironmentLinks = new EnvironmentLinks();

    Fixhub.EnvironmentLinksTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#link_list tbody');

            $('#no_links').show();
            $('#link_list').hide();
        }
    });


})(jQuery);