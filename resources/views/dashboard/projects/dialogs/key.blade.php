<div class="modal fade" id="show_key">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-key"></i> {{ trans('keys.public_ssh_key') }}</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <p>{!! trans('keys.server_keys') !!}</p>
                </div>

                <div id="log"><pre>{{ isset($project) ? $project->key->public_key : 'loading' }}</pre></div>

                <div class="alert alert-default">
                     <p>{!! trans('keys.git_keys') !!}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('app.close') }}</button>
            </div>
        </div>
    </div>
</div>
