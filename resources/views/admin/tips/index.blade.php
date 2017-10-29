@extends('layouts.admin')

@section('admin-content')
<div class="panel-body" id="no_tips">
    <p>{{ trans('tips.none') }}</p>
</div>

<div class="panel-body table-responsive" id="tip_list">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ trans('tips.body') }}</th>
                <th class="text-right">{{ trans('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    {!! $tips_raw->render() !!}
</div>

@include('admin._dialogs.tip')
@stop

@section('right-buttons')
<div class="pull-right">
    <button type="button" class="btn btn-primary" title="{{ trans('tips.create') }}" data-toggle="modal" data-target="#tip"><span class="fixhub fixhub-plus"></span> {{ trans('tips.create') }}</button>
</div>
@stop

@push('javascript')
<script type="text/javascript">
    var tips = {!! $tips !!};
    new Fixhub.TipsTab();
    Fixhub.Tips.add(tips.data);
    @if(isset($action) && $action == 'create')
    $('button.btn.btn-primary').trigger('click');
    @endif
</script>
@endpush

@push('templates')
<script type="text/template" id="tip-template">
    <td data-tip-id="<%- id %>"><%- id %></td>
    <td><%- excerpt %></td>
    <td>
        <div class="btn-group pull-right">
            <button class="btn btn-default btn-show" title="{{ trans('tips.preview') }}" data-toggle="modal" data-target="#show_tip"><i class="fixhub fixhub-preview"></i></button>
            <button class="btn btn-default btn-edit" title="{{ trans('app.edit') }}" data-toggle="modal" data-target="#tip"><i class="fixhub fixhub-edit"></i></button>
            <button class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-target="#model-trash"><i class="fixhub fixhub-delete"></i></button>
        </div>
    </td>
</script>
@endpush