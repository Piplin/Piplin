var app = app || {};

(function ($) {
    var SUCCESSFUL = 0;
    var UNTESTED   = 1;
    var FAILED     = 2;
    var TESTING    = 3;

    $('#link_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('link-id'));
            });

            $.ajax({
                url: '/admin/links/reorder',
                method: 'POST',
                data: {
                    links: ids
                }
            });
        }
    });

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
        } else {
            $('#link_id').val('');
            $('#link_title').val('');
            $('#link_url').val('');
            $('#link_description').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    $('body').delegate('.link-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var link = app.Links.get($('#model_id').val());

        link.destroy({
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

    $('#link button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var link_id = $('#link_id').val();

        if (link_id) {
            var link = app.Links.get(link_id);
        } else {
            var link = new app.Link();
        }

        link.save({
            title:       $('#link_title').val(),
            url:         $('#link_url').val(),
            description: $('#link_description').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!link_id) {
                    app.Links.add(response);
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


    app.Link = Backbone.Model.extend({
        urlRoot: '/admin/links'
    });

    var Links = Backbone.Collection.extend({
        model: app.Link,
        comparator: function(linkA, linkB) {
            if (linkA.get('title') > linkB.get('title')) {
                return -1; // before
            } else if (linkA.get('title') < linkB.get('title')) {
                return 1; // after
            }

            return 0; // equal
        }
    });

    app.Links = new Links();

    app.LinksTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#link_list tbody');

            $('#no_links').show();
            $('#link_list').hide();

            this.listenTo(app.Links, 'add', this.addOne);
            this.listenTo(app.Links, 'reset', this.addAll);
            this.listenTo(app.Links, 'remove', this.addAll);
            this.listenTo(app.Links, 'all', this.render);

            app.listener.on('link:Fixhub\\Bus\\Events\\ModelChangedEvent', function (data) {
                var link = app.Links.get(parseInt(data.model.id));

                if (link) {
                    link.set(data.model);
                }
            });

            app.listener.on('link:Fixhub\\Bus\\Events\\ModelCreatedEvent', function (data) {
                app.Links.add(data.model);
            });

            app.listener.on('link:Fixhub\\Bus\\Events\\ModelTrashedEvent', function (data) {
                var link = app.Links.get(parseInt(data.model.id));

                if (link) {
                    app.Links.remove(link);
                }
            });
        },
        render: function () {
            if (app.Links.length) {
                $('#no_links').hide();
                $('#link_list').show();
            } else {
                $('#no_links').show();
                $('#link_list').hide();
            }
        },
        addOne: function (link) {

            var view = new app.LinkView({
                model: link
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Links.each(this.addOne, this);
        }
    });

    app.LinkView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editLink',
            'click .btn-delete': 'trashLink'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#link-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        editLink: function() {
            $('#link_id').val(this.model.id);
            $('#link_title').val(this.model.get('title'));
            $('#link_url').val(this.model.get('url'));
            $('#link_description').val(this.model.get('description'));

        },
        trashLink: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade link-trash');
        }
    });
})(jQuery);