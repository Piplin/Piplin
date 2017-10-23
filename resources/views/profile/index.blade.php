@extends('layouts.dashboard')

@section('content')
<div class="row">
    <div class="col-md-12">
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>{{ trans('auth.oops') }}</strong> {{ trans('auth.problems') }}<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    </div>
</div>
<div class="row edit-profile">
    <div class="col-md-3">
    @include('profile._partials.sidebar')

    </div>
    <div class="col-md-9">
    @include('profile._partials.'.$tab)
    </div>
</div>
@endsection
