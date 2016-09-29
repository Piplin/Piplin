@extends('layouts.dashboard')

@section('content')

<div class="box">

        @include('admin._partials.nav')
        <div class="box-body">
        Welcome to admin control panel.
    </div>
</div>
@stop

@section('right-buttons')
    <div class="pull-right">
        <a href="/" class="btn btn-default" title="Back to dashboard"><span class="ion ion-ios-undo-outline"></span> Back to dashboard</a>
    </div>
@stop