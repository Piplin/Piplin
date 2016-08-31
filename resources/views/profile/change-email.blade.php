@extends('layouts.basic')

@section('content')
    <div class="login-box">
        <div class="login-logo">
            <b>{{ trans('app.name') }}</b>
        </div>
        <div class="login-box-body">
            <p class="login-box-msg">{{ trans('users.enter_email') }}</p>
            <form action="{{ route('profile.change-email') }}" method="post">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-group has-feedback">
                    <input type="email" class="form-control" placeholder="{{ trans('users.email') }}" name="email"  value="{{ old('email') }}" required />
                    <span class="ion ion-email form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-8">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">{{ trans('users.save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
