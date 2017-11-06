<div class="modal fade" id="link" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="fixhub fixhub-link"></i> <span>{{ trans('links.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="link_id" name="id" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon fixhub fixhub-warning"></i> {{ trans('links.warning') }}
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="link_title">{{ trans('links.title') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="title" id="link_title" placeholder="{{ trans('links.title') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="link_url">{{ trans('links.url') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="url" id="link_url" placeholder="{{ trans('links.url') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('links.description') }}</label>
						<div class="col-sm-9">
							<textarea name="description" rows="3" id="link_description" class="form-control" placeholder="{{ trans('links.description') }}"></textarea>
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
