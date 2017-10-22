<div class="modal fade" id="group">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="fixhub fixhug-group"></i> <span>{{ trans('groups.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="group_id" name="id" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon fixhub fixhub-warning"></i> {{ trans('groups.warning') }}
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="group_name">{{ trans('groups.name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="name" id="group_name" placeholder="{{ trans('groups.name_placeholder') }}" />
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