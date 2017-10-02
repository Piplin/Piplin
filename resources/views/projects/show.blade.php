@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('projects.repository') }}</h3>
                </div>
                <div class="box-body no-padding">
                    <ul class="nav nav-pills nav-stacked">
                        <li><a href="{{ $project->repository_url }}" target="_blank">{{ trans('projects.repository_path') }} <span class="pull-right" title="{{ $project->repository }}"><i class="ion {{ $project->type_icon }}"></i> {{ $project->repository_path }}</span></a></li>
                        <li><a href="{{ $project->branch_url?:'#' }}">{{ trans('projects.branch') }} <span class="pull-right label label-default">{{ $project->branch }}</span></a></li>
                        @if(!empty($project->last_mirrored))
                        <li><a href="javascript:void(0);" data-project-id={{ $project->id }} class="repo-refresh">{{ trans('projects.last_mirrored') }}<span class="pull-right">{{ $project->last_mirrored }}</span> <i class="ion ion-refresh"></i></a></li>
                        @else
                        <li><a href="{{ $project->url }}" target="_blank">{{ trans('projects.url') }} <span class="pull-right text-blue">{{ $project->url }}</span></a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('projects.deployments') }}</h3>
                </div>
                <div class="box-body no-padding">
                    <ul class="nav nav-pills nav-stacked">
                        <li><a href="#">{{ trans('projects.today') }} <span class="pull-right">{{ number_format($today) }}</span></a></li>
                        <li><a href="#">{{ trans('projects.last_week') }} <span class="pull-right">{{ number_format($last_week) }}</span></a></li>
                        <li><a href="#">{{ trans('projects.latest_duration') }}<span class="pull-right">{{ (count($deployments) == 0 OR !$deployments[0]->finished_at) ? trans('app.not_applicable') : $deployments[0]->readable_runtime }} </span></a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('projects.details') }}</h3>
                </div>
                <div class="box-body no-padding">
                    <ul class="nav nav-pills nav-stacked">
                        <li><a href="#" target="_blank">{{ trans('projects.group') }} <span class="pull-right">{{ $project->group->name }}</span></a></li>
                        <li><a href="{{ $project->url }}">{{ trans('projects.url') }} <span class="pull-right"><i class="ion ion-earth"></i></span></a></li>
                        @if(!empty($project->build_url))
                        <li><a href="#">{{ trans('projects.build_status') }} <span class="pull-right"><img src="{{ $project->build_url }}" /></span></a></li>
                        @else
                        <li><a href="#">{{ trans('projects.deploy_status') }}<span class="pull-right label label-{{ $project->css_class }}"><i class="ion ion-{{ $project->icon }}"></i> <span>{{ $project->readable_status }}</span></span></a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row project-status">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li {!! $tab != '' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id]) }}"><span class="ion ion-clock"></span> {{ trans('projects.latest') }}</a></li>
                    <li {!! $tab != 'environments' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'environments']) }}"><span class="ion ion-cube"></span> {{ trans('environments.label') }}</a></li>
                    <li {!! $tab != 'commands' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'commands']) }}"><span class="ion ion-code"></span> {{ trans('commands.label') }}</a></li>
                    <li {!! $tab != 'config-files' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'config-files']) }}"><span class="ion ion-android-settings"></span> {{ trans('configFiles.label') }}</a></li>
                    <li {!! $tab != 'files' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'files']) }}"><span class="ion ion-document"></span> {{ trans('sharedFiles.tab_label') }}</a></li>
                    <li {!! $tab != 'hooks' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'hooks']) }}"><span class="ion ion-paper-airplane"></span> {{ trans('projects.integrations') }}</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane {!! $tab != '' ?: 'active' !!}" id="deployments">
                        @include('projects._partials.deployments')
                    </div>
                    <div class="tab-pane {!! $tab != 'environments' ?: 'active' !!}" id="environments">
                        @include('projects._partials.environments')
                    </div>
                    <div class="tab-pane {!! $tab != 'commands' ?: 'active' !!}" id="commands">
                        @include('projects._partials.commands')
                        @include('projects._partials.variables')
                    </div>
                    <div class="tab-pane {!! $tab != 'config-files' ?: 'active' !!}" id="config-files">
                        @include('projects._partials.config_files')
                    </div>
                    <div class="tab-pane {!! $tab != 'files' ?: 'active' !!}" id="files">
                        @include('projects._partials.shared_files')
                    </div>
                    <div class="tab-pane {!! $tab != 'hooks' ?: 'active' !!}" id="hooks">
                        @include('projects._partials.hooks')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('projects.dialogs.server')
    @include('projects.dialogs.shared_files')
    @include('projects.dialogs.config_files')
    @include('projects.dialogs.variable')
    @include('projects.dialogs.environment')
    @include('projects.dialogs.hook')
    @include('projects.dialogs.key')
    @include('projects.dialogs.reason')
    @include('projects.dialogs.redeploy')
@stop

@section('right-buttons')
    <div class="pull-right">
        <button type="button" class="btn btn-default" title="{{ trans('keys.view_ssh_key') }}" data-toggle="modal" data-target="#show_key"><span class="ion ion-key"></span> {{ trans('keys.ssh_key') }}</button>
        @if(($current_user->isAdmin || $current_user->isOperator) || $project->need_approve)
        <button id="deploy_project" data-toggle="modal" data-backdrop="static" data-target="#reason" type="button" class="btn btn-{{ ($project->isDeploying() OR !count($project->servers)) ? 'danger' : 'info' }}" title="{{ trans('projects.deploy_project') }}" {{ ($project->isDeploying() OR !count($project->servers)) ? 'disabled' : '' }}><span class="ion ion-ios-cloud-upload"></span> {{ trans('projects.deploy') }}</button>
        @endif
    </div>
@stop

@push('javascript')
    <script type="text/javascript">
        new app.SharedFilesTab();
        new app.ConfigFilesTab();
        new app.HooksTab();
        new app.VariablesTab();
        new app.EnvironmentsTab();

        app.SharedFiles.add({!! $sharedFiles->toJson() !!});
        app.ConfigFiles.add({!! $configFiles->toJson() !!});
        app.Hooks.add({!! $hooks->toJson() !!});
        app.Variables.add({!! $variables->toJson() !!});
        app.Environments.add({!! $environments->toJson() !!});

        app.project_id = {{ $project->id }};
        @if(isset($action) && $action == 'apply')
            $('button#deploy_project').trigger('click');
        @endif
    </script>
    <script src="{{ cdn('js/ace.js') }}"></script>
@endpush
