<div class="modal fade" id="sharedfile" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="piplin piplin-shared-file"></i> <span>{{ trans('sharedFiles.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="sharedfile_id" name="id" />
                <input type="hidden" name="targetable_type" value="{{ $targetable_type }}" />
                <input type="hidden" name="targetable_id" value="{{ $targetable_id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon piplin piplin-warning"></i> {{ trans('sharedFiles.warning') }}
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="name">{{ trans('sharedFiles.name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="name" name="name" placeholder="{{ trans('sharedFiles.cache') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="file">{{ trans('sharedFiles.file') }} <i class="piplin piplin-info" data-toggle="tooltip" data-placement="right" title="{{ trans('sharedFiles.example') }}"></i></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="file" name="file" placeholder="storage/" />
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
