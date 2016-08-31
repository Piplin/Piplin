<div class="modal fade" id="group">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-ios-browsers-outline"></i> <span>{{ trans('groups.create') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="group_id" name="id" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('groups.warning') }}
                    </div>

                    <div class="form-group">
                        <label for="group_name">{{ trans('groups.name') }}</label>
                        <input type="text" class="form-control" name="name" id="group_name" placeholder="{{ trans('groups.projects') }}" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-left btn-save">{{ trans('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>