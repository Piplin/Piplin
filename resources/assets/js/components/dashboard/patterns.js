(function ($) {

    $('#pattern').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('patterns.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('patterns.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#pattern_id').val('');
            $('#name').val('');
            $('#copy_pattern').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.pattern-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var pattern = Piplin.Patterns.get($('#model_id').val());

        pattern.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('patterns.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#pattern button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var pattern_id = $('#pattern_id').val();

        if (pattern_id) {
            var pattern = Piplin.Patterns.get(pattern_id);
        } else {
            var pattern = new Piplin.Pattern();
        }

        pattern.save({
            name:          $('#name').val(),
            copy_pattern:  $('#copy_pattern').val(),
            build_plan_id: $('input[name="build_plan_id"]').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('patterns.edit_success');
                if (!pattern_id) {
                    Piplin.Patterns.add(response);
                    trans('patterns.create_success');
                }

                Piplin.toast(msg);
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

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    Piplin.Pattern = Backbone.Model.extend({
        urlRoot: '/patterns'
    });

    var Patterns = Backbone.Collection.extend({
        model: Piplin.Pattern
    });

    Piplin.Patterns = new Patterns();

    Piplin.PatternsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#pattern_list tbody');

            $('#no_patterns').show();
            $('#pattern_list').hide();

            this.listenTo(Piplin.Patterns, 'add', this.addOne);
            this.listenTo(Piplin.Patterns, 'reset', this.addAll);
            this.listenTo(Piplin.Patterns, 'remove', this.addAll);
            this.listenTo(Piplin.Patterns, 'all', this.render);

            Piplin.listener.on('pattern:' + Piplin.events.MODEL_CHANGED, function (data) {
                var share = Piplin.Patterns.get(parseInt(data.model.id));

                if (share) {
                    share.set(data.model);
                }
            });

            Piplin.listener.on('pattern:' + Piplin.events.MODEL_CREATED, function (data) {
                var build_plan_id = $('input[name="build_plan_id"]').val();
                if (parseInt(data.model.build_plan_id) === parseInt(build_plan_id)) {
                    Piplin.Patterns.add(data.model);
                }
            });

            Piplin.listener.on('pattern:' + Piplin.events.MODEL_TRASHED, function (data) {
                var share = Piplin.Patterns.get(parseInt(data.model.id));

                if (share) {
                    Piplin.Patterns.remove(share);
                }
            });
        },
        render: function () {
            if (Piplin.Patterns.length) {
                $('#no_patterns').hide();
                $('#pattern_list').show();
            } else {
                $('#no_patterns').show();
                $('#pattern_list').hide();
            }
        },
        addOne: function (pattern) {

            var view = new Piplin.PatternView({
                model: pattern
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Patterns.each(this.addOne, this);
        }
    });

    Piplin.PatternView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#pattern-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#pattern_id').val(this.model.id);
            $('#name').val(this.model.get('name'));
            $('#copy_pattern').val(this.model.get('copy_pattern'));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade pattern-trash');
        }
    });

})(jQuery);
