var app = app || {};

(function ($) {
    $('.command-list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('command-id'));
            });

            $.ajax({
                url: '/commands/reorder',
                method: 'POST',
                data: {
                    commands: ids
                }
            });
        }
    });

    var editor;

    $('#command').on('hidden.bs.modal', function (event) {
        editor.destroy();
    });

    $('#command').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('commands.create');

        editor = ace.edit('command_script');
        editor.getSession().setMode('ace/mode/sh');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('commands.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#command_id').val('');
            $('#command_step').val(button.data('step'));
            $('#command_name').val('');
            editor.setValue('');
            editor.gotoLine(1);
            $('#command_user').val('');
            $('#command_optional').prop('checked', false);
            $('#command_default_on').prop('checked', false);
            $('#command_default_on_row').addClass('hide');

            $('.command-server').prop('checked', true);
            $('.command-environment').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.command-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var command = app.Commands.get($('#model_id').val());

        command.destroy({
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

    $('#command_optional').on('change', function (event) {
        $('#command_default_on_row').addClass('hide');
        if ($(this).is(':checked') === true) {
            $('#command_default_on_row').removeClass('hide');
        }
    });

    $('#command button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find(':input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var command_id = $('#command_id').val();

        if (command_id) {
            var command = app.Commands.get(command_id);
        } else {
            var command = new app.Command();
        }

        var server_ids = [];

        $('.command-server:checked').each(function() {
            server_ids.push($(this).val());
        });

        var environment_ids = [];

        $('.command-environment:checked').each(function() {
            environment_ids.push($(this).val());
        });

        command.save({
            name:            $('#command_name').val(),
            script:          editor.getValue(),
            user:            $('#command_user').val(),
            step:            $('#command_step').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val(),
            environments:    environment_ids,
            optional:        $('#command_optional').is(':checked'),
            default_on:      $('#command_default_on').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find(':input').removeAttr('disabled');

                if (!command_id) {
                    app.Commands.add(response);
                }

                editor.setValue('');
                editor.gotoLine(1);
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
                dialog.find(':input').removeAttr('disabled');
            }
        });
    });

    app.Command = Backbone.Model.extend({
        urlRoot: '/commands',
        defaults: function() {
            return {
                order: app.Commands.nextOrder()
            };
        },
        isAfter: function() {
            return (parseInt(this.get('step')) % 3 === 0);
        }
    });

    var Commands = Backbone.Collection.extend({
        model: app.Command,
        comparator: 'order',
        nextOrder: function() {
            if (!this.length) {
                return 1;
            }

            return this.last().get('order') + 1;
        }
    });

    app.Commands = new Commands();

    app.CommandsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$beforeList = $('#commands-before .command-list tbody');
            this.$afterList = $('#commands-after .command-list tbody');

            $('.no-commands').show();
            $('.command-list').hide();

            this.listenTo(app.Commands, 'add', this.addOne);
            this.listenTo(app.Commands, 'reset', this.addAll);
            this.listenTo(app.Commands, 'remove', this.addAll);
            this.listenTo(app.Commands, 'all', this.render);

            app.listener.on('command:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var command = app.Commands.get(parseInt(data.model.id));

                if (command) {
                    command.set(data.model);
                }
            });

            app.listener.on('command:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                //if (data.model.targetable_type == app.targetable_type && parseInt(data.model.targetable_id) === parseInt(app.targetable_id)) {

                    // Make sure the command is for this action (clone, install, activate, purge)
                    if (parseInt(data.model.step) + 1 === parseInt(app.command_action) || parseInt(data.model.step) - 1 === parseInt(app.command_action)) {
                        app.Commands.add(data.model);
                    }
                }
            });

            app.listener.on('command:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var command = app.Commands.get(parseInt(data.model.id));

                if (command) {
                    app.Commands.remove(command);
                }
            });
        },
        render: function () {
            var before = app.Commands.find(function(model) {
                return !model.isAfter();
            });

            if (typeof before !== 'undefined') {
                $('#commands-before .no-commands').hide();
                $('#commands-before .command-list').show();
            } else {
                $('#commands-before .no-commands').show();
                $('#commands-before .command-list').hide();
            }

            var after = app.Commands.find(function(model) {
                return model.isAfter();
            });

            if (typeof after !== 'undefined') {
                $('#commands-after .no-commands').hide();
                $('#commands-after .command-list').show();
            } else {
                $('#commands-after .no-commands').show();
                $('#commands-after .command-list').hide();
            }
        },
        addOne: function (command) {
            var view = new app.CommandView({
                model: command
            });

            if (command.isAfter()) {
                this.$afterList.append(view.render().el);
                if ($('tr', this.$afterList).length < 2) {
                    $('.drag-handle', this.$afterList).hide();
                } else {
                    $('.drag-handle', this.$afterList).show();
                }
            } else {
                this.$beforeList.append(view.render().el);
                if ($('tr', this.$beforeList).length < 2) {
                    $('.drag-handle', this.$beforeList).hide();
                } else {
                    $('.drag-handle', this.$beforeList).show();
                }
            }
        },
        addAll: function () {
            this.$beforeList.html('');
            this.$afterList.html('');
            app.Commands.each(this.addOne, this);
        }
    });

    app.CommandView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#command-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#command_id').val(this.model.id);
            $('#command_step').val(this.model.get('step'));
            $('#command_name').val(this.model.get('name'));
            $('#command_script').text(this.model.get('script'));
            $('#command_user').val(this.model.get('user'));
            $('#command_optional').prop('checked', (this.model.get('optional') === true));
            $('#command_default_on').prop('checked', (this.model.get('default_on') === true));

            $('#command_default_on_row').addClass('hide');
            if (this.model.get('optional') === true) {
                $('#command_default_on_row').removeClass('hide');
            }

            $('.command-environment').prop('checked', false);
            $(this.model.get('environments')).each(function (index, environment) {
                $('#command_environment_' + environment.id).prop('checked', true);
            });
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade command-trash');
        }
    });
})(jQuery);

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

var app = app || {};

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
            $('#server_deploy_code').prop('checked', true);
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

        var server = app.Servers.get($('#model_id').val());

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
            var server = app.Servers.get(server_id);
        } else {
            var server = new app.Server();
        }

        server.save({
            name:           $('#server_name').val(),
            ip_address:     $('#server_address').val(),
            enabled:        $('#server_enabled').is(':checked'),
            port:           $('#server_port').val(),
            user:           $('#server_user').val(),
            path:           $('#server_path').val(),
            deploy_code:    $('#server_deploy_code').is(':checked'),
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
                    app.Servers.add(response);
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


    app.Server = Backbone.Model.extend({
        urlRoot: '/servers'
    });

    var Servers = Backbone.Collection.extend({
        model: app.Server,
        comparator: function(serverA, serverB) {
            if (serverA.get('name') > serverB.get('name')) {
                return -1; // before
            } else if (serverA.get('name') < serverB.get('name')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    app.Servers = new Servers();

    app.ServersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#server_list tbody');

            $('#no_servers').show();
            $('#server_list').hide();

            this.listenTo(app.Servers, 'add', this.addOne);
            this.listenTo(app.Servers, 'reset', this.addAll);
            this.listenTo(app.Servers, 'remove', this.addAll);
            this.listenTo(app.Servers, 'all', this.render);

            app.listener.on('server:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var server = app.Servers.get(parseInt(data.model.id));

                if (server) {
                    if(app.environment_id == data.model.environment_id) {
                        server.set(data.model);
                    } else {
                        app.Servers.remove(server);
                    }
                }
            });

            app.listener.on('server:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                if (parseInt(data.model.environment_id) === parseInt(app.environment_id)) {
                    app.Servers.add(data.model);
                }
            });

            app.listener.on('server:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var server = app.Servers.get(parseInt(data.model.id));

                if (server) {
                    app.Servers.remove(server);
                }
            });
        },
        render: function () {
            if (app.Servers.length) {
                $('#no_servers').hide();
                $('#server_list').show();
            } else {
                $('#no_servers').show();
                $('#server_list').hide();
            }
        },
        addOne: function (server) {

            var view = new app.ServerView({
                model: server
            });

            this.$list.append(view.render().el);

            if (app.Servers.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            app.Servers.each(this.addOne, this);
        }
    });

    app.ServerView = Backbone.View.extend({
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
            $('#server_enabled').prop('checked', (this.model.get('enabled') === true));
            $('#server_address').val(this.model.get('ip_address'));
            $('#server_port').val(this.model.get('port'));
            $('#server_user').val(this.model.get('user'));
            $('#server_path').val(this.model.get('path'));

            $('#server_deploy_code').prop('checked', (this.model.get('deploy_code') === true));
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

var app = app || {};

(function ($) {
    $('#hook').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.callout-warning', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            $('.btn-danger', modal).show();
        } else {
            $('#hook_id').val('');
            $('#hook_name').val('');
            $('#hook_type').val('');
            $('#hook :input[id^=hook_config]').val('');
            $('#hook .hook-config input[type=checkbox]').prop('checked', true);
            $('#hook .hook-enabled input[type=checkbox]').prop('checked', true);
            $('#hook .modal-footer').hide();
            $('.hook-config').hide();
            $('.hook-enabled').hide();
            $('#hook-type').show();
            modal.find('.modal-title span').text(trans('hooks.create'));
        }
    });

    $('#hook #hook-type a.btn-app').on('click', function(event) {
        var button = $(event.currentTarget);
        var modal = $('#hook');

        if (button.attr('disabled')) {
            $('.callout-warning', modal).show();
            return;
        }

        $('.callout-warning', modal).hide();

        var type = button.data('type');
        setTitleWithIcon(type, 'create');
    });

    function setTitleWithIcon(type, action) {
        $('#hook .modal-title span').text(trans('hooks.' + action + '_' + type));

        var element = $('#hook .modal-title i').removeClass().addClass('ion');
        var icon = 'cogs';

        if (type === 'slack') {
            icon = 'pound';
        } else if (type === 'mail') {
            icon = 'email';
        } else if (type === 'custom') {
            icon = 'compose';
        }

        element.addClass('ion-' + icon);

        $('#hook .modal-footer').show();
        $('.hook-config').hide();
        $('#hook-type').hide();
        $('#hook-name').show();
        $('#hook-triggers').show();
        $('.hook-enabled').show();
        $('#hook-config-' + type).show();
        $('#hook_type').val(type);
    }

    //$('#hook button.btn-delete').on('click', function (event) {
    $('body').delegate('.hook-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var hook = app.Hooks.get($('#model_id').val());

        hook.destroy({
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

    $('#hook button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var hook_id = $('#hook_id').val();

        if (hook_id) {
            var hook = app.Hooks.get(hook_id);
        } else {
            var hook = new app.Hook();
        }

        var data = {
          config:                     null,
          name:                       $('#hook_name').val(),
          type:                       $('#hook_type').val(),
          project_id:                 parseInt($('input[name="project_id"]').val()),
          enabled:                    $('#hook_enabled').is(':checked'),
          on_deployment_success:      $('#hook_on_deployment_success').is(':checked'),
          on_deployment_failure:      $('#hook_on_deployment_failure').is(':checked')
        };

        $('#hook #hook-config-' + data.type + ' :input[id^=hook_config]').each(function(key, field) {
            var name = $(field).attr('name');

            data[name] = $(field).val();
        });

        hook.save(data, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!hook_id) {
                    app.Hooks.add(response);
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
                        var parent = element.parents('div.form-group');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('ion-refresh ion-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    app.Hook = Backbone.Model.extend({
        urlRoot: '/hooks'
    });

    var Hooks = Backbone.Collection.extend({
        model: app.Hook
    });

    app.Hooks = new Hooks();

    app.HooksTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#hook_list tbody');

            $('#no_hooks').show();
            $('#hook_list').hide();

            this.listenTo(app.Hooks, 'add', this.addOne);
            this.listenTo(app.Hooks, 'reset', this.addAll);
            this.listenTo(app.Hooks, 'remove', this.addAll);
            this.listenTo(app.Hooks, 'all', this.render);


            app.listener.on('hook:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var hook = app.Hooks.get(parseInt(data.model.id));

                if (hook) {
                    hook.set(data.model);
                }
            });

            app.listener.on('hook:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                if (parseInt(data.model.project_id) === parseInt(app.project_id)) {
                    app.Hooks.add(data.model);
                }
            });

            app.listener.on('hook:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var hook = app.Hooks.get(parseInt(data.model.id));

                if (hook) {
                    app.Hooks.remove(hook);
                }
            });
        },
        render: function () {
            if (app.Hooks.length) {
                $('#no_hooks').hide();
                $('#hook_list').show();
            } else {
                $('#no_hooks').show();
                $('#hook_list').hide();
            }
        },
        addOne: function (hook) {
            var view = new app.HookView({
                model: hook
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Hooks.each(this.addOne, this);
        }
    });

    app.HookView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#hook-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.icon = 'compose';
            data.label = trans('hooks.custom');

            if (this.model.get('type') !== 'custom') {
                data.label = trans('hooks.' + this.model.get('type'));
            }

            if (this.model.get('type') === 'slack') {
                data.icon = 'pound';
            } else if (this.model.get('type') === 'mail') {
                data.icon = 'email';
            }

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            var type = this.model.get('type');

            $.each(this.model.get('config'), function(field, value) {
                $('#hook-config-' + type + ' #hook_config_' + field).val(value);
            });

            $('#hook_id').val(this.model.id);
            $('#hook_name').val(this.model.get('name'));
            $('#hook_type').val(type);
            $('#hook_enabled').prop('checked', (this.model.get('enabled') === true));
            $('#hook_on_deployment_success').prop('checked', (this.model.get('on_deployment_success') === true));
            $('#hook_on_deployment_failure').prop('checked', (this.model.get('on_deployment_failure') === true));

            setTitleWithIcon(this.model.get('type'), 'edit');
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade hook-trash');
        }
    });
})(jQuery);
var app = app || {};

(function ($) {
    var COMPLETED = 0;
    var PENDING   = 1;
    var RUNNING   = 2;
    var FAILED    = 3;
    var CANCELLED = 4;

    $('#redeploy').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);

        var deployment = button.data('deployment-id');

        var tmp = button.data('optional-commands') + '';
        var commands = tmp.split(',');

        if (tmp.length > 0) {
            commands = $.map(commands, function(value) {
                return parseInt(value, 10);
            });
        } else {
            commands = [];
        }

        var modal = $(this);

        $('form', modal).prop('action', '/deployment/' + deployment + '/rollback');

        $('input:checkbox', modal).each(function (index, element) {
            var input = $(element);

            input.prop('checked', false);
            if ($.inArray(parseInt(input.val(), 10), commands) != -1) {
                input.prop('checked', true);
            }
        });
    });

    $('.btn-cancel').on('click', function (event) {
        var button = $(event.currentTarget);
        var deployment = button.data('deployment-id');

        $('form#abort_' + deployment).trigger('submit');
    });

    var fetchingLog = false;
    $('#log').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var log_id = button.attr('id').replace('log_', '');

        var step = $('h3 span', button.parents('.box')).text();
        var modal = $(this);
        var log = $('pre', modal);
        var loader = $('#loading', modal);

        log.hide();
        loader.show();

        $('#action', modal).text(step);
        log.text('');

        fetchingLog = true;

        $.ajax({
            type: 'GET',
            url: '/log/' + log_id
        }).done(function (data) {
            var output = parseOutput(data.output ? data.output : '');

            log.html(output);

            log.show();
            loader.hide();

            app.listener.on('serverlog-' + log_id + ':Fixhub\\Bus\\Events\\ServerOutputChangedEvent', function (data) {
                if (data.log_id === parseInt(log_id)) {
                  fetchLog(log, data.log_id);
                }
            });
        }).always(function() {
            fetchingLog = false;
        });
    });

    $('#log').on('hide.bs.modal', function () {
        fetchingLog = false;
    });

    function fetchLog(element, log_id) {
        if (fetchingLog) {
            return;
        }

        fetchingLog = true;

        $.ajax({
            type: 'GET',
            url: '/log/' + log_id
        }).done(function (data) {
            var output = parseOutput(data.output ? data.output : '');
            var atBottom = false;

            if (element.scrollTop() + element.innerHeight() >= element.get(0).scrollHeight) {
                atBottom = true;
            }

            element.html(output);

            if (atBottom) {
                element.scrollTop(element.get(0).scrollHeight);
            }
        }).always(function() {
            fetchingLog = false;
        });
    }


    function parseOutput(output) {
        return output.replace(/<\/error>/g, '</span>')
            .replace(/<\/info>/g, '</span>')
            .replace(/<error>/g, '<span class="text-red">')
            .replace(/<info>/g, '<span class="text-default">');
    }

    app.ServerLog = Backbone.Model.extend({
        urlRoot: '/status'
    });

    var Deployment = Backbone.Collection.extend({
        model: app.ServerLog
    });

    app.Deployment = new Deployment();

    app.DeploymentView = Backbone.View.extend({
        el: '#app',
        $containers: [],
        events: {

        },
        initialize: function() {
            var that = this;
            $('.deploy-step tbody').each(function(index, element) {
                that.$containers.push({
                    step: parseInt($(element).attr('id').replace('step_', '')),
                    element: element
                })
            });

            this.listenTo(app.Deployment, 'add', this.addOne);
            this.listenTo(app.Deployment, 'reset', this.addAll);
            this.listenTo(app.Deployment, 'remove', this.addAll);
            this.listenTo(app.Deployment, 'all', this.render);

            app.listener.on('serverlog:Fixhub\\Bus\\Events\\ServerLogChangedEvent', function (data) {
                var deployment = app.Deployment.get(data.log_id);

                if (deployment) {
                    deployment.set({
                        status: data.status,
                        output: data.output,
                        runtime: data.runtime,
                        started_at: data.started_at ? data.started_at : false,
                        finished_at: data.finished_at ? data.finished_at : false
                    });

                    // FIXME: If cancelled update all other deployments straight away
                    // FIXME: If completed fake making the next model "running" so it looks responsive
                }
            });

            app.listener.on('deployment:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                if (parseInt(data.model.project_id) === parseInt(app.project_id)) {
                    if (data.model.repo_failure) {
                        $('#repository_error').show();
                    }
                }
            });

        },
        addOne: function (step) {
            var view = new app.LogView({
                model: step
            });

            var found = _.find(this.$containers, function(element) {
                return parseInt(element.step) === parseInt(step.get('deploy_step_id'));
            });

            $(found.element).append(view.render().el);

        },
        addAll: function () {
            $(this.$containers).each(function (index, element) {
                element.html('');
            });

            app.Commands.each(this.addOne, this);
        }
    });

    app.LogView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            //'click .btn-log': 'showLog',
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#log-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.status_css = 'info';
            data.icon_css = 'clock';
            data.status = trans('deployments.pending');

            if (parseInt(this.model.get('status')) === COMPLETED) {
                data.status_css = 'success';
                data.icon_css = 'checkmark-round';
                data.status = trans('deployments.completed');
            } else if (parseInt(this.model.get('status')) === RUNNING) {
                data.status_css = 'warning';
                data.icon_css = 'load-c fixhub-spin';
                data.status = trans('deployments.running');
            } else if (parseInt(this.model.get('status')) === FAILED || parseInt(this.model.get('status')) === CANCELLED) {
                data.status_css = 'danger';
                data.icon_css = 'close-round';

                data.status = trans('deployments.failed');
                if (parseInt(this.model.get('status')) === CANCELLED) {
                    data.status = trans('deployments.cancelled');
                }
            }

            data.formatted_start_time = data.started_at ? moment(data.started_at).format('HH:mm:ss') : false;
            data.formatted_end_time   = data.finished_at ? moment(data.finished_at).format('HH:mm:ss') : false;

            this.$el.html(this.template(data));

            return this;
        }
    });
})(jQuery);
var app = app || {};

(function ($) {
    // Stop the uploader causing errors on pages it shouldn't be used
    if ($('#upload').length === 0) {
        return;
    }

    $('#skin').on('change', function() {
        $('body').removeClass();
        $("body").addClass('skin-' + $(this).find(':selected').val());
    });

    $('#two-factor-auth').on('change', function () {

        var container = $('.auth-code');

        if ($(this).is(':checked')) {
            container.removeClass('hide');
        } else {
            container.addClass('hide');
        }
    });

    $('#request-change-email').on('click', function() {
        var box = $(this).parents('.box');
        box.children('.overlay').removeClass('hide');
        $.post('/profile/email', function(res) {
            if (res == 'success') {
                box.children('.overlay').addClass('hide');
                box.find('.help-block').removeClass('hide');
            }
        });
    });

    var cropperData = {};
    $('.avatar>img').cropper({
        aspectRatio: 1 / 1,
        preview: '.avatar-preview',
        crop: function(data) {
            cropperData.dataX = Math.round(data.x);
            cropperData.dataY = Math.round(data.y);
            cropperData.dataHeight = Math.round(data.height);
            cropperData.dataWidth = Math.round(data.width);
            cropperData.dataRotate = Math.round(data.rotate);
        },
        built: function() {
            $('#upload-overlay').addClass('hide');
        }
    });

    var uploader = new Uploader({
        trigger: '#upload',
        name: 'file',
        action: '/profile/upload',
        accept: 'image/*',
        data: {
            '_token': $('meta[name="token"]').attr('content')
        },
        multiple: false,
        change: function(){
            $('#upload-overlay').removeClass('hide');
            this.submit();
        },
        error: function(file) {
            if (file.responseJSON.file) {
                alert(file.responseJSON.file.join(''));
            } else if (file.responseJSON.error) {
                alert(file.responseJSON.error.message);
            }

            $('#upload-overlay').addClass('hide');
        },
        success: function(response) {
            if( response.message === 'success') {
                $('.avatar>img').cropper('replace', response.image);
                cropperData.path = response.path;

                $('.current-avatar-preview').addClass('hide');
                $('.avatar-preview').removeClass('hide');
                $('#save-avatar').removeClass('hide');
            }
        }
    });

    $('#save-avatar').on('click', function(){
        $('#upload-overlay').removeClass('hide');
        $('.avatar-message .alert').addClass('hide');
        $.post('/profile/avatar', cropperData).success(function(resp) {
            $('#upload-overlay').addClass('hide');
            if (resp.image) {
                $('.avatar-message .alert.alert-success').removeClass('hide');
                $('#use-gravatar').removeClass('hide');
            } else {
                $('.avatar-message .alert.alert-danger').removeClass('hide');
            }
        });
    });

    $('#use-gravatar').on('click', function () {

        $('#upload-overlay').removeClass('hide');
        $('.avatar-message .alert').addClass('hide');

        $.post('/profile/gravatar').success(function(resp) {

            $('#upload-overlay').addClass('hide');

            // if (resp.image) {
                $('.avatar-message .alert.alert-success').removeClass('hide');
                $('.avatar-preview').addClass('hide');
                $('.current-avatar-preview').removeClass('hide');
                $('.current-avatar-preview').attr('src', resp.image);
                $('#use-gravatar').addClass('hide');
                $('#avatar-save-buttons button').addClass('hide');
            // } else {
            //     $('.avatar-preview').addClass('hide');
            //     $('#use-gravatar').removeClass('hide');
            // }
        });
    });
})(jQuery);

var app = app || {};

(function ($) {
    $('.command-list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('command-id'));
            });

            $.ajax({
                url: '/commands/reorder',
                method: 'POST',
                data: {
                    commands: ids
                }
            });
        }
    });

    var editor;

    $('#command').on('hidden.bs.modal', function (event) {
        editor.destroy();
    });

    $('#command').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('commands.create');

        editor = ace.edit('command_script');
        editor.getSession().setMode('ace/mode/sh');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('commands.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#command_id').val('');
            $('#command_step').val(button.data('step'));
            $('#command_name').val('');
            editor.setValue('');
            editor.gotoLine(1);
            $('#command_user').val('');
            $('#command_optional').prop('checked', false);
            $('#command_default_on').prop('checked', false);
            $('#command_default_on_row').addClass('hide');

            $('.command-server').prop('checked', true);
            $('.command-environment').prop('checked', true);
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.command-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var command = app.Commands.get($('#model_id').val());

        command.destroy({
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

    $('#command_optional').on('change', function (event) {
        $('#command_default_on_row').addClass('hide');
        if ($(this).is(':checked') === true) {
            $('#command_default_on_row').removeClass('hide');
        }
    });

    $('#command button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find(':input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var command_id = $('#command_id').val();

        if (command_id) {
            var command = app.Commands.get(command_id);
        } else {
            var command = new app.Command();
        }

        var server_ids = [];

        $('.command-server:checked').each(function() {
            server_ids.push($(this).val());
        });

        var environment_ids = [];

        $('.command-environment:checked').each(function() {
            environment_ids.push($(this).val());
        });

        command.save({
            name:            $('#command_name').val(),
            script:          editor.getValue(),
            user:            $('#command_user').val(),
            step:            $('#command_step').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val(),
            environments:    environment_ids,
            optional:        $('#command_optional').is(':checked'),
            default_on:      $('#command_default_on').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find(':input').removeAttr('disabled');

                if (!command_id) {
                    app.Commands.add(response);
                }

                editor.setValue('');
                editor.gotoLine(1);
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
                dialog.find(':input').removeAttr('disabled');
            }
        });
    });

    app.Command = Backbone.Model.extend({
        urlRoot: '/commands',
        defaults: function() {
            return {
                order: app.Commands.nextOrder()
            };
        },
        isAfter: function() {
            return (parseInt(this.get('step')) % 3 === 0);
        }
    });

    var Commands = Backbone.Collection.extend({
        model: app.Command,
        comparator: 'order',
        nextOrder: function() {
            if (!this.length) {
                return 1;
            }

            return this.last().get('order') + 1;
        }
    });

    app.Commands = new Commands();

    app.CommandsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$beforeList = $('#commands-before .command-list tbody');
            this.$afterList = $('#commands-after .command-list tbody');

            $('.no-commands').show();
            $('.command-list').hide();

            this.listenTo(app.Commands, 'add', this.addOne);
            this.listenTo(app.Commands, 'reset', this.addAll);
            this.listenTo(app.Commands, 'remove', this.addAll);
            this.listenTo(app.Commands, 'all', this.render);

            app.listener.on('command:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var command = app.Commands.get(parseInt(data.model.id));

                if (command) {
                    command.set(data.model);
                }
            });

            app.listener.on('command:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                //if (data.model.targetable_type == app.targetable_type && parseInt(data.model.targetable_id) === parseInt(app.targetable_id)) {

                    // Make sure the command is for this action (clone, install, activate, purge)
                    if (parseInt(data.model.step) + 1 === parseInt(app.command_action) || parseInt(data.model.step) - 1 === parseInt(app.command_action)) {
                        app.Commands.add(data.model);
                    }
                }
            });

            app.listener.on('command:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var command = app.Commands.get(parseInt(data.model.id));

                if (command) {
                    app.Commands.remove(command);
                }
            });
        },
        render: function () {
            var before = app.Commands.find(function(model) {
                return !model.isAfter();
            });

            if (typeof before !== 'undefined') {
                $('#commands-before .no-commands').hide();
                $('#commands-before .command-list').show();
            } else {
                $('#commands-before .no-commands').show();
                $('#commands-before .command-list').hide();
            }

            var after = app.Commands.find(function(model) {
                return model.isAfter();
            });

            if (typeof after !== 'undefined') {
                $('#commands-after .no-commands').hide();
                $('#commands-after .command-list').show();
            } else {
                $('#commands-after .no-commands').show();
                $('#commands-after .command-list').hide();
            }
        },
        addOne: function (command) {
            var view = new app.CommandView({
                model: command
            });

            if (command.isAfter()) {
                this.$afterList.append(view.render().el);
                if ($('tr', this.$afterList).length < 2) {
                    $('.drag-handle', this.$afterList).hide();
                } else {
                    $('.drag-handle', this.$afterList).show();
                }
            } else {
                this.$beforeList.append(view.render().el);
                if ($('tr', this.$beforeList).length < 2) {
                    $('.drag-handle', this.$beforeList).hide();
                } else {
                    $('.drag-handle', this.$beforeList).show();
                }
            }
        },
        addAll: function () {
            this.$beforeList.html('');
            this.$afterList.html('');
            app.Commands.each(this.addOne, this);
        }
    });

    app.CommandView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#command-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#command_id').val(this.model.id);
            $('#command_step').val(this.model.get('step'));
            $('#command_name').val(this.model.get('name'));
            $('#command_script').text(this.model.get('script'));
            $('#command_user').val(this.model.get('user'));
            $('#command_optional').prop('checked', (this.model.get('optional') === true));
            $('#command_default_on').prop('checked', (this.model.get('default_on') === true));

            $('#command_default_on_row').addClass('hide');
            if (this.model.get('optional') === true) {
                $('#command_default_on_row').removeClass('hide');
            }

            $('.command-environment').prop('checked', false);
            $(this.model.get('environments')).each(function (index, environment) {
                $('#command_environment_' + environment.id).prop('checked', true);
            });
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade command-trash');
        }
    });
})(jQuery);

var app = app || {};

(function ($) {

    var editor;
    var previewfile;

    $('#configfile, #view-configfile').on('hidden.bs.modal', function (event) {
        editor.destroy();
    });

    $('#view-configfile').on('show.bs.modal', function (event) {
        editor = ace.edit('preview-content');
        editor.setReadOnly(true);
        editor.getSession().setUseWrapMode(true);

        var extension = previewfile.substr(previewfile.lastIndexOf('.') + 1).toLowerCase();

        if (extension === 'php' || extension === 'ini') {
            editor.getSession().setMode('ace/mode/' + extension);
        } else if (extension === 'yml') {
            editor.getSession().setMode('ace/mode/yaml');
        }
    });

    $('#configfile').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('configFiles.create');

        editor = ace.edit('content');

        var filename = $('#path').val();
        var extension = filename.substr(filename.lastIndexOf('.') + 1).toLowerCase();

        if (extension === 'php' || extension === 'ini') {
            editor.getSession().setMode('ace/mode/' + extension);
        } else if (extension === 'yml') {
            editor.getSession().setMode('ace/mode/yaml');
        }

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('configFiles.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#file_id').val('');
            $('#name').val('');
            $('#path').val('');
            editor.setValue('');
            editor.gotoLine(1);
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.configfile-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file = app.ConfigFiles.get($('#model_id').val());

        file.destroy({
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

    $('#configfile button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var config_file_id = $('#config_file_id').val();

        if (config_file_id) {
            var file = app.ConfigFiles.get(config_file_id);
        } else {
            var file = new app.ConfigFile();
        }

        file.save({
            name:       $('#name').val(),
            path:       $('#path').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val(),
            content:    editor.getValue()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!config_file_id) {
                    app.ConfigFiles.add(response);
                }

                editor.setValue('');
                editor.gotoLine(1);
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

    app.ConfigFile = Backbone.Model.extend({
        urlRoot: '/config-file'
    });

    var ConfigFiles = Backbone.Collection.extend({
        model: app.ConfigFile
    });

    app.ConfigFiles = new ConfigFiles();

    app.ConfigFilesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#configfile_list tbody');

            $('#no_configfiles').show();
            $('#configfile_list').hide();

            this.listenTo(app.ConfigFiles, 'add', this.addOne);
            this.listenTo(app.ConfigFiles, 'reset', this.addAll);
            this.listenTo(app.ConfigFiles, 'remove', this.addAll);
            this.listenTo(app.ConfigFiles, 'all', this.render);

            app.listener.on('configfile:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var file = app.ConfigFiles.get(parseInt(data.model.id));

                if (file) {
                    file.set(data.model);
                }
            });

            app.listener.on('configfile:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    app.ConfigFiles.add(data.model);
                }
            });

            app.listener.on('configfile:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var file = app.ConfigFiles.get(parseInt(data.model.id));

                if (file) {
                    app.ConfigFiles.remove(file);
                }
            });
        },
        render: function () {
            if (app.ConfigFiles.length) {
                $('#no_configfiles').hide();
                $('#configfile_list').show();
            } else {
                $('#no_configfiles').show();
                $('#configfile_list').hide();
            }
        },
        addOne: function (file) {

            var view = new app.ConfigFileView({
                model: file
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.ConfigFiles.each(this.addOne, this);
        }
    });

    app.ConfigFileView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash',
            'click .btn-view': 'view'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#configfiles-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        view: function() {
            previewfile = this.model.get('path');
            $('#preview-content').text(this.model.get('content'));
        },
        edit: function() {
            $('#config_file_id').val(this.model.id);
            $('#name').val(this.model.get('name'));
            $('#path').val(this.model.get('path'));
            $('#content').text(this.model.get('content'));
        },
        trash: function () {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade configfile-trash');
        }
    });

})(jQuery);

var app = app || {};

(function ($) {
    $('#environment_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('environment-id'));
            });

            $.ajax({
                url: '/environments/reorder',
                method: 'POST',
                data: {
                    environments: ids
                }
            });
        }
    });

    $('#environment').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('environments.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();
        $('#add-environment-command', modal).hide();

        if (button.hasClass('btn-edit')) {
            title = trans('environments.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#environment_id').val('');
            $('#environment_name').val('');
            $('#environment_description').val('');
            $('#environment_default_on').prop('checked', true);
            $('#add-environment-command', modal).show();
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.environment-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var environment = app.Environments.get($('#model_id').val());

        environment.destroy({
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

    $('#environment button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var environment_id = $('#environment_id').val();

        if (environment_id) {
            var environment = app.Environments.get(environment_id);
        } else {
            var environment = new app.Environment();
        }

        environment.save({
            name:            $('#environment_name').val(),
            description:     $('#environment_description').val(),
            default_on:      $('#environment_default_on').is(':checked'),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val(),
            add_commands:    $('#environment_commands').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!environment_id) {
                    app.Environments.add(response);
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

    app.Environment = Backbone.Model.extend({
        urlRoot: '/environments',
        initialize: function() {

        }
    });

    var Environments = Backbone.Collection.extend({
        model: app.Environment
    });

    app.Environments = new Environments();

    app.EnvironmentsTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#environment_list tbody');

            $('#no_environments').show();
            $('#environment_list').hide();

            this.listenTo(app.Environments, 'add', this.addOne);
            this.listenTo(app.Environments, 'reset', this.addAll);
            this.listenTo(app.Environments, 'remove', this.addAll);
            this.listenTo(app.Environments, 'all', this.render);

            app.listener.on('environment:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                $('#environment_' + data.model.id).html(data.model.name);

                var environment = app.Environments.get(parseInt(data.model.id));

                if (environment) {
                    environment.set(data.model);
                }
            });

            app.listener.on('environment:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    app.Environments.add(data.model);
                }
            });

            app.listener.on('environment:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var environment = app.Environments.get(parseInt(data.model.id));

                if (environment) {
                    app.Environments.remove(environment);
                }
            });
        },
        render: function () {
            if (app.Environments.length) {
                $('#no_environments').hide();
                $('#environment_list').show();
            } else {
                $('#no_environments').show();
                $('#environment_list').hide();
            }
        },
        addOne: function (environment) {

            var view = new app.EnvironmentView({
                model: environment
            });

            this.$list.append(view.render().el);

            if (app.Environments.length < 2) {
                $('.drag-handle', this.$list).hide();
            } else {
                $('.drag-handle', this.$list).show();
            }
        },
        addAll: function () {
            this.$list.html('');
            app.Environments.each(this.addOne, this);
        }
    });

    app.EnvironmentView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#environment-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#environment_id').val(this.model.id);
            $('#environment_name').val(this.model.get('name'));
            $('#environment_description').val(this.model.get('description'));
            $('#environment_default_on').prop('checked', (this.model.get('default_on') === true));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade environment-trash');
        }
    });
})(jQuery);

var app = app || {};

(function ($) {
    $('#sharedfile').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('sharedFiles.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('sharedFiles.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#sharedfile_id').val('');
            $('#name').val('');
            $('#file').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.sharedfile-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file = app.SharedFiles.get($('#model_id').val());

        file.destroy({
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

    $('#sharedfile button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var file_id = $('#sharedfile_id').val();

        if (file_id) {
            var file = app.SharedFiles.get(file_id);
        } else {
            var file = new app.SharedFile();
        }

        file.save({
            name:            $('#name').val(),
            file:            $('#file').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!file_id) {
                    app.SharedFiles.add(response);
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

    app.SharedFile = Backbone.Model.extend({
        urlRoot: '/shared-files'
    });

    var SharedFiles = Backbone.Collection.extend({
        model: app.SharedFile
    });

    app.SharedFiles = new SharedFiles();

    app.SharedFilesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#sharedfile_list tbody');

            $('#no_sharedfiles').show();
            $('#sharedfile_list').hide();

            this.listenTo(app.SharedFiles, 'add', this.addOne);
            this.listenTo(app.SharedFiles, 'reset', this.addAll);
            this.listenTo(app.SharedFiles, 'remove', this.addAll);
            this.listenTo(app.SharedFiles, 'all', this.render);

            app.listener.on('sharedfile:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var share = app.SharedFiles.get(parseInt(data.model.id));

                if (share) {
                    share.set(data.model);
                }
            });

            app.listener.on('sharedfile:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    app.SharedFiles.add(data.model);
                }
            });

            app.listener.on('sharedfile:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var share = app.SharedFiles.get(parseInt(data.model.id));

                if (share) {
                    app.SharedFiles.remove(share);
                }
            });
        },
        render: function () {
            if (app.SharedFiles.length) {
                $('#no_sharedfiles').hide();
                $('#sharedfile_list').show();
            } else {
                $('#no_sharedfiles').show();
                $('#sharedfile_list').hide();
            }
        },
        addOne: function (file) {

            var view = new app.SharedFileView({
                model: file
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.SharedFiles.each(this.addOne, this);
        }
    });

    app.SharedFileView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#sharedfile-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#sharedfile_id').val(this.model.id);
            $('#name').val(this.model.get('name'));
            $('#file').val(this.model.get('file'));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade sharedfile-trash');
        }
    });

})(jQuery);

var app = app || {};

(function ($) {
    $('#variable').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('variables.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('variables.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#variable_id').val('');
            $('#variable_name').val('');
            $('#variable_value').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.variable-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var variable = app.Variables.get($('#model_id').val());

        variable.destroy({
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

    $('#variable button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var variable_id = $('#variable_id').val();

        if (variable_id) {
            var variable = app.Variables.get(variable_id);
        } else {
            var variable = new app.Variable();
        }

        variable.save({
            name:            $('#variable_name').val(),
            value:           $('#variable_value').val(),
            targetable_type: $('input[name="targetable_type"]').val(),
            targetable_id:   $('input[name="targetable_id"]').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!variable_id) {
                    app.Variables.add(response);
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

    app.Variable = Backbone.Model.extend({
        urlRoot: '/variables',
        initialize: function() {

        }
    });

    var Variables = Backbone.Collection.extend({
        model: app.Variable
    });

    app.Variables = new Variables();

    app.VariablesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#variable_list tbody');

            $('#no_variables').show();
            $('#variable_list').hide();

            this.listenTo(app.Variables, 'add', this.addOne);
            this.listenTo(app.Variables, 'reset', this.addAll);
            this.listenTo(app.Variables, 'remove', this.addAll);
            this.listenTo(app.Variables, 'all', this.render);

            app.listener.on('variable:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                $('#variable_' + data.model.id).html(data.model.name);

                var variable = app.Variables.get(parseInt(data.model.id));

                if (variable) {
                    variable.set(data.model);
                }
            });

            app.listener.on('variable:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                var targetable_type = $('input[name="targetable_type"]').val();
                var targetable_id = $('input[name="targetable_id"]').val();
                if (targetable_type == data.model.targetable_type && parseInt(data.model.targetable_id) === parseInt(targetable_id)) {
                    app.Variables.add(data.model);
                }
            });

            app.listener.on('variable:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var variable = app.Variables.get(parseInt(data.model.id));

                if (variable) {
                    app.Variables.remove(variable);
                }
            });
        },
        render: function () {
            if (app.Variables.length) {
                $('#no_variables').hide();
                $('#variable_list').show();
            } else {
                $('#no_variables').show();
                $('#variable_list').hide();
            }
        },
        addOne: function (variable) {

            var view = new app.VariableView({
                model: variable
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Variables.each(this.addOne, this);
        }
    });

    app.VariableView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'edit',
            'click .btn-delete': 'trash'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#variable-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        edit: function() {
            $('#variable_id').val(this.model.id);
            $('#variable_name').val(this.model.get('name'));
            $('#variable_value').val(this.model.get('value'));
        },
        trash: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade variable-trash');
        }
    });
})(jQuery);
