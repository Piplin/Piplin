@extends('layouts.basic')

@section('content')
    <div class="login-box">
        <div class="login-logo">
            <b>{{ $app_name }}</b>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
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

        <div class="login-box-body">
            <p class="login-box-msg">{{ trans('auth.enter_email') }}</p>
            <form action="{{ route('auth.request-password-reset') }}" method="post">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <div class="form-group has-feedback">
                    <input type="email" class="form-control" placeholder="{{ trans('auth.email') }}" name="email"  value="{{ old('email') }}" required />
                    <span class="ion ion-email form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-8">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">{{ trans('auth.send_link') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
