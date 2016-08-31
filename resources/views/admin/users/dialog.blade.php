<div class="modal fade" id="user">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-android-person-add"></i> <span>{{ trans('users.add') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="user_id" name="id" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('users.warning') }}
                    </div>

                    <div class="form-group">
                        <label for="user_name">{{ trans('users.name') }}</label>
                        <input type="text" class="form-control" name="name" id="user_name" placeholder="Demo" />
                    </div>

                    <div class="form-group">
                        <label for="user_nickname">{{ trans('users.nickname') }}</label>
                        <input type="text" class="form-control" name="nickname" id="user_nickname" placeholder="John Smith" />
                    </div>

                    <div class="form-group">
                        <label for="user_email">{{ trans('users.email') }}</label>
                        <input type="email" class="form-control" name="email" id="user_email" placeholder="john.smith@example.net" />
                    </div>

                    <div class="form-group">
                        <label for="user_password" class="user_password existing-only">{{ trans('users.password_existing') }}</label>
                        <label for="user_password" class="new-only">{{ trans('users.password') }}</label>
                        <input type="password" class="form-control" name="password" id="user_password" />
                    </div>

                    <div class="form-group new-only">
                        <label for="user_password_confirmation">{{ trans('users.password_confirm') }}</label>
                        <input type="password" class="form-control" name="password_confirmation" id="user_password_confirmation" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-left btn-save">{{ trans('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
