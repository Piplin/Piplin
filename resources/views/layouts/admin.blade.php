@extends('layouts.dashboard')
@section('content')
@include('admin._partials.update')
<div class="row">
    <div class="col-md-12">
        <div class="nav-tabs-custom">
        @if(isset($sub_menu))
            @include('admin._partials.nav', ['sub_menu' => $sub_menu[$current_menu]['children']])
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