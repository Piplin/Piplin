(function ($) {

    var AUTOMATIC = 1;
    var MANUAL = 2;

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
            Fixhub.EnvironmentLinks.each(function (environment){
                $('#link_opposite_environment_' + environment.id).prop('checked', true);
            });
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
            link_type:        $('#link_type').val(),
            environments:   environment_ids
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('fixhub fixhub-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.EnvironmentLinks.reset(response);

                //Fixhub.EnvironmentLinks.add(response);

                /*
                var str = [];
                $.each(response, function(index, content) {
                    str.push(content.name)
                });

                $('.opposite-environments').fadeIn(interval).html(str.join(','));
                */
                //Fixhub.EnvironmentLinks.reset();
                //Fixhub.EnvironmentLinks.add(response);

                Fixhub.toast(trans('environments.link_success'));
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

            this.listenTo(Fixhub.EnvironmentLinks, 'add', this.addOne);
            this.listenTo(Fixhub.EnvironmentLinks, 'reset', this.addAll);
            this.listenTo(Fixhub.EnvironmentLinks, 'remove', this.addAll);
            this.listenTo(Fixhub.EnvironmentLinks, 'all', this.render);
        },
        render: function () {
            if (Fixhub.EnvironmentLinks.length) {
                $('#no_links').hide();
                $('#link_list').show();
            } else {
                $('#no_links').show();
                $('#link_list').hide();
            }
        },
        addOne: function (link) {

            var view = new Fixhub.EnvironmentLinkView({
                model: link
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.EnvironmentLinks.each(this.addOne, this);
        }
    });

    Fixhub.EnvironmentLinkView = Backbone.View.extend({
        tagName:  'tr',
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);
            this.template = _.template($('#link-template').html());
        },
        render: function () {
             var data = this.model.toJSON();

             var link_type = parseInt(data.pivot.link_type);

             if (link_type == AUTOMATIC) {
                data.link_type = trans('environments.link_auto');
             } else {
                data.link_type = trans('environments.link_manual');
             }

             this.$el.html(this.template(data));

            return this;
        }
    });


})(jQuery);