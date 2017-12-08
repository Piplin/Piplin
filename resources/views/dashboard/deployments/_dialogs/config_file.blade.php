<div class="modal fade" id="configfile" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="piplin piplin-config-file"></i> <span>{{ trans('configFiles.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="config_file_id" name="id" />
                <input type="hidden" name="targetable_type" value="{{ $targetable_type }}" />
                <input type="hidden" name="targetable_id" value="{{ $targetable_id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon piplin piplin-warning"></i> {{ trans('configFiles.warning') }}
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="name">{{ trans('configFiles.name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="name" name="name" placeholder="{{ trans('configFiles.config') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="path">{{ trans('configFiles.path') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="path" name="path" placeholder="config/app.php" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="content">{{ trans('configFiles.content') }}</label>
						<div class="col-sm-9">
							<div class="configfile-content" id="content" class="form-control"></div>
						</div>
                    </div>
                    @if(count($environments))
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="command_environments">{{ trans('configFiles.environments') }}</label>
                        <div class="col-sm-9">
                        <ul class="list-unstyled">
                            @foreach ($environments as $each)
                            <li>
                                <div class="checkbox">
                                    <label for="configfile_environment_{{ $each->id }}">
                                        <input type="checkbox" class="configfile-environment" name="environments[]" id="configfile_environment_{{ $each->id }}" value="{{ $each->id }}" @if ($each->default_on === true) checked @endif/> {{ $each->name }}
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
                        <button type="button" class="btn btn-primary btn-save"><i class="piplin piplin-save"></i> {{ trans('app.save') }}</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="view-configfile" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="piplin piplin-preview"></i> <span>{{ trans('configFiles.view') }}</span></h4>
            </div>
            <div class="modal-body" id="preview-content">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sync-configfile" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="piplin piplin-preview"></i> <span>{{ trans('configFiles.sync') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
            <input type="hidden" id="sync-configfile_id" name="id" />
            <div class="modal-body">
                @if(count($environments))
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="sync_environments">{{ trans('configFiles.environments') }}</label>
                    <div class="col-sm-9">
                        <ul class="list-unstyled">
                            @foreach ($environments as $each)
                            <li>
                                <div class="checkbox">
                                    <label for="sync_environment_{{ $each->id }}">
                                        <input type="checkbox" class="sync-environment" name="environments[]" id="sync_environment_{{ $each->id }}" value="{{ $each->id }}" @if ($each->default_on === true) checked @endif/> {{ $each->name }}
                                    </label>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="task_reason">{{ trans('configFiles.post_commands') }}</label>
                    <div class="col-sm-9">
                        <div id="command_script" class="form-control"></div>
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