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
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-pricetag"></i></div>
                            <input type="text" class="form-control" name="name" id="user_name" placeholder="Demo" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="user_level">{{ trans('users.role') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-person-stalker"></i></div>
                            <select id="user_level" name="level" class="select2 form-control">
                                @foreach($levels as $level => $role)
                                    <option value="{{ $level }}">{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="user_nickname">{{ trans('users.nickname') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-android-bookmark"></i></div>
                            <input type="text" class="form-control" name="nickname" id="user_nickname" placeholder="John Smith" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="user_email">{{ trans('users.email') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-email"></i></div>
                            <input type="email" class="form-control" name="email" id="user_email" placeholder="john.smith@example.net" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="user_password" class="user_password existing-only">{{ trans('users.password_existing') }}</label>
                        <label for="user_password" class="new-only">{{ trans('users.password') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-unlocked"></i></div>
                            <input type="password" class="form-control" name="password" id="user_password" />
                        </div>
                    </div>

                    <div class="form-group new-only">
                        <label for="user_password_confirmation">{{ trans('users.password_confirm') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-locked"></i></div>
                            <input type="password" class="form-control" name="password_confirmation" id="user_password_confirmation" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group pull-left">
                        <button type="button" class="btn btn-primary btn-save">{{ trans('app.save') }}</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
