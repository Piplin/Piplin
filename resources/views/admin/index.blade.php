@extends('layouts.admin')

@section('admin-content')
<div class="box-body">
    <div class="col-md-6">
        @include('admin.dashboard.environments')
    </div>
    <div class="col-md-6">
        @include('admin.dashboard.dependencies')
    </div>
</div>
@stop

@section('right-buttons')
    <div class="pull-right">
        <a href="/" class="btn btn-default" title="Back to dashboard"><span class="ion ion-ios-undo-outline"></span> {{ trans('dashboard.title') }}</a>
    </div>
@stop