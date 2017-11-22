<div class="box">
    <div class="box-header">
        <div class="pull-right">
            @if($project->can('manage'))
            <button type="button" class="btn btn-primary" title="{{ trans('sharedFiles.create') }}" data-toggle="modal" data-target="#sharedfile"><i class="piplin piplin-plus"></i> {{ trans('sharedFiles.create') }}</button>
            @endif
        </div>
        <h3 class="box-title">{{ trans('sharedFiles.label') }}</h3>
    </div>


    <div class="box-body" id="no_sharedfiles">
        <p>{{ trans('sharedFiles.none') }}</p>
    </div>

    <div class="box-body table-responsive">
        <table class="table table-striped" id="sharedfile_list">
            <thead>
                <tr>
                    <th width="30%">{{ trans('sharedFiles.name') }}</th>
                    <th width="40%">{{ trans('sharedFiles.file') }}</th>
                    <th width="30%" class="text-right">{{ trans('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="sharedfile-template">
        <td><%- name %></td>
        <td><%- file %></td>
        <td>
            <div class="btn-group pull-right">
                @if($project->can('manage'))
                <button type="button" class="btn btn-default btn-edit" title="{{ trans('sharedFiles.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#sharedfile"><i class="piplin piplin-edit"></i></button>
                <button type="button" class="btn btn-danger btn-delete" title="{{ trans('sharedFiles.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="piplin piplin-delete"></i></button>
                @endif
            </div>
        </td>
    </script>
@endpush
