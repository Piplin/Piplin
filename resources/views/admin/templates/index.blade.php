@extends('layouts.admin')

@section('admin-content')
<div class="box-body" id="no_templates">
    <p>{{ trans('templates.none') }}</p>
</div>

<div class="box-body table-responsive" id="template_list">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ trans('templates.name') }}</th>
                <th>{{ trans('commands.label') }}</th>
                <th>{{ trans('variables.label') }}</th>
                <th>{{ trans('sharedFiles.label') }}</th>
                <th>{{ trans('configFiles.label') }}</th>
                <th class="text-right">{{ trans('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    {!! $templates_raw->render() !!}
</div>

@include('admin._dialogs.template')
@stop

@section('right-buttons')
<div class="pull-right">
    <button type="button" class="btn btn-primary" title="{{ trans('templates.create') }}" data-toggle="modal" data-target="#template"><span class="fixhub fixhub-plus"></span> {{ trans('templates.create') }}</button>
</div>
@stop

@push('javascript')
<script type="text/javascript">
    var templates = {!! $templates !!};
    new Fixhub.TemplatesTab();
    Fixhub.Templates.add(templates.data);
</script>
@endpush

@push('templates')
<script type="text/template" id="template-template">
    <td><%- id %></td>
    <td><a href="/admin/templates/<%- id %>"><%- name %></a></td>
    <td><%- command_count %></td>
    <td><%- variable_count %></td>
    <td><%- file_count %></td>
    <td><%- config_count %></td>
    <td>
        <div class="btn-group pull-right">
            <button class="btn btn-default btn-edit" title="{{ trans('app.edit') }}" data-toggle="modal" data-target="#template"><i class="fixhub fixhub-edit"></i></button>
            <button class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-target="#model-trash"><i class="fixhub fixhub-delete"></i></button>
        </div>
    </td>
</script>
@endpush
