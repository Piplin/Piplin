<div class="modal fade" id="variable">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-plus"></i> <span>{{ trans('variables.create') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="variable_id" name="id" />
                <input type="hidden" name="targetable_type" value="{{ $targetable_type }}" />
                <input type="hidden" name="targetable_id" value="{{ $targetable_id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('variables.warning') }}
                    </div>

                    <div class="form-group">
                        <label for="variable_name">{{ trans('variables.name') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-pricetag"></i></div>
                            <input type="text" class="form-control" id="variable_name" name="name" placeholder="COMPOSER_PROCESS_TIMEOUT" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="variable_value">{{ trans('variables.value') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-social-usd"></i></div>
                            <input type="text" class="form-control" id="variable_value" name="value" placeholder="300" />
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
