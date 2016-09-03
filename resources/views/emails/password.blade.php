@extends('emails.layout')

@section('content')
    <h1>{{ $app_name }}</h1>
    <br />
    <h2>{{ trans('emails.reset') }}</h2>

    <br />

    {{ trans('emails.reset_here') }}: <a href="{{ route('auth.reset-password-confirm', ['token' => $token]) }}">{{ route('auth.reset-password-confirm', ['token' => $token]) }}</a>
@stop
