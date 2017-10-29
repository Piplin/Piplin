@extends('layouts.dashboard')

@section('content')
<div class="row edit-profile">
    <div class="col-md-3">
    @include('profile._partials.sidebar')

    </div>
    <div class="col-md-9">
    @include('profile._partials.'.$tab)
    </div>
</div>
@endsection
