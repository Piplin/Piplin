<div class="modal fade" id="show_log">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-code"></i> {{ trans('servers.output') }}</h4>
            </div>
            <div class="modal-body">
                <div id="log"><pre>loading</pre></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('app.close') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <div class="pull-right">
            <button {{ $current_user->is_admin ?: 'disabled="true"' }} type="button" class="btn btn-primary" title="{{ trans('servers.create') }}" data-toggle="modal" data-backdrop="static" data-target="#server"><span class="ion ion-plus"></span> {{ trans('servers.create') }}</button>
        </div>
        <h3 class="box-title">{{ trans('environments.label') }} : {{ $environment->name }}</h3>
    </div>

    <div class="box-body" id="no_servers">
        <p>{{ trans('servers.none') }}</p>
    </div>

    <div class="box-body table-responsive" id="server_list">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ trans('servers.name') }}</th>
                    <th>{{ trans('servers.connect_as') }}</th>
                    <th>{{ trans('servers.ip_address') }}</th>
                    <th>{{ trans('servers.port') }}</th>
                    <th>{{ trans('servers.status') }}</th>
                    <th>{{ trans('servers.updated_at') }}</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="server-template">
        <td data-server-id="<%- id %>"><span class="drag-handle"><i class="ion ion-drag"></i></span><%- name %><% if (!enabled) { %> <i class="ion ion-android-remove-circle text-danger" data-toggle="tooltip" data-placement="right" title="{{ trans('servers.disabled') }}"></i><% } %></td>
        <td><%- user %></td>
        <td><%- ip_address %></td>
        <td><%- port %></td>
        <td>
             <span class="label label-<%- status_css %>"><i class="ion ion-<%-icon_css %>"></i> <%- status %></span>
        </td>
        <td><%- updated_at %></td>
        <td>
            <div class="btn-group pull-right">
                <% if (output !== null) { %>
                    <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-default btn-show" title="{{ trans('deployments.output') }}" id="log_<%- id %>" data-toggle="modal" data-backdrop="static" data-target="#show_log"><i class="ion ion-ios-copy-outline"></i></button>
                <% } %>
                <% if (status === 'Testing') { %>
                    <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-default btn-test" title="{{ trans('servers.test') }}" disabled><i class="ion ion-refresh fixhub-spin"></i></button>
                    <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-default btn-edit" title="{{ trans('servers.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#server" disabled><i class="ion ion-compose"></i></button>
                    <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-danger btn-delete" title="{{ trans('servers.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#server-trash" disabled><i class="ion ion-trash-a"></i></button>
                <% } else { %>
                    <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-default btn-test" title="{{ trans('servers.test') }}"><i class="ion ion-arrow-swap"></i></button>
                    <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-default btn-edit" title="{{ trans('servers.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#server"><i class="ion ion-compose"></i></button>
                    <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="ion ion-trash-a"></i></button>
                <% } %>
            </div>
        </td>
    </script>
@endpush
