<div class="modal fade" id="command" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="piplin piplin-command"></i> <span>{{ trans('commands.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="command_id" name="id" />
                <input type="hidden" name="targetable_type" value="{{ $targetable_type }}" />
                <input type="hidden" name="targetable_id" value="{{ $targetable_id }}" />
                <input type="hidden" id="command_step" name="step" value="After" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon piplin piplin-warning"></i> {{ trans('commands.warning') }}
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="command_name">{{ trans('commands.name') }}</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" name="name" id="command_name" placeholder="{{ trans('commands.migrations') }}" />
						</div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="command_user">{{ trans('commands.run_as') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="user" id="command_user" placeholder="{{ trans('commands.default') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="command_script">{{ trans('commands.bash') }}</label>
						<div class="col-sm-9">
                        <div id="command_script" class="form-control"></div>
                        <h5><a data-toggle="collapse" data-parent="#accordion" href="#tokens">{{ trans('commands.options') }}</a></h5>

                        <div class="panel-collapse collapse" id="tokens">
                            <ul class="list-unstyled">
                                <li><code>@{{ project_path }}</code> - {{ trans('commands.project_path') }}, {{ trans('commands.example') }} <span class="label label-default">/var/www</span></li>
                                @if($targetable_type == 'Piplin\\Models\\BuildPlan')
                                <li><code>@{{ build }}</code> - {{ trans('commands.release_id') }}, {{ trans('commands.example') }} <span class="label label-default">{{ date('YmdHis') }}</span></li>
                                <li><code>@{{ build_path }}</code> - {{ trans('commands.release_path') }}, {{ trans('commands.example') }} <span class="label label-default">/var/www/builds/{{ date('YmdHis') }}</span></li>
                                @else
                                <li><code>@{{ release }}</code> - {{ trans('commands.release_id') }}, {{ trans('commands.example') }} <span class="label label-default">{{ date('YmdHis') }}</span></li>
                                <li><code>@{{ release_path }}</code> - {{ trans('commands.release_path') }}, {{ trans('commands.example') }} <span class="label label-default">/var/www/releases/{{ date('YmdHis') }}</span></li>
                                <li><code>@{{ author_email }}</code> - {{ trans('commands.author_email') }}, {{ trans('commands.example') }} <span class="label label-default">{{ $current_user->email }}</span></li>
                                <li><code>@{{ author_name }}</code> - {{ trans('commands.author_name') }}, {{ trans('commands.example') }} <span class="label label-default">{{ $current_user->name }}</span></li>
                                @endif
                                <li><code>@{{ committer_email }}</code> - {{ trans('commands.committer_email') }}, {{ trans('commands.example') }} <span class="label label-default">piplin@piplin.com</span></li>
                                <li><code>@{{ committer_name }}</code> - {{ trans('commands.committer_name') }}, {{ trans('commands.example') }} <span class="label label-default">Piplin</span></li>
                                <li><code>@{{ branch }}</code> - {{ trans('commands.branch') }}, {{ trans('commands.example') }} <span class="label label-default">master</span></li>
                                <li><code>@{{ sha }}</code> - {{ trans('commands.sha') }}, {{ trans('commands.example') }} <span class="label label-default">1def37e6f6fd15c50efe53e090308861ec8a8288</span></li>
                                <li><code>@{{ short_sha }}</code> - {{ trans('commands.short_sha') }}, {{ trans('commands.example') }} <span class="label label-default">1def37e</span></li>
                            </ul>
                        </div>
						</div>
                    </div>
                    @if (count($targetable->environments))
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="command_environments">{{ trans('commands.environments') }}</label>
						<div class="col-sm-9">
                        <ul class="list-unstyled">
                            @foreach ($targetable->environments as $environment)
                            <li>
                                <div class="checkbox">
                                    <label for="command_environment_{{ $environment->id }}">
                                        <input type="checkbox" class="command-environment" name="environments[]" id="command_environment_{{ $environment->id }}" value="{{ $environment->id }}" /> {{ $environment->name }}
                                    </label>
                                </div>
                            </li>
                            @endforeach
                        </ul>
						</div>
                    </div>
                    @endif
                    @if (count($targetable->patterns))
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="command_patterns">{{ trans('commands.patterns') }}</label>
                        <div class="col-sm-9">
                        <ul class="list-unstyled">
                            @foreach ($targetable->patterns as $pattern)
                            <li>
                                <div class="checkbox">
                                    <label for="command_pattern_{{ $pattern->id }}">
                                        <input type="checkbox" class="command-pattern" name="patterns[]" id="command_pattern_{{ $pattern->id }}" value="{{ $pattern->id }}" /> {{ $pattern->name }}
                                    </label>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        </div>
                    </div>
                    @endif
                    <div class="form-group">
                        <label class="col-sm-2 control-label">{{ trans('commands.optional') }}</label>
						<div class="col-sm-9">
							<div class="checkbox">
								<label for="command_optional">
									<input type="checkbox" value="1" name="optional" id="command_optional" />
									{{ trans('commands.optional_description') }}
								</label>
							</div>

							<div class="checkbox hide" id="command_default_on_row">
								<label for="command_default_on">
									<input type="checkbox" value="1" name="default_on" id="command_default_on" />
									{{ trans('commands.default_description') }}
								</label>
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
