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
        } else if (target.val() === 'commit') {
            $('#deployment_commit').parent('div').show();
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

        if (source === 'branch' || source === 'tag' || source === 'commit') {
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

    $('.repo-refresh').on('click', function (event) {
        var target = $(event.currentTarget);
        var project_id = target.data('project-id');
        var icon = $('i', target);

        if ($('.fixhub-spin', target).length > 0) {
            return;
        }
        $('span', target).html('loading');
        target.attr('disabled', 'disabled');
        icon.addClass('fixhub-spin');

        $.ajax({
            type: 'GET',
            url: '/repository/' + project_id + '/refresh'
        }).fail(function (response) {

        }).done(function (data) {
            $('span', target).html(data.last_mirrored).addClass('text-success');
        }).always(function () {
            icon.removeClass('fixhub-spin');
            target.removeAttr('disabled');
        });

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
            $('#webhook').fadeIn(interval).html(data.url);
        }).always(function () {
            icon.removeClass('fixhub-spin');
            target.removeAttr('disabled');
        });
    });
})(jQuery);
