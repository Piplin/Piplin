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

    $('body').delegate('.release-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var release = Piplin.Releases.get($('#model_id').val());

        release.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('releases.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    Piplin.Release = Backbone.Model.extend({
        urlRoot: '/releases'
    });

    var Releases = Backbone.Collection.extend({
        model: Piplin.Release
    });

    Piplin.Releases = new Releases();

    Piplin.ReleasesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#release_list tbody');

            $('#no_releases').show();
            $('#release_list').hide();

            this.listenTo(Piplin.Releases, 'add', this.addOne);
            this.listenTo(Piplin.Releases, 'reset', this.addAll);
            this.listenTo(Piplin.Releases, 'remove', this.addAll);
            this.listenTo(Piplin.Releases, 'all', this.render);

            Piplin.listener.on('release:' + Piplin.events.MODEL_CHANGED, function (data) {
                var share = Piplin.Releases.get(parseInt(data.model.id));

                if (share) {
                    share.set(data.model);
                }
            });

            Piplin.listener.on('release:' + Piplin.events.MODEL_CREATED, function (data) {
                var build_plan_id = $('input[name="build_plan_id"]').val();
                if (parseInt(data.model.build_plan_id) === parseInt(build_plan_id)) {
                    Piplin.Releases.add(data.model);
                }
            });

            Piplin.listener.on('release:' + Piplin.events.MODEL_TRASHED, function (data) {
                var share = Piplin.Releases.get(parseInt(data.model.id));

                if (share) {
                    Piplin.Releases.remove(share);
                }
            });
        },
        render: function () {
            if (Piplin.Releases.length) {
                $('#no_releases').hide();
                $('#release_list').show();
            } else {
                $('#no_releases').show();
                $('#release_list').hide();
            }
        },
        addOne: function (release) {

            var view = new Piplin.ReleaseView({
                model: release
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Releases.each(this.addOne, this);
        }
    });

    Piplin.ReleaseView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#release-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#release_id').val(this.model.id);
            $('#name').val(this.model.get('name'));
            $('#copy_release').val(this.model.get('copy_release'));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade release-trash');
        }
    });

})(jQuery);