(function ($) {

    $('#group_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('group-id'));
            });

            $.ajax({
                url: '/admin/groups/reorder',
                method: 'POST',
                data: {
                    groups: ids
                }
            });
        }
    });

    $('#group').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('groups.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('groups.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#group_id').val('');
            $('#group_name').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.group-trash button.btn-delete','click', function (event) {

        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var group = Piplin.Groups.get($('#model_id').val());

        group.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('groups.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#group button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var group_id = $('#group_id').val();

        if (group_id) {
            var group = Piplin.Groups.get(group_id);
        } else {
            var group = new Piplin.Group();
        }

        group.save({
            name: $('#group_name').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('groups.edit_success');
                if (!group_id) {
                    Piplin.Groups.add(response);
                    msg = trans('groups.create_success');
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

    Piplin.Group = Backbone.Model.extend({
        urlRoot: '/admin/groups',
        initialize: function() {

        }
    });

    var Groups = Backbone.Collection.extend({
        model: Piplin.Group
    });

    Piplin.Groups = new Groups();

    Piplin.GroupsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#group_list tbody');

            $('#group_list').hide();
            $('#no_groups').show();

            this.listenTo(Piplin.Groups, 'add', this.addOne);
            this.listenTo(Piplin.Groups, 'reset', this.addAll);
            this.listenTo(Piplin.Groups, 'remove', this.addAll);
            this.listenTo(Piplin.Groups, 'all', this.render);

            Piplin.listener.on('projectgroup:' + Piplin.events.MODEL_CHANGED, function (data) {
                $('#group_' + data.model.id).html(data.model.name);

                var group = Piplin.Groups.get(parseInt(data.model.id));

                if (group) {
                    group.set(data.model);
                }
            });

            Piplin.listener.on('projectgroup:' + Piplin.events.MODEL_CREATED, function (data) {
                Piplin.Groups.add(data.model);
            });

            Piplin.listener.on('projectgroup:' + Piplin.events.MODEL_TRASHED, function (data) {
                var group = Piplin.Groups.get(parseInt(data.model.id));

                if (group) {
                    Piplin.Groups.remove(group);
                }

                $('#group_' + data.model.id).parent('li').remove();

                if (parseInt(data.model.id) === parseInt(Piplin.group_id)) {
                    window.location.href = '/';
                }
            });
        },
        render: function () {
            if (Piplin.Groups.length) {
                $('#no_groups').hide();
                $('#group_list').show();
            } else {
                $('#no_groups').show();
                $('#group_list').hide();
            }
        },
        addOne: function (group) {

            var view = new Piplin.GroupView({
                model: group
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Groups.each(this.addOne, this);
        }
    });

    Piplin.GroupView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#group-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#group_id').val(this.model.id);
            $('#group_name').val(this.model.get('name'));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade group-trash');
        }

    });
})(jQuery);
