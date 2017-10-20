<div class="modal fade" id="project">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-social-codepen-outline"></i> <span>{{ trans('projects.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="project_id" name="id" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('projects.warning') }}
                    </div>

                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#project_details" data-toggle="tab">{{ trans('projects.details') }}</a></li>
                            <li><a href="#project_repo" data-toggle="tab">{{ trans('projects.repository') }}</a></li>
                            <li><a href="#project_build" data-toggle="tab">{{ trans('projects.build_options') }}</a></li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane active" id="project_details">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="project_name">{{ trans('projects.name') }}</label>
                                    <div class="col-sm-9">
                                    <input type="text" class="form-control" name="name" id="project_name" placeholder="{{ trans('projects.name_placeholder') }}" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="project_group_id">{{ trans('projects.group') }}</label>
                                    <div class="col-sm-9">
                                    <select id="project_group_id" name="group_id" class="form-control">
                                        @foreach($groups as $item)
                                            <option value="{{ $item->id }}" {!! isset($group) && $group->id == $item->id ? 'selected="selected"' : NULL !!}>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                                @if (count($templates) > 0)
                                <div class="form-group" id="template-list">
                                    <label class="col-sm-3 control-label" for="project_template_id">{{ trans('templates.type') }}</label>
                                    <div class="col-sm-9">
                                    <select id="project_template_id" name="template_id" class="select2 form-control">
                                        <option value="">{{ trans('templates.custom') }}</option>
                                        @foreach ($templates as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                                @endif
								<div class="form-group">
                                    <label class="col-sm-3 control-label" for="project_url">{{ trans('projects.url') }}</label>
                                    <div class="col-sm-9">
                                    <input type="text" class="form-control" name="url" id="project_url" placeholder="http://www.example.com" />
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="project_repo">
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

                            <div class="tab-pane" id="project_build">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="project_key_id">{{ trans('projects.key') }}</label>
                                    <div class="col-sm-9">
                                    <select id="project_key_id" name="key_id" class="select2 form-control">
                                        @foreach($keys as $key)
                                            <option value="{{ $key->id }}">{{ $key->name }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="project_builds_to_keep">{{ trans('projects.builds') }}</label>
                                    <div class="col-sm-9">
                                    <input type="number" class="form-control" name="builds_to_keep" min="1" max="20" id="project_builds_to_keep" placeholder="10" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="project_build_url">{{ trans('projects.image') }} <i class="ion ion-help-buoy" data-toggle="tooltip" data-placement="right" title="{{ trans('projects.ci_image') }}"></i>
									</label>
                                    <div class="col-sm-9">
										<input type="text" class="form-control" name="build_url" id="project_build_url" placeholder="http://ci.example.com/status.png?project=1" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-save">{{ trans('app.save') }}</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="project-clone">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-ios-browsers-outline"></i> <span>{{ trans('projects.clone') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form" method="post">
                <input type="hidden" id="skeleton_id" name="id" />
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('projects.warning') }}
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="group_name">{{ trans('projects.clone_name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="name" id="project_clone_name" placeholder="{{ trans('groups.name') }}" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="project_key_id">{{ trans('projects.clone_type') }}</label>
                        <div class="col-sm-9">
                        <select id="project_extract_type" name="type" class="select2 form-control">
							<option value="project">{{ trans('projects.clone_duplicate') }}</option>
							<option value="template">{{ trans('projects.clone_convert') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary btn-save">{{ trans('app.save') }}</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>