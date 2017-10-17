(function ($) {
    var SUCCESSFUL = 0;
    var UNTESTED   = 1;
    var FAILED     = 2;
    var TESTING    = 3;

    $('#tip_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500
    });

    $('#tip').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('tips.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('tips.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#tip_id').val('');
            $('#tip_body').val('');
            $('#tip_status').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.tip-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var tip = Fixhub.Tips.get($('#model_id').val());

        tip.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#tip button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var tip_id = $('#tip_id').val();

        if (tip_id) {
            var tip = Fixhub.Tips.get(tip_id);
        } else {
            var tip = new Fixhub.Tip();
        }

        tip.save({
            body:   $('#tip_body').val(),
            status: $('#tip_status').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!tip_id) {
                    Fixhub.Tips.add(response);
                }
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input, form textarea', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent('div');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });


    Fixhub.Tip = Backbone.Model.extend({
        urlRoot: '/admin/tips'
    });

    var tips = Backbone.Collection.extend({
        model: Fixhub.Tip,
        comparator: function(tipA, tipB) {
            if (tipA.get('id') > tipB.get('id')) {
                return -1; // before
            } else if (tipA.get('id') < tipB.get('id')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    Fixhub.Tips = new tips();

    Fixhub.TipsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#tip_list tbody');

            $('#no_tips').show();
            $('#tip_list').hide();

            this.listenTo(Fixhub.Tips, 'add', this.addOne);
            this.listenTo(Fixhub.Tips, 'reset', this.addAll);
            this.listenTo(Fixhub.Tips, 'remove', this.addAll);
            this.listenTo(Fixhub.Tips, 'all', this.render);

            Fixhub.listener.on('tip:' + Fixhub.events.MODEL_CHANGED, function (data) {
                var tip = Fixhub.Tips.get(parseInt(data.model.id));

                if (tip) {
                    tip.set(data.model);
                }
            });

            Fixhub.listener.on('tip:' + Fixhub.events.MODEL_CREATED, function (data) {
                Fixhub.Tips.add(data.model);
            });

            Fixhub.listener.on('tip:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var tip = Fixhub.Tips.get(parseInt(data.model.id));

                if (tip) {
                    Fixhub.Tips.remove(tip);
                }
            });
        },
        render: function () {
            if (Fixhub.Tips.length) {
                $('#no_tips').hide();
                $('#tip_list').show();
            } else {
                $('#no_tips').show();
                $('#tip_list').hide();
            }
        },
        addOne: function (tip) {

            var view = new Fixhub.TipView({
                model: tip
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.Tips.each(this.addOne, this);
        }
    });

    Fixhub.TipView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-show': 'show',
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#tip-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#tip_id').val(this.model.id);
            $('#tip_body').val(this.model.get('body'));
            $('#tip_status').prop('checked', (this.model.get('status') === true));

        },
        show: function() {
            var data = this.model.toJSON();

            $('#tip_preview').html(data.body);
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade tip-trash');
        }
    });
})(jQuery);