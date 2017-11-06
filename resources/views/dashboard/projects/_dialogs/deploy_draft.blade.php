<div class="modal modal-default fade" id="deploy_draft" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="fixhub fixhub-check"></i> {{ trans('deployments.draft_title') }}</h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" name="deployment_id" />
                <div class="modal-body">
                    {{ trans('deployments.draft_warning') }}
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-save"><i class="fixhub fixhub-save"></i> {{ trans('projects.deploy') }}</button>
                         <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>