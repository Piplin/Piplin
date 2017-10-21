<div class="modal fade" id="tip">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="fixhub fixhub-tip"></i> <span>{{ trans('tips.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="tip_id" name="id" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon fixhub fixhub-warning"></i> {{ trans('tips.warning') }}
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('tips.body') }}</label>
						<div class="col-sm-9">
							<textarea name="body" rows="10" id="tip_body" class="form-control" placeholder="{{ trans('tips.body') }}"></textarea>
						</div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('tips.status') }}</label>
                        <div class="col-sm-9 checkbox">
                            <label for="tip_status">
                                <input type="checkbox" value="1" name="status" id="tip_status" />
                                {{ trans('tips.enabled') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-save">{{ trans('app.save') }}</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
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
                <h4 class="modal-title"><i class="fixhub fixhub-tip"></i> {{ trans('tips.body') }}</h4>
            </div>
            <div class="modal-body">
                <div id="tip_preview">loading</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.close') }}</button>
            </div>
        </div>
    </div>
</div>
