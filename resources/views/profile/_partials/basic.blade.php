<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('users.basic') }}</h3>
    </div>
    <div class="box-body">
        <form action="{{ route('profile.update') }}" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <div class="form-group">
                <label for="name">{{ trans('users.name') }}</label>
                <p class="form-control bg-gray">{{ $current_user->name }}</p>
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