<div class="modal fade" id="project">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-social-codepen-outline"></i> <span>{{ trans('projects.create') }}</span></h4>
            </div>
            <form role="form">
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
                                    <label for="project_name">{{ trans('projects.name') }}</label>
                                    <div class="input-group">
                                    <div class="input-group-addon"><i class="ion ion-pricetag"></i></div>
                                    <input type="text" class="form-control" name="name" id="project_name" placeholder="{{ trans('projects.name_placeholder') }}" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="project_group_id">{{ trans('projects.group') }}</label>
                                    <div class="input-group">
                                    <div class="input-group-addon"><i class="ion ion-ios-browsers-outline"></i></div>
                                    <select id="project_group_id" name="group_id" class="form-control">
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                                @if (count($templates) > 0)
                                <div class="form-group" id="template-list">
                                    <label for="project_template_id">{{ trans('templates.type') }}</label>
                                    <div class="input-group">
                                    <div class="input-group-addon"><i class="ion ion-ios-paper-outline"></i></div>
                                    <select id="project_template_id" name="template_id" class="form-control">
                                        <option value="">{{ trans('templates.custom') }}</option>
                                        @foreach ($templates as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                                @endif
                                <div class="form-group">
                                    <label for="project_url">{{ trans('projects.url') }}</label>
                                    <div class="input-group">
                                    <div class="input-group-addon"><i class="ion ion-android-open"></i></div>
                                    <input type="text" class="form-control" name="url" id="project_url" placeholder="http://www.example.com" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('projects.options') }}</label>
                                    <div class="checkbox">
                                        <label for="project_need_approve">
                                            <input type="checkbox" value="1" name="need_approve" id="project_need_approve" />
                                            {{ trans('projects.need_approve') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="project_repo">
                                <div class="form-group">
                                    <label for="project_repository">{{ trans('projects.repository_url') }}</label>
                                    <div class="input-group">
                                    <div class="input-group-addon"><i class="ion ion-soup-can-outline"></i></div>
                                    <input type="text" class="form-control" name="repository" id="project_repository" placeholder="git&#64;git.example.com:repositories/project.git" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="project_branch">{{ trans('projects.branch') }}</label>
                                    <div class="input-group">
                                    <div class="input-group-addon"><i class="ion ion-merge"></i></div>
                                    <input type="text" class="form-control" name="branch" id="project_branch"  placeholder="master" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('projects.options') }}</label>
                                    <div class="checkbox">
                                        <label for="project_allow_other_branch">
                                            <input type="checkbox" value="1" name="allow_other_branch" id="project_allow_other_branch" />
                                            {{ trans('projects.change_branch') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="project_build">
                                <div class="form-group">
                                    <label for="project_key_id">{{ trans('projects.key') }}</label>
                                    <div class="input-group">
                                    <div class="input-group-addon"><i class="ion ion-key"></i></div>
                                    <select id="project_key_id" name="key_id" class="form-control">
                                        @foreach($keys as $key)
                                            <option value="{{ $key->id }}">{{ $key->name }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="project_builds_to_keep">{{ trans('projects.builds') }}</label>
                                    <div class="input-group">
                                    <div class="input-group-addon"><i class="ion ion-ios-box-outline"></i></div>
                                    <input type="number" class="form-control" name="builds_to_keep" min="1" max="20" id="project_builds_to_keep" placeholder="10" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="project_build_url">{{ trans('projects.image') }}</label>
                                    <i class="ion ion-help-buoy" data-toggle="tooltip" data-placement="right" title="{{ trans('projects.ci_image') }}"></i>
                                    <div class="input-group">
                                    <div class="input-group-addon"><i class="ion ion-image"></i></div>
                                    <input type="text" class="form-control" name="build_url" id="project_build_url" placeholder="http://ci.example.com/status.png?project=1" />
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-left btn-save">{{ trans('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
