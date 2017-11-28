<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ trans('cabinets.label') }}</h3>
        <div class="pull-right">
            @if($project->can('manage'))
            <button type="button" class="btn btn-primary" title="{{ trans('cabinets.link') }}" data-toggle="modal" data-target="#cabinet"><span class="piplin piplin-plus"></span> {{ trans('cabinets.link') }}</button>
            @endif
        </div>
    </div>

    <div class="box-body" id="no_cabinets">
        <p>{{ trans('cabinets.none') }}</p>
    </div>

    <div class="box-body table-responsive">
        <table class="table table-striped" id="cabinet_list">
            <thead>
                <tr>
                    <th>{{ trans('cabinets.name') }}</th>
                    <th>{{ trans('cabinets.servers') }}</th>
                    <th>{{ trans('cabinets.description') }}</th>
                    <th>{{ trans('cabinets.status') }}</th>
                    <th class="text-right">{{ trans('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="cabinet-template">
        <td><%- name %></td>
        <td><%- server_count %> <i class="piplin piplin-server server-names" data-html="true" data-toggle="tooltip" data-placement="right" title="<%- server_names %>"></i></td>
        <td><%- description %></td>
        <td>{{ trans('cabinets.enabled') }}</td>
        <td class="text-right">
            @if($project->can('manage'))
            <div class="btn-group">
                <button type="button" class="btn btn-danger btn-delete" title="{{ trans('cabinets.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="piplin piplin-delete"></i></button>
            </div>
            @endif
        </td>
    </script>
@endpush