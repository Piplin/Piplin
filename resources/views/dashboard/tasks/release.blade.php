<div class="modal fade" id="release" tabindex="-1"> 
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="piplin piplin-release"></i> {{ trans('releases.create') }}</h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" name="project_id" value="{{ $task->project_id }}" />
                <input type="hidden" name="task_id" value="{{ $task->id }}" />
                <div class="modal-body">
                    <div class="callout callout-danger">
                        <i class="icon piplin piplin-warning"></i> {{ trans('releases.warning') }}
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="release_name">{{ trans('releases.name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="name" id="release_name" placeholder="{{ trans('releases.name_placeholder') }}" value="{{ isset($release_name) ? $release_name : null }}" />
                        </div>
                    </div>
                </div>
            </form>
            <div class="modal-footer">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-save"><i class="piplin piplin-save"></i> <span>{{ trans('app.save') }}</span></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
