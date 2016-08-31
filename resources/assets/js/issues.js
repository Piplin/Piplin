var app = app || {};

(function ($) {

        // FIXME: This seems very wrong
    $('#issue').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = trans('issues.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();

        if (button.hasClass('btn-edit')) {
            title = trans('issues.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#issue_id').val('');
            $('#issue_title').val('');
            $('#issue_content').val('');
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('body').delegate('.issue-trash button.btn-delete','click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var issue = app.Issues.get($('#model_id').val());

        issue.destroy({
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

        // FIXME: This seems very wrong
    $('#issue button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('ion-refresh fixhub-spin');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var issue_id = $('#issue_id').val();

        if (issue_id) {
            var issue = app.Issues.get(issue_id);
        } else {
            var issue = new app.Issue();
        }

        issue.save({
            title:      $('#issue_title').val(),
            content:    $('#issue_content').val(),
            project_id: $('input[name="project_id"]').val()
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('ion-refresh fixhub-spin');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!issue_id) {
                    app.Issues.add(response);
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

    app.Issue = Backbone.Model.extend({
        urlRoot: '/issues'
    });

    var Issues = Backbone.Collection.extend({
        model: app.Issue
    });

    app.Issues = new Issues();

    app.IssuesTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#issue_list tbody');

            $('#no_issues').show();
            $('#issue_list').hide();

            this.listenTo(app.Issues, 'add', this.addOne);
            this.listenTo(app.Issues, 'reset', this.addAll);
            this.listenTo(app.Issues, 'remove', this.addAll);
            this.listenTo(app.Issues, 'all', this.render);

            app.listener.on('issue:Fixhub\\Bus\\Events\\ModelChanged', function (data) {
                var issue = app.Issues.get(parseInt(data.model.id));

                if (issue) {
                    issue.set(data.model);
                }
            });

            app.listener.on('issue:Fixhub\\Bus\\Events\\ModelCreated', function (data) {
                if (parseInt(data.model.project_id) === parseInt(app.project_id)) {
                    app.Issues.add(data.model);
                }
            });

            app.listener.on('issue:Fixhub\\Bus\\Events\\ModelTrashed', function (data) {
                var issue = app.Issues.get(parseInt(data.model.id));

                if (issue) {
                    app.Issues.remove(issue);
                }
            });
        },
        render: function () {
            if (app.Issues.length) {
                $('#no_issues').hide();
                $('#issue_list').show();
            } else {
                $('#no_issues').show();
                $('#issue_list').hide();
            }
        },
        addOne: function (issue) {

            var view = new app.IssueView({
                model: issue
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Issues.each(this.addOne, this);
        }
    });

    app.IssueView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-edit': 'editIssue',
            'click .btn-delete': 'trashIssue'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#issue-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            this.$el.html(this.template(data));

            return this;
        },
        editIssue: function() {
            // FIXME: Sure this is wrong?
            $('#issue_id').val(this.model.id);
            $('#issue_title').val(this.model.get('title'));
            $('#issue_content').val(this.model.get('content'));
        },
        trashIssue: function() {
            var target = $('#model_id');
            target.val(this.model.id);
            target.parents('.modal').removeClass().addClass('modal fade issue-trash');
        }
    });

})(jQuery);