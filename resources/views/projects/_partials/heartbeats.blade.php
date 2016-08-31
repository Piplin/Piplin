<div class="box">
    <div class="box-header">
        <div class="pull-right">
            <button type="button" class="btn btn-success" title="{{ trans('heartbeats.create') }}" data-toggle="modal" data-backdrop="static" data-target="#heartbeat"><span class="ion ion-plus"></span> {{ trans('heartbeats.create') }}</button>
        </div>
        <h3 class="box-title">{{ trans('heartbeats.label') }}</h3>
    </div>

    <div class="box-body" id="no_heartbeats">
        <p>{{ trans('heartbeats.none') }}</p>
    </div>

    <div class="box-body table-responsive" id="heartbeat_list">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th width="10%">{{ trans('heartbeats.name') }}</th>
                    <th width="45%">{{ trans('heartbeats.url') }}</th>
                    <th width="10%">{{ trans('heartbeats.interval') }}</th>
                    <th width="15%">{{ trans('heartbeats.last_check_in') }}</th>
                    <th width="10%">{{ trans('heartbeats.status') }}</th>
                    <th width="10%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="heartbeat-template">
        <td><%- name %></td>
        <td><%- callback_url %></td>
        <td><%- interval_label %></td>
        <td>
            <% if (has_run) { %>
                <%- formatted_date %>
            <% } else { %>
                {{ trans('app.never') }}
            <% } %>
        </td>
        <td>
             <span class="label label-<%- status_css %>"><i class="ion ion-<%-icon_css %>"></i> <%- status %></span>
        </td>
        <td>
            <div class="btn-group pull-right">
                <button type="button" class="btn btn-default btn-edit" title="{{ trans('heartbeats.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#heartbeat"><i class="ion ion-compose"></i></button>
                <button type="button" class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="ion ion-trash-a"></i></button>
            </div>
        </td>
    </script>
@endpush
