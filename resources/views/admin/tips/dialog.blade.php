<div class="modal fade" id="tip">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-ios-browsers-outline"></i> <span>{{ trans('tips.create') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="tip_id" name="id" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('tips.warning') }}
                    </div>

                    <div class="form-group">
                        <label>{{ trans('tips.body') }}</label>
                        <i class="ion ion-help" data-toggle="tooltip" data-placement="right" title="{{ trans('tips.body_info') }}"></i>
                        <textarea name="body" rows="10" id="tip_body" class="form-control" placeholder="{{ trans('tips.body') }}"></textarea>
                    </div>
                    <div class="form-group">
                        <label>{{ trans('tips.status') }}</label>
                        <div class="checkbox">
                            <label for="project_status">
                                <input type="checkbox" value="1" name="status" id="project_status" />
                                {{ trans('tips.enabled') }}
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

<div class="modal fade" id="show_tip">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-ios-lightbulb-outline"></i> {{ trans('tips.body') }}</h4>
            </div>
            <div class="modal-body">
                <div id="tip_preview">loading</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('app.close') }}</button>
            </div>
        </div>
    </div>
</div>
