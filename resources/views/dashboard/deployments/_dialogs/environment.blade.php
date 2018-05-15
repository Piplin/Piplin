<div class="modal fade" id="environment" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="piplin piplin-environment"></i> <span>{{ trans('environments.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="environment_id" name="id" />
                <input type="hidden" name="targetable_type" value="{{ $targetable_type }}" />
                <input type="hidden" name="targetable_id" value="{{ $targetable_id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="piplin piplin-warning"></i> {{ trans('environments.warning') }}
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="environment_name">{{ trans('environments.name') }} <i class="piplin piplin-help text-gray" data-toggle="tooltip" data-placement="right" title="{{ trans('environments.example') }}"></i></label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="environment_name" name="name" placeholder="{{ trans('environments.name_placeholder') }}" />
						</div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="environment_description">{{ trans('environments.description') }}</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="environment_description" name="description" placeholder="{{ trans('environments.desc_placeholder') }}" />
						</div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('environments.optional') }}</label>
						<div class="col-sm-9">
							<div class="checkbox">
								<label for="environment_default_on">
									<input type="checkbox" value="1" name="default_on" id="environment_default_on" />
									{{ trans('environments.default_description') }}
								</label>
							</div>
							@if (Route::currentRouteName() == 'projects' && $project->deployPlan->commands->count() > 0)
							<div class="checkbox" id="add-environment-command">
								<label class="control-label" for="environment_commands">
									<input type="checkbox" value="1" name="commands" id="environment_commands" checked />
									{{ trans('environments.add_command') }}
								</label>
							</div>
							@endif
						</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-save"><i class="piplin piplin-save"></i> {{ trans('app.save') }}</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
