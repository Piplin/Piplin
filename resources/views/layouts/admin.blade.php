@extends('layouts.dashboard')
@section('content')
<div class="row">
    <div class="col-md-3 sub-sidebar">
        @include('admin._partials.sub-sidebar')
    </div>
    <div class="col-md-9">
        <div class="nav-tabs-custom">
        @if(isset($sub_menu) and $sub_menu)
            @include('admin._partials.nav')
        @endif
        <div class="tab-content">
            <div class="panel">
                @yield('admin-content')
            </div>
        </div>
        </div>
    </div>
</div>
@stop