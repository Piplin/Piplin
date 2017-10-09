<div class="modal fade" id="configfile">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion ion-document"></i> <span>{{ trans('configFiles.create') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="config_file_id" name="id" />
                <input type="hidden" name="targetable_type" value="{{ $targetable_type }}" />
                <input type="hidden" name="targetable_id" value="{{ $targetable_id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('configFiles.warning') }}
                    </div>

                    <div class="form-group">
                        <label for="config-file-name">{{ trans('configFiles.name') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-pricetag"></i></div>
                            <input type="text" class="form-control" id="config-file-name" name="config-file-name" placeholder="{{ trans('configFiles.config') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="config-file-path">{{ trans('configFiles.path') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-folder"></i></div>
                            <input type="text" class="form-control" id="config-file-path" name="path" placeholder="config/app.php" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="config-file-content">{{ trans('configFiles.content') }}</label>
                        <div id="config-file-content" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-left btn-save">{{ trans('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="view-configfile">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-eye"></i> <span>{{ trans('configFiles.view') }}</span></h4>
            </div>
            <div class="modal-body" id="preview-content">
            </div>
        </div>
    </div>
</div>