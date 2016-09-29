@extends('layouts.dashboard')

@section('content')
    <div class="box">

        <div class="box-body" id="no_groups">
            <p>{{ trans('groups.none') }}</p>
        </div>

        <div class="box-body table-responsive" id="group_list">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ trans('groups.name') }}</th>
                        <th>{{ trans('groups.projects') }}</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
            {!! $groups->render() !!}
        </div>
    </div>

    @include('admin.groups.dialog')
@stop

@section('right-buttons')
    <div class="pull-right">
        <button type="button" class="btn btn-success" title="{{ trans('groups.create') }}" data-toggle="modal" data-target="#group"><span class="ion ion-plus"></span> {{ trans('groups.create') }}</button>
    </div>
@stop

@push('javascript')
    <script type="text/javascript">
        var groups = {!! $groups->toJson() !!};

        new app.GroupsTab();
        app.Groups.add(groups.data);

        @if(isset($action) && $action == 'create')
        $('button.btn.btn-success').trigger('click');
        @endif
    </script>
@endpush

@push('templates')
    <script type="text/template" id="group-template">
        <td data-group-id="<%- id %>"><span class="drag-handle"><i class="ion ion-drag"></i></span><%- name %></td>
        <td><%- project_count %></td>
        <td>
            <div class="btn-group pull-right">
                <button class="btn btn-default btn-edit" title="{{ trans('groups.edit') }}" data-toggle="modal" data-target="#group" data-group-id="<%- id %>"><i class="ion ion-compose"></i></button>
                <button class="btn btn-danger btn-delete" title="{{ trans('groups.delete') }}" data-toggle="modal" data-target="#model-trash" data-group-id="<%- id %>"><i class="ion ion-trash-a"></i></button>
            </div>
        </td>
    </script>
@endpush
