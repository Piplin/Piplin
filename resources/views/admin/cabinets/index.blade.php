@extends('layouts.admin')

@section('admin-content')
<div class="panel-body" id="no_cabinets">
    <p>{{ trans('cabinets.none') }}</p>
</div>

<div class="panel-body table-responsive" id="cabinet_list">

    <table class="table table-striped">
        <thead>
            <tr>
                <th>{{ trans('cabinets.name') }}</th>
                <th>{{ trans('cabinets.servers') }}</th>
                <th>{{ trans('cabinets.description') }}</th>
                <th class="text-right">{{ trans('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    {!! $cabinets->render() !!}
</div>
@include('admin._dialogs.cabinet')
@stop

@section('right-buttons')
<div class="pull-right">
    <button type="button" class="btn btn-primary" title="{{ trans('cabinets.create') }}" data-toggle="modal" data-target="#cabinet"><span class="piplin piplin-plus"></span> {{ trans('cabinets.create') }}</button>
</div>
@stop

@push('javascript')
<script type="text/javascript">
    var cabinets = {!! $cabinets->toJson() !!};

    new Piplin.CabinetsTab();
    Piplin.Cabinets.add(cabinets.data);

    @if(isset($action) && $action == 'create')
    $('button.btn.btn-primary').trigger('click');
    @endif
</script>
@endpush

@push('templates')
<script type="text/template" id="cabinet-template">
    <td data-cabinet-id="<%- id %>"><span class="drag-handle"><i class="piplin piplin-drag"></i></span><a href="/admin/cabinets/<%- id %>"><%- name %></a>
    </td>
    <td><%- server_count %> <i class="piplin piplin-server server-names" data-html="true" data-toggle="tooltip" data-placement="right" title="<%- server_names %>"></i></td>
    <td><%- description %></td>
    <td>
        <div class="btn-cabinet pull-right">
            <button class="btn btn-default btn-edit" title="{{ trans('app.edit') }}" data-toggle="modal" data-target="#cabinet" data-cabinet-id="<%- id %>"><i class="piplin piplin-edit"></i></button>
            <button class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-target="#model-trash" data-cabinet-id="<%- id %>"><i class="piplin piplin-delete"></i></button>
        </div>
    </td>
</script>
@endpush
