(function ($) {

    $('#user').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('users.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.existing-only', modal).hide();
        $('.new-only', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();


        if (button.hasClass('btn-edit')) {
            title = trans('users.edit');
            $('.btn-danger', modal).show();
            $('.existing-only', modal).show();
        } else {
            $('#user_id').val('');
            $('#user_name').val('');
            //$('#user_level').val($("#user_level option:first").val());
            $('#user_level').select2(Piplin.select2_options);
            $('#user_nickname').val('');
            $('#user_email').val('');
            $('#user_password').val('');
            $('#user_password_confirmation').val('');

            $('.new-only', modal).show();
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.user-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var user = Piplin.Users.get($('#model_id').val());

        user.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                Piplin.toast(trans('users.delete_success'));
            },
            error: function() {
                icon.removeClass().addClass('piplin piplin-delete');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    $('#user button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.removeClass().addClass('piplin piplin-load piplin-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var user_id = $('#user_id').val();

        if (user_id) {
            var user = Piplin.Users.get(user_id);
        } else {
            var user = new Piplin.User();
        }

        user.save({
            name:                  $('#user_name').val(),
            level:                 $('#user_level').val(),
            nickname:              $('#user_nickname').val(),
            email:                 $('#user_email').val(),
            password:              $('#user_password').val(),
            password_confirmation: $('#user_password_confirmation').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass().addClass('piplin piplin-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                var msg = trans('users.edit_success');
                if (!user_id) {
                    Piplin.Users.add(response);
                    msg = trans('users.create_success');
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

    Piplin.User = Backbone.Model.extend({
        urlRoot: '/admin/users',
        initialize: function() {

        }
    });

    var Users = Backbone.Collection.extend({
        model: Piplin.User
    });

    Piplin.Users = new Users();

    Piplin.UsersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#user_list tbody');

            this.listenTo(Piplin.Users, 'add', this.addOne);
            this.listenTo(Piplin.Users, 'reset', this.addAll);
            this.listenTo(Piplin.Users, 'remove', this.addAll);
            this.listenTo(Piplin.Users, 'all', this.render);

            Piplin.listener.on('user:' + Piplin.events.MODEL_CHANGED, function (data) {
                var user = Piplin.Users.get(parseInt(data.model.id));

                if (user) {
                    user.set(data.model);
                }
            });

            Piplin.listener.on('user:' + Piplin.events.MODEL_CREATED, function (data) {
                Piplin.Users.add(data.model);
            });

            Piplin.listener.on('user:' + Piplin.events.MODEL_TRASHED, function (data) {
                var user = Piplin.Users.get(parseInt(data.model.id));

                if (user) {
                    Piplin.Users.remove(user);
                }
            });
        },
        addOne: function (user) {
            var view = new Piplin.UserView({
                model: user
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            Piplin.Users.each(this.addOne, this);
        }
    });

    Piplin.UserView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#user-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.created = moment(data.created_at).format('YYYY-MM-DD HH:mm:ss');

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#user_id').val(this.model.id);
            $('#user_name').val(this.model.get('name'));
            //$('#user_level').val(this.model.get('level'));
            $('#user_level').select2(Piplin.select2_options)
                                .val(this.model.get('level'))
                                .trigger('change');
            $('#user_nickname').val(this.model.get('nickname'));
            $('#user_email').val(this.model.get('email'));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade user-trash');
        }
    });
})(jQuery);
