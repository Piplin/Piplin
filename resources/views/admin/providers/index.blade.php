@extends('layouts.admin')

@section('admin-content')
<div class="panel-body" id="no_providers">
    <p>{{ trans('providers.none') }}</p>
</div>

<div class="panel-body table-responsive" id="provider_list">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>{{ trans('providers.name') }}</th>
                <th>{{ trans('providers.slug') }}</th>
                <th>{{ trans('providers.login_button') }}</th>
                <th>{{ trans('providers.description') }}</th>
                <th class="text-right">{{ trans('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    {!! $providers_raw->render() !!}
</div>

@include('admin._dialogs.provider')
@stop

@section('right-buttons')
    <div class="pull-right">
        <button type="button" class="btn btn-primary" title="{{ trans('providers.create') }}" data-toggle="modal" data-target="#provider"><span class="piplin piplin-plus"></span> {{ trans('providers.create') }}</button>
    </div>
@stop

@push('javascript')
    <script type="text/javascript">
        var providers = {!! $providers !!};
        new Piplin.ProvidersTab();
        Piplin.Providers.add(providers.data);
        @if(isset($action) && $action == 'create')
        $('button.btn.btn-primary').trigger('click');
        @endif
    </script>
@endpush

@push('templates')
    <script type="text/template" id="provider-template">
        <td data-provider-id="<%- id %>"><span class="drag-handle"><i class="piplin piplin-drag"></i></span><%- name %></td>
        <td><%- slug %></td>
        <td>
            <button class="btn btn-social btn-<%- slug %>"><i class="piplin piplin-cube"></i> <%- name %></button>
        </td>
        <td><%- description %></td>
        <td>
            <div class="btn-group pull-right">
                <button class="btn btn-default btn-edit" title="{{ trans('app.edit') }}" data-toggle="modal" data-target="#provider"><i class="piplin piplin-edit"></i></button>
                <button class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-target="#model-trash"><i class="piplin piplin-delete"></i></button>
            </div>
        </td>
    </script>
@endpush