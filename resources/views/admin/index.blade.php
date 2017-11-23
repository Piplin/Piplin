@extends('layouts.admin')

@section('admin-content')

<div class="panel-heading">
    <h4><i class="piplin piplin-clock"></i> {{ trans_choice('dashboard.latest', 2) }}</h4>
</div>
<div class="panel-body" id="timeline">
    @include('dashboard.timeline')
</div>

@stop