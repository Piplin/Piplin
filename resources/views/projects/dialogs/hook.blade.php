<div class="modal fade" id="hook">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="fa fa-bullhorn"></i> <span>{{ trans('hooks.create') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="hook_id" name="id" />
                <input type="hidden" name="project_id" value="{{ $project->id }}" />
                <input type="hidden" name="type" id="hook_type" value="" />
                <div class="modal-body">
                    <div class="callout callout-danger">
                        <i class="icon fa fa-warning"></i> {{ trans('hooks.warning') }}
                    </div>

                    <div class="callout callout-warning">
                        <h4><i class="icon fa fa-ban"></i> {{ trans('hooks.not_configured_title') }}</h4>
                        {{ trans('hooks.not_configured') }}
                    </div>

                    <div id="hook-type">
                        <p>{{ trans('hooks.which') }}</p>
                        <div class="row text-center">
                            <a class="btn btn-app" data-type="slack"><i class="ion ion-pound"></i> {{ trans('hooks.slack') }}</a>
                            <a class="btn btn-app" data-type="mail"><i class="ion ion-email"></i> {{ trans('hooks.mail') }}</a>
                            <a class="btn btn-app" data-type="custom"><i class="ion ion-compose"></i> {{ trans('hooks.custom') }}</a>
                        </div>
                    </div>

                    <div class="hook-config form-group" id="hook-name">
                        <label for="hook_name">{{ trans('hooks.name') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-pricetag"></i></div>
                            <input type="text" class="form-control" id="hook_name" name="name" placeholder="{{ trans('hooks.bot') }}" />
                        </div>
                    </div>

                    @include('projects.dialogs.hooks.slack')
                    @include('projects.dialogs.hooks.mail')
                    @include('projects.dialogs.hooks.custom')

                    <div class="hook-config form-group" id="hook-triggers">
                        <label>{{ trans('hooks.triggers') }}</label>
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
                    <div class="hook-enabled from-group">
                        <label for="hook_name">{{ trans('hooks.enabled') }}</label>
                         <div class="checkbox">
                            <label for="hook_enabled">
                                <input type="checkbox" value="1" name="enabled" id="hook_enabled" />
                                {{ trans('hooks.enabled') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-save"><i class="fa fa-save"></i> {{ trans('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>