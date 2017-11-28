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
                    <th width="30%">{{ trans('configFiles.name') }}</th>
                    <th width="20%">{{ trans('configFiles.path') }}</th>
                    <th width="20%">{{ trans('configFiles.environments') }}</th>
                    <th width="30%" class="text-right">{{ trans('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="configfiles-template">
        <td><%- name %></td>
        <td><%- path %></td>
        <td><%- environment_names %></td>
        <td>
            <div class="btn-group pull-right">
                <button type="button" class="btn btn-default btn-view" title="{{ trans('configFiles.view') }}" data-toggle="modal" data-backdrop="static" data-target="#view-configfile"><i class="piplin piplin-preview"></i></button>
                <button type="button" class="btn btn-default btn-edit" title="{{ trans('configFiles.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#configfile"><i class="piplin piplin-edit"></i></button>
                <button type="button" class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="piplin piplin-delete"></i></button>
            </div>
        </td>
    </script>
@endpush
