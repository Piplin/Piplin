@extends('layouts.admin')

@section('admin-content')
<div class="box-body" id="no_tips">
    <p>{{ trans('tips.none') }}</p>
</div>

<div class="box-body table-responsive" id="tip_list">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ trans('tips.body') }}</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    {!! $tips_raw->render() !!}
</div>

@include('admin.tips.dialog')
@stop

@section('right-buttons')
<div class="pull-right">
    <button type="button" class="btn btn-success" title="{{ trans('tips.create') }}" data-toggle="modal" data-target="#tip"><span class="ion ion-plus-round"></span> {{ trans('tips.create') }}</button>
</div>
@stop

@push('javascript')
<script type="text/javascript">
    var tips = {!! $tips !!};
    new app.TipsTab();
    app.Tips.add(tips.data);
    @if(isset($action) && $action == 'create')
    $('button.btn.btn-success').trigger('click');
    @endif
</script>
@endpush

@push('templates')
<script type="text/template" id="tip-template">
    <td data-tip-id="<%- id %>"><%- id %></td>
    <td><%- excerpt %></td>
    <td>
        <div class="btn-group pull-right">
            <button class="btn btn-default btn-show" title="{{ trans('tips.preview') }}" data-toggle="modal" data-target="#show_tip"><i class="ion ion-eye"></i></button>
            <button class="btn btn-default btn-edit" title="{{ trans('app.edit') }}" data-toggle="modal" data-target="#tip"><i class="ion ion-compose"></i></button>
            <button class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-target="#model-trash"><i class="ion ion-trash-a"></i></button>
        </div>
    </td>
</script>
@endpush