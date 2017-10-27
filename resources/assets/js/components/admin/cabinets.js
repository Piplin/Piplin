(function ($) {

    $('#cabinet_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('cabinet-id'));
            });

            $.ajax({
                url: '/admin/cabinets/reorder',
                method: 'POST',
                data: {
                    cabinets: ids
                }
            });
        }
    });

    $('#cabinet').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('cabinets.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('cabinets.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#cabinet_id').val('');
            $('#cabinet_name').val('');
            $('#cabinet_description').val('');
            $('#cabinet_key_id').val($("#cabinet_key_id option:first").val());
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.cabinet-trash button.btn-delete','click', function (event) {

        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('fixhub fixhub-load fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var cabinet = Fixhub.Cabinets.get($('#model_id').val());

        cabinet.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('fixhub fixhub-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Fixhub.toast(trans('cabinets.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('fixhub fixhub-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#cabinet button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('fixhub fixhub-load fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var cabinet_id = $('#cabinet_id').val();

        if (cabinet_id) {
            var cabinet = Fixhub.Cabinets.get(cabinet_id);
        } else {
            var cabinet = new Fixhub.Cabinet();
        }

        cabinet.save({
            name:        $('#cabinet_name').val(),
            description: $('#cabinet_description').val(),
            key_id:      $('#cabinet_key_id').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('fixhub fixhub-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('cabinets.edit_success');
                if (!cabinet_id) {
                    Fixhub.Cabinets.add(response);
                    msg = trans('cabinets.create_success');
                }
                Fixhub.toast(msg);
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

                icon.removeClass().addClass('fixhub fixhub-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    Fixhub.Cabinet = Backbone.Model.extend({
        urlRoot: '/admin/cabinets',
        initialize: function() {

        }
    });

    var Cabinets = Backbone.Collection.extend({
        model: Fixhub.Cabinet
    });

    Fixhub.Cabinets = new Cabinets();

    Fixhub.CabinetsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#cabinet_list tbody');

            $('#cabinet_list').hide();
            $('#no_cabinets').show();

            this.listenTo(Fixhub.Cabinets, 'add', this.addOne);
            this.listenTo(Fixhub.Cabinets, 'reset', this.addAll);
            this.listenTo(Fixhub.Cabinets, 'remove', this.addAll);
            this.listenTo(Fixhub.Cabinets, 'all', this.render);

            Fixhub.listener.on('cabinet:' + Fixhub.events.MODEL_CHANGED, function (data) {
                $('#cabinet_' + data.model.id).html(data.model.name);

                var cabinet = Fixhub.Cabinets.get(parseInt(data.model.id));

                if (cabinet) {
                    cabinet.set(data.model);
                }
            });

            Fixhub.listener.on('cabinet:' + Fixhub.events.MODEL_CREATED, function (data) {
                Fixhub.Cabinets.add(data.model);
            });

            Fixhub.listener.on('cabinet:' + Fixhub.events.MODEL_TRASHED, function (data) {
                var cabinet = Fixhub.Cabinets.get(parseInt(data.model.id));

                if (cabinet) {
                    Fixhub.Cabinets.remove(cabinet);
                }

                $('#cabinet_' + data.model.id).parent('li').remove();

                if (parseInt(data.model.id) === parseInt(Fixhub.cabinet_id)) {
                    window.location.href = '/';
                }
            });
        },
        render: function () {
            if (Fixhub.Cabinets.length) {
                $('#no_cabinets').hide();
                $('#cabinet_list').show();
            } else {
                $('#no_cabinets').show();
                $('#cabinet_list').hide();
            }
        },
        addOne: function (cabinet) {

            var view = new Fixhub.CabinetView({
                model: cabinet
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.Cabinets.each(this.addOne, this);
        }
    });

    Fixhub.CabinetView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#cabinet-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#cabinet_id').val(this.model.id);
            $('#cabinet_name').val(this.model.get('name'));
            $('#cabinet_description').val(this.model.get('description'));
            $('#cabinet_key_id').select2(Fixhub.select2_options)
                                .val(this.model.get('key_id'))
                                .trigger('change');
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade cabinet-trash');
        }

    });
})(jQuery);
