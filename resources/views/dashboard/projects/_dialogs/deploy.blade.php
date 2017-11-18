<div class="modal fade" id="deploy" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="fixhub fixhub-deploy"></i> {{ trans('projects.deploy_project') }}</h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" name="project_id" value="{{ $project->id }}" />
                <input type="hidden" name="targetable_type" value="{{ $targetable_type }}" />
                <input type="hidden" name="targetable_id" value="{{ $targetable_id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon fixhub fixhub-warning"></i> {{ trans('deployments.warning') }}
                    </div>
                    @if (count($environments))
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="environments">{{ trans('deployments.environments') }}</label>
						<div class="col-sm-9">
                        <ul class="list-unstyled">
                            @foreach ($environments as $each)
                            <li>
                                <div class="checkbox">
                                    <label for="deployment_environment_{{ $each->id }}">
                                        <input type="checkbox" class="deployment-environment" name="environments[]" id="deployment_environment_{{ $each->id }}" value="{{ $each->id }}" @if ($each->default_on === true) checked @endif/> {{ $each->name }}
                                    </label>
                                </div>
                            </li>
                            @endforeach
                        </ul>
						</div>
                    </div>
                    @endif
                    @if ($project->allow_other_branch && (count($branches) || count($tags)))
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="deployment_source">{{ trans('deployments.source') }}</label>
						<div class="col-sm-9">
							<ul class="list-unstyled">
								<li>
									<div class="radio">
										<label for="deployment_source_default">
											<input type="radio" class="deployment-source" name="source" id="deployment_source_default" value="{{ $project->branch }}" checked /> {{ trans('deployments.default', [ 'branch' => $project->branch ]) }}
										</label>
									</div>
								</li>

								@if (count($branches))
								<li>
									<div class="radio">
										<label for="deployment_source_branch">
											<input type="radio" class="deployment-source" name="source" id="deployment_source_branch" value="branch" /> {{ trans('deployments.different_branch') }}

											<div class="deployment-source-container">
												<select class="form-control deployment-source" name="source_branch" id="deployment_branch">
													@foreach ($branches as $branch)
														<option value="{{ $branch }}">{{ $branch }}</option>
													@endforeach
												</select>
											</div>
										</label>
									</div>
								</li>
								@endif

								@if (count($tags))
								<li>
									<div class="radio">
										<label for="deployment_source_tag">
											<input type="radio" class="deployment-source" name="source" id="deployment_source_tag" value="tag" /> {{ trans('deployments.tag') }}

											<div class="deployment-source-container">
												<select class="form-control deployment-source" name="source_tag" id="deployment_tag">
													@foreach ($tags as $tag)
														<option value="{{ $tag }}">{{ $tag }}</option>
													@endforeach
												</select>
											</div>
										</label>
									</div>
								</li>
								@endif
								<li>
									<div class="radio">
										<label for="deployment_source_commit">
											<input type="radio" class="deployment-source" name="source" id="deployment_source_commit" value="commit" /> {{ trans('deployments.commit') }}

											<div class="deployment-source-container">
												<input class="form-control deployment-source" name="source_commit" id="deployment_commit" placeholder="{{ trans('deployments.describe_commit') }}">
											</div>
										</label>
									</div>
								</li>
							</ul>
						</div>
                    </div>
                    <hr />
                    @endif
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="deployment_reason">{{ trans('deployments.reason') }}</label>
						<div class="col-sm-9">
							<textarea rows="3" id="deployment_reason" class="form-control" name="reason" placeholder="{{ trans('deployments.describe_reason') }}"></textarea>
						</div>
                    </div>
                    @if (count($optional))
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="deployment_commands">{{ trans('deployments.optional') }}</label>
						<div class="col-sm-9">
                        <ul class="list-unstyled">
                            @foreach ($optional as $command)
                            <li>
                                <div class="checkbox">
                                    <label for="deployment_command_{{ $command->id }}">
                                        <input type="checkbox" class="deployment-command" name="optional[]" id="deployment_command_{{ $command->id }}" value="{{ $command->id }}" @if ($command->default_on === true) checked @endif/> {{ $command->name }}
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
                        <button type="button" class="btn btn-primary btn-save"><i class="fixhub fixhub-save"></i> {{ trans('projects.deploy') }}</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
