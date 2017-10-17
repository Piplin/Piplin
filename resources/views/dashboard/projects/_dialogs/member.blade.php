<div class="modal fade" id="member">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-social-buffer-outline"></i> <span>{{ trans('members.create') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="member_id" name="id" />
                <input type="hidden" name="project_id" value="{{ $project->id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('members.warning') }}
                    </div>
                    <div class="form-group">
                        <label for="member_users">{{ trans('members.users') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-android-person"></i></div>
                            <select class="form-control project-members" id="member_user_id" name="user_id"></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="user_level">{{ trans('members.level') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-ios-locked"></i></div>
                            <select id="member_level" name="level" class="form-control">
                                @foreach([1,2,3] as $level)
                                    <option value="{{ $level }}">{{ $level }}</option>
                                @endforeach
                            </select>
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
