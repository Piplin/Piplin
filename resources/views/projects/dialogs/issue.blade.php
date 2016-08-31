<div class="modal fade" id="issue">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-ios-information-outline"></i> <span>{{ trans('issues.create') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="issue_id" name="id" />
                <input type="hidden" name="project_id" value="{{ $project->id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('issues.warning') }}
                    </div>

                    <div class="form-group">
                        <label for="issue_title">{{ trans('issues.title') }}</label>
                        <input type="text" class="form-control" id="issue_title" name="title" placeholder="{{ trans('issues.title_placeholder') }}" />
                    </div>
                    <div class="form-group">
                        <label for="issue_content">{{ trans('issues.content') }}</label>
                        <textarea rows="5" id="issue_content" class="form-control" name="content" placeholder="{{ trans('issues.content_placeholder') }}"></textarea>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-left btn-save">{{ trans('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
