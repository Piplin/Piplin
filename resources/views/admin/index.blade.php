@extends('layouts.dashboard')

@section('content')

<div class="box">

        @include('admin._partials.nav')
        <div class="box-body">
        <!-- start -->
        <div class="row">
            <div class="col-md-6">
                @include('admin.dashboard.environment')
            </div>
            <div class="col-md-6">
                @include('admin.dashboard.dependencies')
            </div>
        </div>
        <!-- end -->
        </div>
</div>
<!-- /.box -->
@stop

@section('right-buttons')
    <div class="pull-right">
        <a href="/" class="btn btn-default" title="Back to dashboard"><span class="ion ion-ios-undo-outline"></span> {{ trans('dashboard.title') }}</a>
    </div>
@stop