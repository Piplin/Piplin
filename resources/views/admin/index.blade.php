@extends('layouts.admin')

@section('admin-content')
<div class="panel-body">
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
        <a href="/" class="btn btn-default" title="Back to dashboard"><span class="fixhub fixhub-left"></span> {{ trans('dashboard.title') }}</a>
    </div>
@stop