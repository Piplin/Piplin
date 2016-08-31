<div class="box">
    <div class="box-header">
        <div class="pull-right">
            <button type="button" class="btn btn-success" title="{{ trans('issues.create') }}" data-toggle="modal" data-backdrop="static" data-target="#issue"><span class="ion ion-plus"></span> {{ trans('issues.create') }}</button>
        </div>
        <h3 class="box-title">{{ trans('issues.label') }}</h3>
    </div>

    <div class="box-body" id="no_issues">
        <p>{{ trans('issues.none') }}</p>
    </div>

    <div class="box-body table-responsive" id="issue_list">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{{ trans('issues.title') }}</th>
                    <th>{{ trans('issues.content') }}</th>
                    <th>{{ trans('app.created') }}</th>
                    <th>{{ trans('issues.updated_at') }}</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="issue-template">
        <td><%- id %></td>
        <td data-issue-id="<%- id %>"><%- title %></td>
        <td><%- content %></td>
        <td><%- created_at %></td>
        <td><%- updated_at %></td>
        <td>
            <div class="btn-group pull-right">
                <button type="button" class="btn btn-success btn-confirm" title="{{ trans('issues.confirm') }}"><i class="ion ion-ios-checkmark-outline"></i></button>
                <button type="button" class="btn btn-default btn-edit" title="{{ trans('issues.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#issue"><i class="ion ion-compose"></i></button>
                <button type="button" class="btn btn-danger btn-delete" title="{{ trans('issues.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="ion ion-trash-a"></i></button>
            </div>
        </td>
    </script>
@endpush
