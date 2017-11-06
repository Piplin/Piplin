<div class="modal fade" id="user" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="fixhub fixhub-user"></i> <span>{{ trans('users.add') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="user_id" name="id" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon fixhub fixhub-warning"></i> {{ trans('users.warning') }}
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="user_name">{{ trans('users.name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="name" id="user_name" placeholder="Demo" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="user_level">{{ trans('users.role') }}</label>
                        <div class="col-sm-9">
                            <select id="user_level" name="level" class="form-control">
                                @foreach($levels as $level => $role)
                                    <option value="{{ $level }}">{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="user_nickname">{{ trans('users.nickname') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="nickname" id="user_nickname" placeholder="John Smith" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="user_email">{{ trans('users.email') }}</label>
                        <div class="col-sm-9">
                            <input type="email" class="form-control" name="email" id="user_email" placeholder="john.smith@example.net" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="user_password">{{ trans('users.password') }}</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" name="password" id="user_password" />
							<label class="existing-only">{{ trans('users.password_existing') }}</label>
                        </div>
                    </div>

                    <div class="form-group new-only">
                        <label class="col-sm-3 control-label" for="user_password_confirmation">{{ trans('users.password_confirm') }}</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" name="password_confirmation" id="user_password_confirmation" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-save"><i class="fixhub fixhub-save"></i> {{ trans('app.save') }}</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
