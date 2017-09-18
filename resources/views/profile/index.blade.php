@extends('layouts.dashboard')

@section('content')
<div class="row edit-profile">
    <div class="col-md-4">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('users.basic') }}</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('profile.update') }}" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <input type="hidden" name="name" value="{{$current_user->name}}" />
                    <div class="form-group">
                        <label for="uid">ID</label>
                        <p class="form-control bg-gray">{{ $current_user->id }}</p>
                    </div>
                    <div class="form-group">
                        <label for="name">{{ trans('users.name') }}</label>
                        <p class="form-control bg-gray">{{ $current_user->name }}</p>
                    </div>
                    <div class="form-group">
                        <label for="role">{{ trans('users.role') }}</label>
                        <p class="form-control bg-gray">{{ $current_user->role_name }}</p>
                    </div>

                    <div class="form-group">
                        <label for="nickname">{{ trans('users.nickname') }}</label>
                        <input type="text" name="nickname" value="{{ $current_user->nickname }}" placeholder="{{ trans('users.nickname') }}" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label for="password">{{ trans('users.password') }}</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="{{ trans('users.password_existing') }}">
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">{{ trans('users.password_confirm') }}</label>
                        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="{{ trans('users.password_existing') }}">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-flat">{{ trans('users.save') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('users.settings') }}</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('profile.settings') }}" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <div class="form-group">
                        <label for="skin">{{ trans('users.theme') }}</label>
                        <select name="skin" id="skin" class="form-control">
                            @foreach (['white', 'black', 'yellow', 'red', 'green', 'purple', 'blue'] as $colour)
                                <option value="{{ $colour }}" @if ($colour === $theme) selected @endif>{{ trans('users.' . $colour )}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="language">{{ trans('users.language') }}</label>
                        <select name="language" id="language" class="form-control">
                            @foreach (['en', 'zh-CN'] as $item)
                                <option value="{{ $item }}" @if ($item === $language) selected @endif>{{ trans('users.' . $item )}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-flat">{{ trans('users.save') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
    <div class="col-md-8">
        <div class="box box-defaut">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('users.avatar') }}</h3>
            </div>
            <div class="box-body">
                <div class="row">

                    <div class="col-md-12 avatar-message">
                        <div class="alert alert-success hide" role="alert">{{ trans('users.avatar_success') }}</div>
                        <div class="alert alert-danger hide" role="alert">{{ trans('users.avatar_failed') }}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="avatar">
                            <img src="{{ url('upload/picture.jpg') }}" class="img-rounded img-responsive" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <img src="{{ $current_user->avatar_url }}" class="current-avatar-preview" />

                        <div class="avatar-preview preview-md hide"></div>

                        <div id="avatar-save-buttons">
                            <button type="button" class="btn btn-primary btn-flat hide" id="save-avatar">{{ trans('users.save') }}</button>
                            <button type="button" class="btn btn-warning btn-flat @if (!$current_user->avatar) hide @endif " id="use-gravatar">{{ trans('users.reset_gravatar') }}</button>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary btn-flat" id="upload">{{ trans('users.upload') }}</button>
                    </div>
                </div>
            </div>
            <div class="overlay" id="upload-overlay">
                <i class="ion ion-refresh fixhub-spin"></i>
            </div>
        </div>

        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('users.2fa') }}</h3>
            </div>
            <div class="box-body">

                @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif

                <form action="{{ route('profile.twofactor') }}" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                    <div class="pull-right auth-code @if (!$current_user->has_two_factor_authentication and !old('google_code')) hide @endif ">
                        <img src="{{ $google_2fa_url }}" id="two-factor-img" class="img-responsive" />
                    </div>

                    <div class="checkbox">
                        <label for="two-factor-auth">
                            <input type="checkbox" id="two-factor-auth" name="two_factor" value="on" @if ($current_user->has_two_factor_authentication or old('google_code')) checked @endif />
                            <strong>{{ trans('users.enable_2fa') }}</strong>
                        </label>

                        <span class="help-block">
                            {!! trans('users.2fa_help') !!}
                        </span>
                    </div>

                    @if (!$current_user->has_two_factor_authentication)
                    <div class="form-group auth-code @if (!old('google_code')) hide @endif">

                        <label for="verify-google-code" style="clear:both">{{ trans('users.verify_code') }}</label>
                        <input type="text" name="2fa_code" placeholder="{{ trans('auth.authentication_code') }}" maxlength="6" class="form-control" id="verify-google-code" />
                        <input type="hidden" name="google_code" value="{{ $google_2fa_code }}" />

                        <span class="help-block">
                            {{ trans('users.verify_help') }}
                        </span>
                    </div>
                    @endif

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-flat">{{ trans('users.save') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('users.change_email') }}</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label for="email">{{ trans('users.email') }}</label>
                    <p class="form-control bg-gray">{{ $current_user->email }}</p>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-danger btn-flat" id="request-change-email">{{ trans('users.request_confirm') }}</button>
                    <span class="help-block hide">{{ trans('users.email_sent') }}</span>
                </div>
            </div>
            <div class="overlay hide">
                <i class="ion ion-refresh fixhub-spin"></i>
            </div>
        </div>
    </div>
</div>
@endsection
