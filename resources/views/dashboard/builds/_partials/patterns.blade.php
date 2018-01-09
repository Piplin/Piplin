<div class="box">
    <div class="box-header">
        <div class="pull-right">
            @if($project->can('manage'))
            <button type="button" class="btn btn-primary" title="{{ trans('patterns.create') }}" data-toggle="modal" data-target="#pattern"><i class="piplin piplin-plus"></i> {{ trans('patterns.create') }}</button>
            @endif
        </div>
        <h3 class="box-title">{{ trans('patterns.label') }}</h3>
    </div>

    <div class="box-body">
        <p>{!! trans('patterns.description') !!}</p>
    </div>

    <div class="box-body" id="no_patterns">
        <p>{{ trans('patterns.none') }}</p>
    </div>

    <div class="box-body table-responsive">
        <table class="table table-striped" id="pattern_list">
            <thead>
                <tr>
                    <th width="30%">{{ trans('patterns.name') }}</th>
                    <th width="40%">{{ trans('patterns.copy_pattern') }}</th>
                    <th width="20%">{{ trans('patterns.commands') }}</th>
                    <th width="10%" class="text-right">{{ trans('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="pattern-template">
        <td><%- name %></td>
        <td><%- copy_pattern %></td>
        <td><%- command_names %></td>
        <td>
            <div class="btn-group pull-right">
                @if($project->can('manage'))
                <button type="button" class="btn btn-default btn-edit" title="{{ trans('patterns.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#pattern"><i class="piplin piplin-edit"></i></button>
                <button type="button" class="btn btn-danger btn-delete" title="{{ trans('patterns.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="piplin piplin-delete"></i></button>
                @endif
            </div>
        </td>
    </script>
@endpush
