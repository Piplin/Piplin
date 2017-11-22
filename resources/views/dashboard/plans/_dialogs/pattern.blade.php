<div class="modal fade" id="pattern" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="piplin piplin-pattern"></i> <span>{{ trans('patterns.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="pattern_id" name="id" />
                <input type="hidden" name="plan_id" value="{{ $targetable_id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon piplin piplin-warning"></i> {{ trans('patterns.warning') }}
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="name">{{ trans('patterns.name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="name" name="name" placeholder="{{ trans('patterns.name_placeholder') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="copy_pattern">{{ trans('patterns.copy_pattern') }} <i class="piplin piplin-help" data-toggle="tooltip" data-placement="right" title="{{ trans('patterns.example') }}"></i></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="copy_pattern" name="copy_pattern" placeholder="**/*.tar.gz" />
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
