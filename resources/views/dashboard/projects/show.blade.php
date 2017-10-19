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
									<i class="ion {{ $project->type_icon }}"></i> <a href="{{ $project->repository_url }}" target="_blank">{{ $project->repository_path }}</a>
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
								<td>{{ trans('projects.group') }}</td>
								<td class="text-right">
									{{ $project->group ? $project->group->name : null }}
								</td>
							</tr>
							<tr>
								<td>{{ trans('projects.deployed') }}</td>
								<td class="text-right"><abbr class="timeago" data-toggle="tooltip" data-placement="right" title="{{ $project->last_run }}" data-timeago="{{ $project->last_run }}"></abbr></td>
							</tr>
							<tr>
								<td>{{ trans('projects.deploy_status') }}</td>
								<td class="text-right">
                                    <span class="text-{{$project->css_class}}"><i class="ion ion-{{ $project->icon }}"></i> {{ $project->readable_status }}</span>
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
                    <li {!! $tab != '' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id]) }}"><span class="ion ion-clock"></span> {{ trans('deployments.label') }}</a></li>
                    <li {!! $tab != 'environments' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'environments']) }}"><span class="ion ion-ios-filing-outline"></span> {{ trans('environments.label') }}</a></li>
                    <li {!! $tab != 'commands' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'commands']) }}"><span class="ion ion-code"></span> {{ trans('commands.label') }}</a></li>
                    <li {!! $tab != 'config-files' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'config-files']) }}"><span class="ion ion-ios-copy-outline"></span> {{ trans('configFiles.label') }}</a></li>
                    <li {!! $tab != 'shared-files' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'shared-files']) }}"><span class="ion ion-ios-folder-outline"></span> {{ trans('sharedFiles.tab_label') }}</a></li>
                    <li {!! $tab != 'hooks' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'hooks']) }}"><span class="ion ion-paper-airplane"></span> {{ trans('projects.integrations') }}</a></li>
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
        @include('dashboard.projects._dialogs.server')
    @elseif($tab == 'commands')
        @include('dashboard.projects._dialogs.variable')
    @elseif($tab == 'config-files')
        @include('dashboard.projects._dialogs.config_files')
    @elseif($tab == 'shared-files')
        @include('dashboard.projects._dialogs.shared_files')
    @elseif($tab == 'hooks')
        @include('dashboard.projects._dialogs.hook')
    @endif

    @include('dashboard.projects._dialogs.key')
    @include('dashboard.projects._dialogs.deploy')
    @include('dashboard.projects._dialogs.redeploy')
@stop

@section('right-buttons')
    <div class="pull-right">
        <button type="button" class="btn btn-default" title="{{ trans('keys.view_ssh_key') }}" data-toggle="modal" data-target="#show_key"><span class="ion ion-key"></span> {{ trans('keys.ssh_key') }}</button>
        @if(($current_user->isAdmin || $current_user->isOperator) || $project->need_approve)
        <button id="deploy_project" data-toggle="modal" data-backdrop="static" data-target="#deploy" type="button" class="btn btn-{{ ($project->isDeploying() OR !count($project->environments)) ? 'danger' : 'info' }}" title="{{ trans('projects.deploy_project') }}" {{ ($project->isDeploying() OR !count($project->environments)) ? 'disabled' : '' }}><span class="ion ion-ios-cloud-upload"></span> {{ trans('projects.deploy') }}</button>
        @endif
    </div>
@stop

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
        @endif

        Fixhub.project_id = {{ $project->id }};
        @if(isset($action) && $action == 'apply')
            $('button#deploy_project').trigger('click');
        @endif
    </script>
    <script src="{{ cdn('js/ace.js') }}"></script>
@endpush
