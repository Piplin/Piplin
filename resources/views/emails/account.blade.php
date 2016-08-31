@extends('emails.layout')

@section('content')
    <h1>{{ trans('app.name') }}</h1>
    <br />
    <h2>{{ trans('emails.created') }}</h2>

    <br />
    {{ trans('emails.login_at') }}: <a href="{{ route('dashboard') }}">{{ route('dashboard') }}</a>

    <br />
    <br />
    <ul>
        <li><strong>{{ trans('emails.username') }}</strong>: {{ $email }}</li>
        <li><strong>{{ trans('emails.password') }}</strong>: {{ $password }}</li>
    </ul>
@stop
