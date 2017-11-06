<div class="modal fade" id="project-create" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="fixhub fixhub-project"></i> <span>{{ trans('projects.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="project_id" name="id" />
                <div class="modal-body">
                    <div class="callout callout-danger">
                        <i class="icon fixhub fixhub-warning"></i> {{ trans('projects.warning') }}
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="project_name">{{ trans('projects.name') }}</label>
                        <div class="col-sm-3">
                            <select id="project_targetable_id" name="targetable_id" class="form-control">
                            <option value="">{{ $current_user->name }}</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="name" id="project_name" placeholder="{{ trans('projects.name_placeholder') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="project_repository">{{ trans('projects.repository_path') }}</label>
                        <div class="col-sm-9">
                        <input type="text" class="form-control" name="repository" id="project_repository" placeholder="git&#64;git.example.com:repositories/project.git" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="project_branch">{{ trans('projects.branch') }}</label>
                        <div class="col-sm-9">
                        <input type="text" class="form-control" name="branch" id="project_branch"  placeholder="master" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('projects.options') }}</label>
                        <div class="col-sm-9 checkbox">
                            <label for="project_allow_other_branch">
                                <input type="checkbox" value="1" name="allow_other_branch" id="project_allow_other_branch" />
                                {{ trans('projects.change_branch') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-save"><i class="fixhub fixhub-save"></i> {{ trans('app.save') }}</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>