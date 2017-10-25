<div class="modal fade" id="hook">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="fixhub fixhub-plus"></i> <span>{{ trans('hooks.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="hook_id" name="id" />
                <input type="hidden" name="project_id" value="{{ $project->id }}" />
                <input type="hidden" name="type" id="hook_type" value="" />
                <div class="modal-body">
                    <div class="callout callout-danger">
                        <i class="icon fixhub fixhub-warning"></i> {{ trans('hooks.warning') }}
                    </div>

                    <div class="callout callout-warning">
                        <h4><i class="icon fixhub fixhub-disabled"></i> {{ trans('hooks.not_configured_title') }}</h4>
                        {{ trans('hooks.not_configured') }}
                    </div>

                    <div id="hook-type">
                        <p>{{ trans('hooks.which') }}</p>
                        <div class="row text-center">
                            <a class="btn btn-app" data-type="slack"><i class="fixhub fixhub-slack"></i> {{ trans('hooks.slack') }}</a>
                            <a class="btn btn-app" data-type="dingtalk"><i class="fixhub fixhub-pin"></i> {{ trans('hooks.dingtalk') }}</a>
                            <a class="btn btn-app" data-type="mail"><i class="fixhub fixhub-email"></i> {{ trans('hooks.mail') }}</a>
                            <a class="btn btn-app" data-type="custom"><i class="fixhub fixhub-edit"></i> {{ trans('hooks.custom') }}</a>
                        </div>
                    </div>

                    <div class="hook-config form-group" id="hook-name">
                        <label class="col-sm-3 control-label" for="hook_name">{{ trans('hooks.name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="hook_name" name="name" placeholder="{{ trans('hooks.bot') }}" />
                        </div>
                    </div>

                    @include('dashboard.projects._dialogs.hooks.slack')
                    @include('dashboard.projects._dialogs.hooks.dingtalk')
                    @include('dashboard.projects._dialogs.hooks.mail')
                    @include('dashboard.projects._dialogs.hooks.custom')

                    <div class="hook-config form-group" id="hook-triggers">
                        <label class="col-sm-3 control-label">{{ trans('hooks.triggers') }}</label>
                        <div class="col-sm-9">
                            <div class="checkbox">
                                <label for="hook_on_deployment_success">
                                    <input type="checkbox" value="1" name="on_deployment_success" id="hook_on_deployment_success" />
                                    {{ trans('hooks.on_deployment_success') }}
                                </label>
                            </div>

                            <div class="checkbox">
                                <label for="hook_on_deployment_failure">
                                    <input type="checkbox" value="1" name="on_deployment_failure" id="hook_on_deployment_failure" />
                                    {{ trans('hooks.on_deployment_failure') }}
                                </label>
                            </div>
                         </div>
                    </div>

                    <div class="hook-enabled form-group">
                        <label class="col-sm-3 control-label" for="hook_enabled">{{ trans('hooks.enabled') }}</label>
                        <div class="col-sm-9">
                            <div class="checkbox">
                                <label for="hook_enabled">
                                    <input type="checkbox" value="1" name="enabled" id="hook_enabled" />{{ trans('hooks.enabled') }}
                                </label>
                            </div>
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