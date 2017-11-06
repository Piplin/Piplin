<div class="modal fade" id="cabinet" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="fixhub fixhub-cabinet"></i> <span>{{ trans('cabinets.link') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" name="environment_id" value="{{ $targetable->id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="fixhub fixhub-warning"></i> {{ trans('cabinets.warning') }}
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="environment-cabinets">{{ trans('environments.cabinets') }}</label>
                        <div class="col-sm-9">
                            <select class="form-control environment-cabinets" id="cabinet_ids" name="cabinet_ids" multiple="multiple"></select>
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
