<div class="modal modal-default fade" id="rollback" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="piplin piplin-rollback"></i> {{ trans('tasks.rollback_title') }}</h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" name="task_id" />
                <div class="modal-body">

                    <div class="alert alert-warning">
                        <h4>{{ trans('tasks.caution') }}</h4>
                        <p>{{ trans('tasks.expert') }}</p>
                        <p>{{ trans('tasks.rollback_warning') }}</p>
                    </div>
                    <hr />
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="task_reason">{{ trans('tasks.reason') }}</label>
						<div class="col-sm-9">
							<textarea rows="10" id="task_reason" class="form-control" name="reason" placeholder="{{ trans('tasks.describe_reason') }}"></textarea>
						</div>
                    </div>
                    @if (count($optional))
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="task_commands">{{ trans('tasks.optional') }}</label>
                        <div class="col-sm-9">
                        <ul class="list-unstyled">
                            @foreach ($optional as $command)
                            <li>
                                <div class="checkbox">
                                    <label for="task_command_{{ $command->id }}">
                                        <input type="checkbox" class="task-command" name="optional[]" id="task_command_{{ $command->id }}" value="{{ $command->id }}" @if ($command->default_on === true) checked @endif/> {{ $command->name }}
                                    </label>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-save"><i class="piplin piplin-save"></i> {{ trans('projects.rollback') }}</button>
                         <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
