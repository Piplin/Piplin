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
                            <li class="active"><a href="#project_details" data-toggle="tab">{{ trans('projects.project_details') }}</a></li>
                            <li><a href="#project_repo" data-toggle="tab">{{ trans('projects.repository') }}</a></li>
                            <li><a href="#project_build" data-toggle="tab">{{ trans('projects.build_options') }}</a></li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane active" id="project_details">
                                <div class="form-group">
                                    <label for="project_name">{{ trans('projects.name') }}</label>
                                    <input type="text" class="form-control" name="name" id="project_name" placeholder="{{ trans('projects.name_placeholder') }}" />
                                </div>
                                <div class="form-group">
                                    <label for="project_group_id">{{ trans('projects.group') }}</label>
                                    <select id="project_group_id" name="group_id" class="form-control">
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="project_key_id">{{ trans('projects.key') }}</label>
                                    <select id="project_key_id" name="key_id" class="form-control">
                                        @foreach($keys as $key)
                                            <option value="{{ $key->id }}">{{ $key->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if (count($templates) > 0)
                                <div class="form-group" id="template-list">
                                    <label for="project_template_id">{{ trans('templates.type') }}</label>
                                    <select id="project_template_id" name="template_id" class="form-control">
                                        <option value="">{{ trans('templates.custom') }}</option>
                                        @foreach ($templates as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                <div class="form-group">
                                    <label for="project_url">{{ trans('projects.url') }}</label>
                                    <input type="text" class="form-control" name="url" id="project_url" placeholder="http://www.example.com" />
                                </div>
                            </div>

                            <div class="tab-pane" id="project_build">

                                <div class="form-group">
                                    <label for="project_builds_to_keep">{{ trans('projects.builds') }}</label>
                                    <input type="number" class="form-control" name="builds_to_keep" min="1" max="20" id="project_builds_to_keep" placeholder="10" />
                                </div>
                                <div class="form-group">
                                    <label for="project_build_url">{{ trans('projects.image') }}</label>
                                    <i class="ion ion-help" data-toggle="tooltip" data-placement="right" title="{{ trans('projects.ci_image') }}"></i>
                                    <input type="text" class="form-control" name="build_url" id="project_build_url" placeholder="http://ci.example.com/status.png?project=1" />
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('projects.options') }}</label>
                                    <div class="checkbox">
                                        <label for="project_include_dev">
                                            <input type="checkbox" value="1" name="include_dev" id="project_include_dev" />
                                            {{ trans('projects.include_dev') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="project_repo">
                                <div class="form-group">
                                    <label for="project_repository">{{ trans('projects.repository_url') }}</label>
                                    <input type="text" class="form-control" name="repository" id="project_repository" placeholder="git&#64;git.example.com:repositories/project.git" />
                                </div>
                                <div class="form-group">
                                    <label for="project_branch">{{ trans('projects.branch') }}</label>
                                    <input type="text" class="form-control" name="branch" id="project_branch"  placeholder="master" />
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
