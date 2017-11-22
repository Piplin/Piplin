(function ($) {

    // test
    var settings =  {
        placeholder: trans('cabinets.search'),
        minimumInputLength: 1,
        width: '100%',
        ajax: {
            url: "/api/autocomplete/cabinets",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term // search term
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (obj) {
                        return {id: obj.id, text: obj.name};
                    })
                };
            },
            cache: true
        }
    };

    var cabinet_select2 = $('.environment-cabinets').select2(settings);

    // end
    $('#cabinet').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('cabinets.link');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.callout-warning', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('cabinets.edit');
            $('#cabinet_ids').parent().parent().hide();
            $('.btn-danger', modal).show();
        } else {
            $('#cabinet_ids').parent().parent().show();
            cabinet_select2.val('').trigger('change');
            modal.find('.modal-title span').text(trans('cabinets.link'));
        }

        modal.find('.modal-title span').text(title);
    });

    $('#cabinet #cabinet-type a.btn-app').on('click', function(event) {
        var button = $(event.currentTarget);
        var modal = $('#cabinet');

        if (button.attr('disabled')) {
            $('.callout-warning', modal).show();
            return;
        }

        $('.callout-warning', modal).hide();
    });

    $('body').delegate('.cabinet-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var cabinet = Piplin.Cabinets.get($('#model_id').val());

        cabinet.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('cabinets.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#cabinet button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var cabinet_id = $('#cabinet_id').val();

        if (cabinet_id) {
            var cabinet = Piplin.Cabinets.get(cabinet_id);
        } else {
            var cabinet = new Piplin.Cabinet();
        }

        var data = {
          cabinet_ids:    $('#cabinet_ids').val(),
          environment_id: parseInt($('input[name="environment_id"]').val())
        };

        cabinet.save(data, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('cabinets.edit_success');
                if (!cabinet_id) {
                    Piplin.Cabinets.add(response);
                    msg = trans('cabinets.create_success');
                }
                Piplin.toast(msg);
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form select', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parent();
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

    Piplin.Cabinet = Backbone.Model.extend({
        urlRoot: '/cabinets/' + parseInt($('input[name="environment_id"]').val())
    });

    var Cabinets = Backbone.Collection.extend({
        model: Piplin.Cabinet
    });

    Piplin.Cabinets = new Cabinets();

    Piplin.CabinetsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#cabinet_list tbody');

            $('#no_cabinets').show();
            $('#cabinet_list').hide();

            this.listenTo(Piplin.Cabinets, 'add', this.addOne);
            this.listenTo(Piplin.Cabinets, 'reset', this.addAll);
            this.listenTo(Piplin.Cabinets, 'remove', this.addAll);
            this.listenTo(Piplin.Cabinets, 'all', this.render);

            Piplin.listener.on('cabinet:' + Piplin.events.MODEL_CHANGED, function (data) {
                var cabinet = Piplin.Cabinets.get(parseInt(data.model.id));

                if (cabinet) {
                    cabinet.set(data.model);
                }
            });

            Piplin.listener.on('cabinet:' + Piplin.events.MODEL_TRASHED, function (data) {
                var cabinet = Piplin.Cabinets.get(parseInt(data.model.id));

                if (cabinet) {
                    Piplin.Cabinets.remove(cabinet);
                }
            });
        },
        render: function () {
            if (Piplin.Cabinets.length) {
                $('#no_cabinets').hide();
                $('#cabinet_list').show();
            } else {
                $('#no_cabinets').show();
                $('#cabinet_list').hide();
            }
        },
        addOne: function (cabinet) {
            var view = new Piplin.CabinetView({
                model: cabinet
            });

            this.$list.append(view.render().el);

            $('.server-names', this.$list).tooltip();
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Cabinets.each(this.addOne, this);
        }
    });

    Piplin.CabinetView = Backbone.View.extend({
        tagName:  'tr',
        events: {
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
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade cabinet-trash');
        }
    });
})(jQuery);