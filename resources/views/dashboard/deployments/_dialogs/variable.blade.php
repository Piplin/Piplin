<div class="modal fade" id="variable" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="piplin piplin-plus"></i> <span>{{ trans('variables.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="variable_id" name="id" />
                <input type="hidden" name="targetable_type" value="{{ $targetable_type }}" />
                <input type="hidden" name="targetable_id" value="{{ $targetable_id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon piplin piplin-warning"></i> {{ trans('variables.warning') }}
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="variable_name">{{ trans('variables.name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="variable_name" name="name" placeholder="COMPOSER_PROCESS_TIMEOUT" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="variable_value">{{ trans('variables.value') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="variable_value" name="value" placeholder="300" />
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
