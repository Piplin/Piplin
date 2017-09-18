<div class="box">
    <div class="box-header">
        <div class="pull-right">
            <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-success" title="{{ trans('environments.create') }}" data-toggle="modal" data-backdrop="static" data-target="#environment"><span class="ion ion-plus"></span> {{ trans('environments.create') }}</button>
        </div>
        <h3 class="box-title">{{ trans('environments.label') }}</h3>
    </div>
    <div class="box-body">
        <p>{!! trans('environments.description') !!}</p>
        <p>{!! trans('environments.example') !!}</p>
    </div>

    <div class="box-body table-responsive" id="environment_list">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ trans('environments.name') }}</th>
                    <th>{{ trans('environments.description') }}</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="environment-template">
        <td data-environment-id="<%- id %>"><%- name %></td>
        <td><%- description %></td>
        <td>
            <div class="btn-group pull-right">
                <button type="button" class="btn btn-default btn-edit" title="{{ trans('environments.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#environment"><i class="ion ion-compose"></i></button>
                <button type="button" class="btn btn-danger btn-delete" title="{{ trans('environments.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="ion ion-trash-a"></i></button>
            </div>
        </td>
    </script>
@endpush
