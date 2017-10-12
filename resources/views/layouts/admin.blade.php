@extends('layouts.dashboard')


@section('content')

<div class="box">
        @include('admin._partials.nav')
        <div class="box-body">
        <!-- start -->
        <div class="row">
            @if(isset($sub_menu) and $sub_menu)
            <div class="col-md-3 sub-sidebar">
                @include('admin._partials.sub-sidebar')
            </div>
            <div class="col-md-9">
                @yield('admin-content')
            </div>
            @else
            <div class="col-md-12">
                @yield('admin-content')
            </div>
            @endif
        </div>
        <!-- end -->
        </div>
</div>
<!-- /.box -->
@stop