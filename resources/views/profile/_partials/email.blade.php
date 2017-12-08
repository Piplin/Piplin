<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('users.change_email') }}</h3>
    </div>
    <div class="box-body">
        <div class="form-group">
            <label for="email">{{ trans('users.email') }}</label>
            <p class="form-control bg-gray">{{ $current_user->email }}</p>
        </div>
        <div class="form-group">
            <button type="button" class="btn btn-warning btn-flat" id="request-change-email">{{ trans('users.request_confirm') }}</button>
            <span class="help-block hide">{{ trans('users.email_sent') }}</span>
        </div>
    </div>
    <div class="overlay hide">
        <i class="piplin piplin-refresh piplin-spin"></i>
    </div>
</div>