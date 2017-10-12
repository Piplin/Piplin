@extends('layouts.admin')

@section('admin-content')
<div class="box-body" id="no_providers">
    <p>{{ trans('providers.none') }}</p>
</div>

<div class="box-body table-responsive" id="provider_list">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>{{ trans('providers.name') }}</th>
                <th>{{ trans('providers.slug') }}</th>
                <th>{{ trans('providers.login_button') }}</th>
                <th>{{ trans('providers.description') }}</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    {!! $providers_raw->render() !!}
</div>

@include('admin.providers.dialog')
@stop

@section('right-buttons')
    <div class="pull-right">
        <button type="button" class="btn btn-success" title="{{ trans('providers.create') }}" data-toggle="modal" data-target="#provider"><span class="ion ion-plus-round"></span> {{ trans('providers.create') }}</button>
    </div>
@stop

@push('javascript')
    <script type="text/javascript">
        var providers = {!! $providers !!};
        new app.ProvidersTab();
        app.Providers.add(providers.data);
        @if(isset($action) && $action == 'create')
        $('button.btn.btn-success').trigger('click');
        @endif
    </script>
@endpush

@push('templates')
    <script type="text/template" id="provider-template">
        <td data-provider-id="<%- id %>"><span class="drag-handle"><i class="ion ion-drag"></i></span><%- name %></td>
        <td><%- slug %></td>
        <td>
            <button class="btn btn-social btn-<%- slug %>"><i class="ion <% if (icon) { %> <%- icon %><% } else { %>ion-android-open<% } %>"></i> <%- name %></button>
        </td>
        <td><%- description %></td>
        <td>
            <div class="btn-group pull-right">
                <button class="btn btn-default btn-edit" title="{{ trans('app.edit') }}" data-toggle="modal" data-target="#provider"><i class="ion ion-compose"></i></button>
                <button class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-target="#model-trash"><i class="ion ion-trash-a"></i></button>
            </div>
        </td>
    </script>
@endpush