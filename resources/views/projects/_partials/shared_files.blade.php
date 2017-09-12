<div class="box">
    <div class="box-header">
        <div class="pull-right">
            <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-success" title="{{ trans('sharedFiles.create') }}" data-toggle="modal" data-target="#sharefile"><span class="ion ion-plus"></span> {{ trans('sharedFiles.create') }}</button>
        </div>
        <h3 class="box-title">{{ trans('sharedFiles.label') }}</h3>
    </div>


    <div class="box-body" id="no_files">
        <p>{{ trans('sharedFiles.none') }}</p>
    </div>

    <div class="box-body table-responsive">
        <table class="table table-striped" id="file_list">
            <thead>
                <tr>
                    <th width="30%">{{ trans('sharedFiles.name') }}</th>
                    <th width="40%">{{ trans('sharedFiles.file') }}</th>
                    <th width="30%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="files-template">
        <td><%- name %></td>
        <td><%- file %></td>
        <td>
            <div class="btn-group pull-right">
                <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-default btn-edit" title="{{ trans('sharedFiles.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#sharefile"><i class="ion ion-compose"></i></button>
                <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-danger btn-delete" title="{{ trans('sharedFiles.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="ion ion-trash-a"></i></button>
            </div>
        </td>
    </script>
@endpush
