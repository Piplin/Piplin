<div class="callout">
    <h4>{{ trans('commands.deploy_webhook') }} <i class="ion ion-help-buoy" id="show_help" data-toggle="modal" data-backdrop="static" data-target="#help"></i></h4>
    <code id="webhook">{{ $project->webhook_url }}</code><button class="btn btn-xs btn-link" id="new_webhook" title="{{ trans('commands.generate_webhook') }}" data-project-id="{{ $project->id }}"><i class="ion ion-refresh"></i></button>
</div>
<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ trans('triggers.label') }}</h3>
        <div class="pull-right">
            <button type="button" class="btn btn-success" title="{{ trans('triggers.create') }}" data-toggle="modal" data-target="#trigger"><span class="ion ion-plus"></span> {{ trans('triggers.create') }}</button>
        </div>
    </div>

    <div class="box-body">
        <p>{{ trans('triggers.help') }}</p>
    </div>

    <div class="box-body" id="no_triggers">
        <p>{{ trans('triggers.none') }}</p>
    </div>

    <div class="box-body table-responsive">
        <table class="table table-striped" id="trigger_list">
            <thead>
                <tr>
                    <th>{{ trans('triggers.name') }}</th>
                    <th>{{ trans('triggers.type') }}</th>
                    <th>{{ trans('triggers.enabled') }}</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@include('dashboard.projects.dialogs.incoming_webhook')

@push('templates')
    <script type="text/template" id="trigger-template">
        <td><%- name %></td>
        <td><span class="ion ion-<%- icon %>"></span> <%- label %></td>
        <td><% if (enabled) { %>{{ trans('app.yes') }}<% } else { %>{{ trans('app.no') }}<% } %></td>
        <td>
            <div class="btn-group pull-right">
                <button type="button" class="btn btn-default btn-edit" title="{{ trans('triggers.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#trigger"><i class="ion ion-edit"></i></button>
                <button type="button" class="btn btn-danger btn-delete" title="{{ trans('triggers.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="ion ion-trash-a"></i></button>
            </div>
        </td>
    </script>
@endpush