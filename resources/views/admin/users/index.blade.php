@extends('layouts.admin')

@section('admin-content')
<div class="box-body table-responsive" id="user_list">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ trans('users.name') }}</th>
                <th>{{ trans('users.role') }}</th>
                <th>{{ trans('users.email') }}</th>
                <th>{{ trans('app.created') }}</th>
                <th class="text-right">{{ trans('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    {!! $users_raw->render() !!}
</div>

@include('admin._dialogs.user')
@stop

@section('right-buttons')
    <div class="pull-right">
        <button type="button" class="btn btn-primary" title="{{ trans('users.create') }}" data-toggle="modal" data-target="#user"><span class="fixhub fixhub-plus"></span> {{ trans('users.create') }}</button>
    </div>
@stop

@push('javascript')
    <script type="text/javascript">
        var users = {!! $users !!};

        new Fixhub.UsersTab();
        Fixhub.Users.add(users.data);
    </script>
@endpush

@push('templates')
    <script type="text/template" id="user-template">
        <td><%- id %></td>
        <td><%- name %></td>
        <td><%- role_name %></td>
        <td><%- email %></td>
        <td><%- created %></td>
        <td>
            <div class="btn-group pull-right">
                <button class="btn btn-default btn-edit" title="{{ trans('app.edit') }}" data-toggle="modal" data-target="#user" data-user-id="<%- id %>"><i class="fixhub fixhub-edit"></i></button>
                <button class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-target="#model-trash" data-user-id="<%- id %>"><i class="fixhub fixhub-delete"></i></button>
            </div>
        </td>
    </script>
@endpush
