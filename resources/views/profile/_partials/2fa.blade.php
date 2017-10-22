<div class="box box-default">
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