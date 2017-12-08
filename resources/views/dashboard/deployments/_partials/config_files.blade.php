<div class="box">
    <div class="box-header">
        <div class="pull-right">
            <button type="button" class="btn btn-primary" title="{{ trans('configFiles.create') }}" data-toggle="modal" data-target="#configfile"><i class="piplin piplin-plus"></i> {{ trans('configFiles.create') }}</button>
        </div>
        <h3 class="box-title">{{ trans('configFiles.label') }}</h3>
    </div>


    <div class="box-body" id="no_configfiles">
        <p>{{ trans('configFiles.none') }}</p>
    </div>

    <div class="box-body table-responsive">
        <table class="table table-striped" id="configfile_list">
            <thead>
                <tr>
                    <th width="20%">{{ trans('configFiles.name') }}</th>
                    <th width="10%">{{ trans('configFiles.path') }}</th>
                    <th width="20%">{{ trans('configFiles.environments') }}</th>
                    <th width="10%">{{ trans('configFiles.status') }}</th>
                    <th width="40%" class="text-right">{{ trans('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="show_log" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="piplin piplin-code"></i> {{ trans('servers.output') }}</h4>
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

@push('templates')
    <script type="text/template" id="configfiles-template">
        <td><%- name %></td>
        <td><%- path %></td>
        <td><%- environment_names %></td>
        <td><span class="text-<%- status_css %>"><i class="piplin piplin-<%-icon_css %>"></i> <span><%- status %></span></span>
        </td>
        <td>
            <div class="btn-group pull-right">
                <% if (output !== null) { %>
                    <button type="button" class="btn btn-default btn-show" title="{{ trans('tasks.output') }}" id="log_<%- id %>" data-toggle="modal" data-backdrop="static" data-target="#show_log"><i class="piplin piplin-copy"></i></button>
                <% } %>
                <button type="button" <% if (status === "{{trans('configFiles.syncing')}}") { %> class="btn btn-default btn-sync" disabled<% } else { %> class="btn btn-info btn-sync" <% } %> title="{{ trans('configFiles.sync') }}"><i class="piplin piplin-refresh" title="{{ trans('configFiles.sync') }}" data-toggle="modal" data-backdrop="static" data-target="#sync-configfile" data-configfile-id="<%- id %>"></i></button>
                <button type="button" class="btn btn-default btn-view" title="{{ trans('configFiles.view') }}" data-toggle="modal" data-backdrop="static" data-target="#view-configfile"><i class="piplin piplin-preview"></i></button>
                <button type="button" class="btn btn-default btn-edit" title="{{ trans('configFiles.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#configfile"><i class="piplin piplin-edit"></i></button>
                <button type="button" class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="piplin piplin-delete"></i></button>
            </div>
        </td>
    </script>
@endpush
