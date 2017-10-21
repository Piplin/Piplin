@extends('layouts.admin')

@section('admin-content')
<div class="box-body" id="no_links">
    <p>{{ trans('links.none') }}</p>
</div>

<div class="box-body table-responsive" id="link_list">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>{{ trans('links.title') }}</th>
                <th>{{ trans('links.url') }}</th>
                <th>{{ trans('links.description') }}</th>
                <th class="text-right">{{ trans('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    {!! $links_raw->render() !!}
</div>

@include('admin._dialogs.link')
@stop

@section('right-buttons')
<div class="pull-right">
    <button type="button" class="btn btn-primary" title="{{ trans('links.create') }}" data-toggle="modal" data-target="#link"><span class="fixhub fixhub-plus"></span> {{ trans('links.create') }}</button>
</div>
@stop

@push('javascript')
<script type="text/javascript">
    var links = {!! $links !!};
    new Fixhub.LinksTab();
    Fixhub.Links.add(links.data);
    @if(isset($action) && $action == 'create')
    $('button.btn.btn-primary').trigger('click');
    @endif
</script>
@endpush

@push('templates')
<script type="text/template" id="link-template">
    <td data-link-id="<%- id %>"><span class="drag-handle"><i class="fixhub fixhub-drag"></i></span><%- title %></td>
    <td><%- url %></td>
    <td><%- description %></td>
    <td>
        <div class="btn-group pull-right">
            <a href="<%- url %>" target="_blank" class="btn btn-default btn-show" title="{{ trans('links.view_link') }}"><i class="fixhub fixhub-preview"></i></a>
            <button class="btn btn-default btn-edit" title="{{ trans('app.edit') }}" data-toggle="modal" data-target="#link"><i class="fixhub fixhub-edit"></i></button>
            <button class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-target="#model-trash"><i class="fixhub fixhub-delete"></i></button>
        </div>
    </td>
</script>
@endpush