@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-md-5">
            <div class="panel panel-flush">
                <div class="panel-heading">
                    <h4>{{ trans('projects.repository') }}</h4>
                </div>
                <div class="panel-body">
					<table class="table table-relaxed">
						<tbody>
							<tr>
								<td>{{ trans('projects.repository_path') }}</td>
								<td class="text-right">
									<i class="fixhub {{ $project->type_icon }}"></i> <a href="{{ $project->repository_url }}" target="_blank">{{ $project->repository_path }}</a>
								</td>
							</tr>
							<tr>
								<td>{{ trans('projects.branch') }}</td>
								<td class="text-right"><a href="{{ $project->branch_url?:'#' }}"><span class="label label-default">{{ $project->branch }}</span></td>
							</tr>
							<tr>
								<td>{{ trans('projects.change_branch') }}</td>
								<td class="text-right">
									{{ $project->allow_other_branch ? trans('app.yes') : trans('app.no') }}</a>
								</td>
							</tr>
						</tbody>
					</table>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-flush">
                <div class="panel-heading">
                    <h4>{{ trans('projects.deployments') }}</h4>
                </div>
                <div class="panel-body">
					<table class="table table-relaxed">
						<tbody>
							<tr>
								<td>{{ trans('projects.today') }}</td>
								<td class="text-right">
									{{ number_format($today) }}
								</td>
							</tr>
							<tr>
								<td>{{ trans('projects.last_week') }}</td>
								<td class="text-right">{{ number_format($last_week) }}</td>
							</tr>
							<tr>
								<td>{{ trans('projects.latest_duration') }}</td>
								<td class="text-right">
									{{ (count($deployments) == 0 OR !$deployments[0]->finished_at) ? trans('app.not_applicable') : $deployments[0]->readable_runtime }}
								</td>
							</tr>
						</tbody>
					</table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-flush">
                <div class="panel-heading">
                    <h4>{{ trans('projects.details') }}</h4>
                </div>
                <div class="panel-body">
					<table class="table table-relaxed">
						<tbody>
							<tr>
								<td>{{ trans('projects.deploy_path') }}</td>
								<td class="text-right small">
									{{ $project->clean_deploy_path }}
								</td>
							</tr>
							<tr>
								<td>{{ trans('projects.key') }}</td>
								<td class="text-right"><a href="#" title="{{ trans('keys.view_ssh_key') }}" class="label label-warning" data-toggle="modal" data-target="#show_key">{{ trans('keys.ssh_key') }}</a></td>
							</tr>
							<tr>
								<td>{{ trans('projects.deploy_status') }}</td>
								<td class="text-right">
                                    <span class="text-{{$project->css_class}}"><i class="fixhub fixhub-{{ $project->icon }}"></i> {{ $project->readable_status }}</span> / <abbr class="timeago" data-toggle="tooltip" data-placement="right" title="{{ $project->last_run }}" data-timeago="{{ $project->last_run }}"></abbr>
								</td>
							</tr>
						</tbody>
					</table>
                </div>
            </div>
        </div>
    </div>

    <div class="row project-status">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li {!! $tab != '' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id]) }}"><span class="fixhub fixhub-clock"></span> {{ trans('deployments.label') }}</a></li>
                    <li {!! $tab != 'environments' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'environments']) }}"><span class="fixhub fixhub-environment"></span> {{ trans('environments.label') }}</a></li>
                    <li {!! $tab != 'commands' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'commands']) }}"><span class="fixhub fixhub-command"></span> {{ trans('commands.label') }}</a></li>
                    <li {!! $tab != 'config-files' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'config-files']) }}"><span class="fixhub fixhub-config-file"></span> {{ trans('configFiles.label') }}</a></li>
                    <li {!! $tab != 'shared-files' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'shared-files']) }}"><span class="fixhub fixhub-shared-file"></span> {{ trans('sharedFiles.tab_label') }}</a></li>
                    <li {!! $tab != 'hooks' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'hooks']) }}"><span class="fixhub fixhub-hook"></span> {{ trans('projects.integrations') }}</a></li>
                    <li {!! $tab != 'members' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'members']) }}"><span class="fixhub fixhub-users"></span> {{ trans('members.label') }}</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        @if($tab == 'environments')
                            @include('dashboard.projects._partials.environments')
                        @elseif($tab == 'commands')
                            @include('dashboard.projects._partials.commands')
                            @include('dashboard.projects._partials.variables')
                        @elseif($tab == 'config-files')
                            @include('dashboard.projects._partials.config_files')
                        @elseif($tab == 'shared-files')
                            @include('dashboard.projects._partials.shared_files')
                        @elseif($tab == 'hooks')
                            @include('dashboard.projects._partials.hooks')
                        @elseif($tab == 'members')
                            @include('dashboard.projects._partials.members')
                        @else
                            @include('dashboard.projects._partials.deployments')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if($tab == 'environments')
        @include('dashboard.projects._dialogs.environment')
    @elseif($tab == 'commands')
        @include('dashboard.projects._dialogs.variable')
    @elseif($tab == 'config-files')
        @include('dashboard.projects._dialogs.config_files')
    @elseif($tab == 'shared-files')
        @include('dashboard.projects._dialogs.shared_files')
    @elseif($tab == 'hooks')
        @include('dashboard.projects._dialogs.hook')
    @elseif($tab == 'members')
        @include('dashboard.projects._dialogs.member')
    @endif

    @include('dashboard.projects._dialogs.public_key')
    @include('dashboard.projects._dialogs.deploy')
    @include('dashboard.projects._dialogs.rollback')
    @include('dashboard.projects._dialogs.deploy_draft')
@stop

@if($project->can('deploy'))
@section('right-buttons')
    <div class="pull-right">
        @if($project->can('manage'))
        <div class="btn-group">
          <button type="button" class="btn btn-lg btn-default" data-toggle="dropdown" aria-expanded="false"><i class="fixhub fixhub-more"></i>
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
          </button>
          <ul class="dropdown-menu" role="menu">
            <li><a class="btn-edit" data-project-id="{{ $project->id }}" href="#" data-toggle="modal" data-target="#project_create"><i class="fixhub fixhub-setting"></i> {{ trans('projects.settings') }}</a></li>
            <li><a class="project-delete" data-project-id="{{ $project->id }}" href="#" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><span class="text-danger"><i class="fixhub fixhub-delete"></i> {{ trans('projects.delete') }}</span></a></li>
          </ul>
        </div>
        @endif
        @if($project->plan)
        <a href="{{ route('plans', ['id' => $project->plan->id]) }}" class="btn btn-lg btn-primary"><i class="fixhub fixhub-template"></i> {{ trans('plans.label') }}</a>
        @endif
        @if($project->can('deploy'))
        <button id="deploy_project" data-toggle="modal" data-backdrop="static" data-target="#deploy" type="button" class="btn btn-lg btn-{{ ($project->isDeploying() OR !count($project->environments)) ? 'danger' : 'info' }}" title="{{ trans('projects.deploy_project') }}" {{ ($project->isDeploying() OR !count($project->environments)) ? 'disabled' : '' }}><span class="fixhub fixhub-deploy"></span> {{ trans('projects.deploy') }}</button>
        @endif
    </div>
@stop
@endif

@push('javascript')
    <script type="text/javascript">
        @if($tab == 'environments')
        new Fixhub.EnvironmentsTab();
        Fixhub.Environments.add({!! $environments->toJson() !!});

        @elseif($tab == 'commands')
        new Fixhub.VariablesTab();
        Fixhub.Variables.add({!! $variables->toJson() !!});

        @elseif($tab == 'config-files')
        new Fixhub.ConfigFilesTab();
        Fixhub.ConfigFiles.add({!! $configFiles->toJson() !!});

        @elseif($tab == 'shared-files')
        new Fixhub.SharedFilesTab();
        Fixhub.SharedFiles.add({!! $sharedFiles->toJson() !!});

        @elseif($tab == 'hooks')
        new Fixhub.HooksTab();
        Fixhub.Hooks.add({!! $hooks->toJson() !!});

        @elseif($tab == 'members')
        new Fixhub.MembersTab();
        Fixhub.Members.add({!! $members !!});

        @endif

        Fixhub.project_id = {{ $project->id }};

        @if($tab == 'deploy')
            $('button#deploy_project').trigger('click');
        @endif
    </script>
    <script src="{{ cdn('js/ace.js') }}"></script>
@endpush
