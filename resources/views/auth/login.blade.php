@extends('layouts.basic')

@section('content')
    <div class="login-box">
        <div class="login-logo text-success">
            <img src="/img/logo.svg"><strong>{{ $app_name }}</strong>
        </div>

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

        <div class="login-box-body">
            <p class="login-box-msg">{{ trans('auth.please_sign_in') }}</p>
            <form action="{{ route('auth.login') }}" method="post">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <div class="form-group has-feedback">
                    <input type="login" class="form-control" placeholder="{{ trans('auth.login') }}" name="login" value="{{ old('login') }}" required />
                    <span class="fixhub fixhub-user form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="password" class="form-control" placeholder="{{ trans('auth.password') }}" name="password" required />
                    <span class="fixhub fixhub-lock form-control-feedback"></span>
                </div>

                <div class="row">
                    <div class="col-xs-8">
                      <div class="checkbox form-group">
                        <label>
                          <input type="checkbox" name="remember" value="on" />
                                    {{ trans('auth.remember') }}
                        </label>
                      </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-xs-4">
                      <button type="submit" class="btn btn-primary btn-block">{{ trans('auth.sign_in') }}</button>
                    </div>
                    <!-- /.col -->
              </div>
            </form>
            @if(isset($provider_count) && $provider_count > 0)
            <div class="social-auth-links text-center">
              <p>- OR -</p>
              @foreach($providers as $provider)
              <a href="{{ route('oauth.provider', ['provider' => $provider->slug]) }}" class="btn btn-block btn-social btn-{{$provider->slug}}"><i class="fixhub fixhub-cube"></i> {{ trans('auth.oauth_login', ['provider' => $provider->name]) }}</a>
              @endforeach
            </div>
            @endif
        </div>

        <div class="pull-right" id="forgotten-password">
            <p><a href="{{ route('auth.reset-password') }}">{{ trans('auth.forgotten') }}</a></p>
        </div>

    </div>
@stop
