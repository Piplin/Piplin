@extends('layouts.admin')

@section('admin-content')
<div class="box-body" id="no_keys">
    <p>{{ trans('keys.none') }}</p>
</div>

<div class="box-body table-responsive" id="key_list">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>{{ trans('keys.name') }}</th>
                <th>{{ trans('keys.fingerprint') }}</th>
                <th class="text-right">{{ trans('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    {!! $keys_raw->render() !!}
</div>

@include('admin._dialogs.key')
@stop

@section('right-buttons')
<div class="pull-right">
    <button type="button" class="btn btn-primary" title="{{ trans('keys.create') }}" data-toggle="modal" data-target="#key"><span class="ion ion-plus-round"></span> {{ trans('keys.create') }}</button>
</div>
@stop

@push('javascript')
<script type="text/javascript">
    var keys = {!! $keys !!};
    new Fixhub.KeysTab();
    Fixhub.Keys.add(keys.data);
    @if(isset($action) && $action == 'create')
    $('button.btn.btn-primary').trigger('click');
    @endif
</script>
@endpush

@push('templates')
<script type="text/template" id="key-template">
    <td data-key-id="<%- id %>"><span class="drag-handle"><i class="ion ion-drag"></i></span><%- name %></td>
    <td><%- fingerprint %></td>
    <td>
        <div class="btn-group pull-right">
            <button class="btn btn-default btn-show" title="{{ trans('keys.view_ssh_key') }}" data-toggle="modal" data-target="#show_key"><i class="ion ion-eye"></i></button>
            <button class="btn btn-default btn-edit" title="{{ trans('app.edit') }}" data-toggle="modal" data-target="#key"><i class="ion ion-compose"></i></button>
            <button class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-target="#model-trash"><i class="ion ion-trash-a"></i></button>
        </div>
    </td>
</script>
@endpush