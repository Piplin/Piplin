<div class="modal fade" id="trigger">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="fa fa-bullhorn"></i> <span>{{ trans('triggers.create') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="trigger_id" name="id" />
                <input type="hidden" name="project_id" value="{{ $project->id }}" />
                <input type="hidden" name="type" id="trigger_type" value="" />
                <div class="modal-body">
                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('triggers.warning') }}
                    </div>

                    <div class="callout callout-warning">
                        <h4><i class="icon ion ion-android-remove-circle"></i> {{ trans('triggers.not_configured_title') }}</h4>
                        {{ trans('triggers.not_configured') }}
                    </div>

                    <div id="trigger-type">
                        <p>{{ trans('triggers.which') }}</p>
                        <div class="row text-center callout">
                            <a class="btn btn-block btn-app bg-navy" data-type="schedule">
                            <span class="badge bg-yellow">Schedule</span>
                            <i class="ion ion-clock"></i> Run According to schedule</a>
                            <a class="btn btn-block btn-app bg-purple" data-type="daily">
                            <span class="badge bg-green">Daily</span>
                            <i class="ion ion-calendar"></i> Run once a day</a>
                        </div>
                    </div>

                    <div class="trigger-config form-group" id="trigger-name">
                        <label for="trigger_name">{{ trans('triggers.name') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-pricetag"></i></div>
                            <input type="text" class="form-control" id="trigger_name" name="name" placeholder="{{ trans('triggers.name') }}" />
                        </div>
                    </div>

                    @include('dashboard.projects.dialogs.triggers.schedule')
                    @include('dashboard.projects.dialogs.triggers.daily')

                    <div class="trigger-enabled from-group">
                        <label for="trigger_name">{{ trans('triggers.enabled') }}</label>
                         <div class="checkbox">
                            <label for="trigger_enabled">
                                <input type="checkbox" value="1" name="enabled" id="trigger_enabled" />
                                {{ trans('triggers.enabled') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-left btn-save"><i class="fa fa-save"></i> {{ trans('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>