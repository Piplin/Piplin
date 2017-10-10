<div class="box">
    <div class="box-header">
        <div class="pull-right">
            <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-success" title="{{ trans('configFiles.create') }}" data-toggle="modal" data-target="#configfile"><span class="ion ion-plus"></span> {{ trans('configFiles.create') }}</button>
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
                    <th width="40%">{{ trans('configFiles.path') }}</th>
                    <th width="30%">&nbsp;</th>
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
        <td>
            <div class="btn-group pull-right">
                <button type="button" class="btn btn-default btn-view" title="{{ trans('configFiles.view') }}" data-toggle="modal" data-backdrop="static" data-target="#view-configfile"><i class="ion ion-eye"></i></button>
                <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-default btn-edit" title="{{ trans('configFiles.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#configfile"><i class="ion ion-compose"></i></button>
                <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="ion ion-trash-a"></i></button>
            </div>
        </td>
    </script>
@endpush
