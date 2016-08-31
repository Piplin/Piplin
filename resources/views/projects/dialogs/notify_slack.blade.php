<div class="modal fade" id="notifyslack">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-paper-airplane"></i> <span>{{ trans('notifySlacks.create') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="notifyslack_id" name="id" />
                <input type="hidden" name="project_id" value="{{ $project->id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('notifySlacks.warning') }}
                    </div>

                    <div class="form-group">
                        <label for="notifyslack_name">{{ trans('notifySlacks.name') }}</label>
                        <input type="text" class="form-control" id="notifyslack_name" name="name" placeholder="{{ trans('notifySlacks.bot') }}" />
                    </div>
                    <div class="form-group">
                        <label for="notifyslack_icon">{{ trans('notifySlacks.icon') }}</label>
                        <i class="ion ion-help" data-toggle="tooltip" data-placement="right" title="{{ trans('notifySlacks.icon_info') }}"></i>
                        <input type="text" class="form-control" id="notifyslack_icon" name="icon" placeholder=":ghost:" />
                    </div>
                    <div class="form-group">
                        <label for="notifyslack_channel">{{ trans('notifySlacks.channel') }}</label>
                        <input type="text" class="form-control" id="notifyslack_channel" name="channel" placeholder="#slack" />
                    </div>
                    <div class="form-group">
                        <label for="notifyslack_webhook">{{ trans('notifySlacks.webhook') }}</label>
                        <input type="text" class="form-control" id="notifyslack_webhook" name="webhook" placeholder="https://hooks.slack.com/services/" />
                    </div>
                    <div class="form-group">
                        <label>{{ trans('notifySlacks.failure_only') }}</label>
                        <div class="checkbox">
                            <label for="notifyslack_failure_only">
                                <input type="checkbox" value="1" name="failure_only" id="notifyslack_failure_only" />
                                {{ trans('notifySlacks.failure_description') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-left btn-save">{{ trans('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
