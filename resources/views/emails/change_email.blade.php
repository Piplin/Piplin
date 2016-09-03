@extends('emails.layout')

@section('content')
    <h1>{{ $app_name }}</h1>
    <br />
    <h2>{{ trans('emails.login_reset') }}</h2>

    <br />
    {{ trans('emails.request_email', ['username' => $name ]) }}: <a href="{{ route('profile.confirm-change-email', ['token' => $token]) }}">{{ route('profile.confirm-change-email', ['token' => $token]) }}</a>
@stop
