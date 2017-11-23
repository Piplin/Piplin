@extends('layouts.dashboard')

@section('content')
    <div class="row project-status">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li {!! $tab != '' ?: 'class="active"' !!}><a href="{{ route('deployments',['id'=>$targetable_id]) }}"><span class="piplin piplin-clock"></span> {{ trans('tasks.label') }}</a></li>
                    <li {!! $tab != 'environments' ?: 'class="active"' !!}><a href="{{ route('deployments',['id'=>$targetable_id, 'tab'=>'environments']) }}"><span class="piplin piplin-environment"></span> {{ trans('environments.label') }}</a></li>
                    <li {!! $tab != 'commands' ?: 'class="active"' !!}><a href="{{ route('deployments',['id'=>$targetable_id, 'tab'=>'commands']) }}"><span class="piplin piplin-command"></span> {{ trans('commands.label') }}</a></li>
                    <li {!! $tab != 'config-files' ?: 'class="active"' !!}><a href="{{ route('deployments',['id'=>$targetable_id, 'tab'=>'config-files']) }}"><span class="piplin piplin-config-file"></span> {{ trans('configFiles.label') }}</a></li>
                    <li {!! $tab != 'shared-files' ?: 'class="active"' !!}><a href="{{ route('deployments',['id'=>$targetable_id, 'tab'=>'shared-files']) }}"><span class="piplin piplin-shared-file"></span> {{ trans('sharedFiles.tab_label') }}</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        @if($tab == 'environments')
                            @include('dashboard.deployments._partials.environments')
                        @elseif($tab == 'commands')
                            @include('dashboard.deployments._partials.commands')
                            @include('dashboard.deployments._partials.variables')
                        @elseif($tab == 'config-files')
                            @include('dashboard.deployments._partials.config_files')
                        @elseif($tab == 'shared-files')
                            @include('dashboard.deployments._partials.shared_files')
                        @else
                            @include('dashboard.projects._partials.tasks')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if($tab == 'environments')
        @include('dashboard.deployments._dialogs.environment')
    @elseif($tab == 'commands')
        @include('dashboard.deployments._dialogs.variable')
    @elseif($tab == 'config-files')
        @include('dashboard.deployments._dialogs.config_files')
    @elseif($tab == 'shared-files')
        @include('dashboard.deployments._dialogs.shared_files')
    @endif

    @include('dashboard.projects._dialogs.public_key')
    @include('dashboard.projects._dialogs.task')
    @include('dashboard.projects._dialogs.rollback')
    @include('dashboard.projects._dialogs.task_draft')
    @include('dashboard.projects._dialogs.create')
@stop

@if($project->can('deploy'))
@section('right-buttons')
    <div class="pull-right">
        @if($project->can('deploy'))
        <button id="deploy_project" data-toggle="modal" data-backdrop="static" data-target="#task" type="button" class="btn btn-lg btn-{{ ($project->isRunning() OR !count($project->environments)) ? 'danger' : 'primary' }}" title="{{ trans('projects.deploy_project') }}" {{ ($project->isRunning() OR !count($project->environments)) ? 'disabled' : '' }}><span class="piplin piplin-deploy"></span> {{ trans('projects.deploy') }}</button>
        @endif
    </div>
@stop
@endif

@push('javascript')
    <script type="text/javascript">
        @if($tab == 'environments')
        new Piplin.EnvironmentsTab();
        Piplin.Environments.add({!! $environments->toJson() !!});

        @elseif($tab == 'commands')
        new Piplin.VariablesTab();
        Piplin.Variables.add({!! $variables->toJson() !!});

        @elseif($tab == 'config-files')
        new Piplin.ConfigFilesTab();
        Piplin.ConfigFiles.add({!! $configFiles->toJson() !!});

        @elseif($tab == 'shared-files')
        new Piplin.SharedFilesTab();
        Piplin.SharedFiles.add({!! $sharedFiles->toJson() !!});

        @endif

        Piplin.project_id = {{ $project->id }};

        @if($tab == 'deploy')
            $('button#deploy_project').trigger('click');
        @endif
    </script>
    <script src="{{ cdn('js/ace.js') }}"></script>
@endpush
