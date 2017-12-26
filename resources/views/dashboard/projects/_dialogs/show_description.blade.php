<div class="modal fade" id="show_description" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="piplin piplin-info"></i> {{ trans('projects.description') }}</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <p>{!! nl2br($project->description) !!}</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="btn-group">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.close') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
