<div class="modal fade" id="project-recover" tabindex="-1" role="dialog" aria-hidden="true"> 
    <div class="modal-dialog"> 
        <div class="modal-content"> 
            <div class="modal-header"> 
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button> 
                <h4 class="modal-title"><i class="piplin piplin-project"></i> <span>{{ trans('app.confirm_title') }}</span></h4> 
            </div> 
            <form role="form">
            <input type="hidden" name="project_id" value="{{ $project->id }}" />
            <div class="modal-body">{{ trans('projects.recover_text') }}</div> 
            <div class="modal-footer"> 
                <div class="btn-group">
                    <button type="button" class="btn btn-warning btn-recover"><i class="piplin piplin-save"></i> {{ trans('app.confirm') }}</button> 
                     <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                </div>
            </div> 
            </form>
        </div><!-- /.modal-content --> 
    </div><!-- /.modal-dialog --> 
</div> 