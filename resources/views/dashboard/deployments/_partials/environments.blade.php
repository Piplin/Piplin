<div class="box">
    <div class="box-header">
        <div class="pull-right">
            <button class="btn btn-primary" type="button" data-toggle="modal" data-backdrop="static" data-target="#environment"><i class="piplin piplin-plus"></i> {{ trans('environments.create') }}</button>
        </div>
        <h3 class="box-title">{{ trans('environments.label') }}</h3>
    </div>

    <div class="box-body" id="no_environments">
        <p>{{ trans('environments.none') }}</p>
    </div>

    <div class="box-body table-responsive" id="environment_list">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ trans('environments.name') }}</th>
                    <th>{{ trans('environments.links') }}</th>
                    <th>{{ trans('environments.servers') }}</th>
                    <th>{{ trans('environments.status') }}</th>
                    <th>{{ trans('environments.completed') }}</th>
                    <th>{{ trans('environments.default_on') }}</th>
                    <th class="text-right">{{ trans('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="environment-template">
        <td data-environment-id="<%- id %>"><span class="drag-handle"><i class="piplin piplin-drag"></i></span>
        @if (Route::currentRouteName() == 'deployments')
        <a href="/deploy-plan/{{ $targetable_id }}/environments/<%- id %>" title="<%- description %>"><%- name %></a>
        @else
        <%- name %>
        @endif
        </td>
        <td><i class="piplin piplin-link server-names" data-html="true" data-toggle="tooltip" data-placement="right" title="<%- link_names %>"></i> <%- link_count %></td>
        <td><i class="piplin piplin-server server-names" data-html="true" data-toggle="tooltip" data-placement="right" title="<%- server_names %>"></i> <%- server_count %> <i class="piplin piplin-cabinet server-names" data-html="true" data-toggle="tooltip" data-placement="right" title="<%- cabinet_names %>"></i> <%- cabinet_count %></td>
        <td><span class="text-<%- label_class %>"><i class="piplin piplin-<%-icon_class %>"></i> <span><%- label %></span></td>
        <td><%- last_run %></td>
        <td><% if (default_on) { %>{{ trans('app.yes') }}<% } else { %>{{ trans('app.no') }}<% } %></td>
        <td>
            <div class="btn-group pull-right">
                <a href="/deploy-plan/{{ $targetable_id }}/environments/<%- id %>?action=add-server" class="btn btn-info" title="{{ trans('servers.create') }}"><i class="piplin piplin-plus"></i></a>
                <button type="button" class="btn btn-default btn-edit" title="{{ trans('environments.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#environment"><i class="piplin piplin-edit"></i></button>
                <button type="button" class="btn btn-danger btn-delete" title="{{ trans('environments.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="piplin piplin-delete"></i></button>
            </div>
        </td>
    </script>
@endpush
