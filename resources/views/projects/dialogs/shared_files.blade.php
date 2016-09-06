<div class="modal fade" id="sharefile">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-document"></i> <span>{{ trans('sharedFiles.create') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="file_id" name="id" />
                <input type="hidden" name="targetable_type" value="{{ $targetable_type }}" />
                <input type="hidden" name="targetable_id" value="{{ $targetable_id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('sharedFiles.warning') }}
                    </div>

                    <div class="form-group">
                        <label for="name">{{ trans('sharedFiles.name') }}</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="{{ trans('sharedFiles.cache') }}" />
                    </div>
                    <div class="form-group">
                        <label for="file">{{ trans('sharedFiles.file') }}</label>
                        <i class="ion ion-help" data-toggle="tooltip" data-placement="right" title="{{ trans('sharedFiles.example') }}"></i>
                        <input type="text" class="form-control" id="file" name="file" placeholder="storage/" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-left btn-save">{{ trans('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
