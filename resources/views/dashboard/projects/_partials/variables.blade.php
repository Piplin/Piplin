<div class="box">
    <div class="box-header">
        <div class="pull-right">
            @if($project->can('manage'))
            <button type="button" class="btn btn-primary" title="{{ trans('variables.create') }}" data-toggle="modal" data-backdrop="static" data-target="#variable"><span class="piplin piplin-plus"></span> {{ trans('variables.create') }}</button>
            @endif
        </div>
        <h3 class="box-title">{{ trans('variables.label') }}</h3>
    </div>
    <div class="box-body">
        <p>{!! trans('variables.description') !!}</p>
        <p>{!! trans('variables.example') !!}</p>
    </div>

     <div class="box-body" id="no_variables">
        <p>{{ trans('variables.none') }}</p>
    </div>
    <div class="box-body table-responsive" id="variable_list">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ trans('variables.name') }}</th>
                    <th>{{ trans('variables.value') }}</th>
                    <th class="text-right">{{ trans('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="variable-template">
        <td data-variable-id="<%- id %>"><%- name %></td>
        <td><%- value %></td>
        <td>
            @if($project->can('manage'))
            <div class="btn-group pull-right">
                <button type="button" class="btn btn-default btn-edit" title="{{ trans('variables.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#variable"><i class="piplin piplin-edit"></i></button>
                <button type="button" class="btn btn-danger btn-delete" title="{{ trans('variables.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="piplin piplin-delete"></i></button>
            </div>
            @endif
        </td>
    </script>
@endpush
