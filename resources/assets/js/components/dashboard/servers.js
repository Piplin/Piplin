(function ($) {
    var SUCCESSFUL = 0;
    var UNTESTED   = 1;
    var FAILED     = 2;
    var TESTING    = 3;

    $('#server_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('server-id'));
            });

            $.ajax({
                url: '/servers/reorder',
                method: 'POST',
                data: {
                    servers: ids
                }
            });
        }
    });

    $('#server').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('servers.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('servers.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#server_id').val('');
            $('#server_name').val('');
            $('#server_enabled').prop('checked', true);
            $('#server_address').val('');
            $('#server_port').val('22');
            $('#server_user').val('');
            $('#server_path').val('');
            $('#server_environment_id').val($("#server_environment_id option:selected").val());
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.server-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var server = Fixhub.Servers.get($('#model_id').val());

        server.destroy({
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

    $('#server button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var server_id = $('#server_id').val();

        if (server_id) {
            var server = Fixhub.Servers.get(server_id);
        } else {
            var server = new Fixhub.Server();
        }

        server.save({
            name:           $('#server_name').val(),
            ip_address:     $('#server_address').val(),
            enabled:        $('#server_enabled').is(':checked'),
            port:           $('#server_port').val(),
            user:           $('#server_user').val(),
            path:           $('#server_path').val(),
            environment_id: $('#server_environment_id').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!server_id) {
                    Fixhub.Servers.add(response);
                }
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

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });


    Fixhub.Server = Backbone.Model.extend({
        urlRoot: '/servers'
    });

    var Servers = Backbone.Collection.extend({
        model: Fixhub.Server,
        comparator: function(serverA, serverB) {
            if (serverA.get('name') > serverB.get('name')) {
                return -1; // before
            } else if (serverA.get('name') < serverB.get('name')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    Fixhub.Servers = new Servers();

    Fixhub.ServersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#server_list tbody');

            $('#no_servers').show();
            $('#server_list').hide();

            this.listenTo(Fixhub.Servers, 'add', this.addOne);
            this.listenTo(Fixhub.Servers, 'reset', this.addAll);
            this.listenTo(Fixhub.Servers, 'remove', this.addAll);
            this.listenTo(Fixhub.Servers, 'all', this.render);

            Fixhub.listener.on('server:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var server = Fixhub.Servers.get(parseInt(data.model.id));

                if (server) {
                    if(Fixhub.environment_id == data.model.environment_id) {
                        server.set(data.model);
                    } else {
                        Fixhub.Servers.remove(server);
                    }
                }
            });

            Fixhub.listener.on('server:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                if (parseInt(data.model.environment_id) === parseInt(Fixhub.environment_id)) {
                    Fixhub.Servers.add(data.model);
                }
            });

            Fixhub.listener.on('server:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var server = Fixhub.Servers.get(parseInt(data.model.id));

                if (server) {
                    Fixhub.Servers.remove(server);
                }
            });
        },
        render: function () {
            if (Fixhub.Servers.length) {
                $('#no_servers').hide();
                $('#server_list').show();
            } else {
                $('#no_servers').show();
                $('#server_list').hide();
            }
        },
        addOne: function (server) {

            var view = new Fixhub.ServerView({
                model: server
            });

            this.$list.append(view.render().el);

            if (Fixhub.Servers.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            Fixhub.Servers.each(this.addOne, this);
        }
    });

    Fixhub.ServerView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-test': 'testConnection',
            'click .btn-show': 'showLog',
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#server-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.status_css = 'primary';
            data.icon_css   = 'help';
            data.status     = trans('servers.untested');

            if (parseInt(this.model.get('status')) === SUCCESSFUL) {
                data.status_css = 'success';
                data.icon_css   = 'checkmark-round';
                data.status     = trans('servers.successful');
            } else if (parseInt(this.model.get('status')) === TESTING) {
                data.status_css = 'warning';
                data.icon_css   = 'load-c fixhub-spin';
                data.status     = trans('servers.testing');
            } else if (parseInt(this.model.get('status')) === FAILED) {
                data.status_css = 'danger';
                data.icon_css   = 'close-round';
                data.status     = trans('servers.failed');
            }

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#server_id').val(this.model.id);
            $('#server_name').val(this.model.get('name'));
            $('#server_environment_id')
                .select2(Fixhub.select2_otpions)
                .val(this.model.get('environment_id'))
                .trigger('change');
            $('#server_enabled').prop('checked', (this.model.get('enabled') === true));
            $('#server_address').val(this.model.get('ip_address'));
            $('#server_port').val(this.model.get('port'));
            $('#server_user').val(this.model.get('user'));
            $('#server_path').val(this.model.get('path'));
        },
        showLog: function() {
            var data = this.model.toJSON();

            $('#log pre').html(data.output);
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade server-trash');
        },
        testConnection: function() {
            if (parseInt(this.model.get('status')) === TESTING) {
                return;
            }

            this.model.set({
                status: TESTING
            });

            var that = this;
            $.ajax({
                type: 'GET',
                url: this.model.urlRoot + '/' + this.model.id + '/test'
            }).fail(function (response) {
                that.model.set({
                    status: FAILED
                });
            });

        }
    });
})(jQuery);
