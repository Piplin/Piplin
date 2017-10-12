@extends('layouts.dashboard')


@section('content')
<div class="nav-tabs-custom">
    @include('admin._partials.nav')
    <div class="tab-content">
        <!-- start -->
        <div class="row">
            @if(isset($sub_menu) and $sub_menu)
            <div class="col-md-3 sub-sidebar">
                @include('admin._partials.sub-sidebar')
            </div>
            <div class="col-md-9">
                <div class="box">
                    @yield('admin-content')
                </div>
            </div>
            @else
            <div class="col-md-12">
                <div class="box">
                    @yield('admin-content')
                </div>
            </div>
            @endif
        </div>
        <!-- end -->
    </div>
</div>
@stop