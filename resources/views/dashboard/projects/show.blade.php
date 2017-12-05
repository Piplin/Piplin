@extends('layouts.dashboard')

@section('content')
    @include('dashboard.projects._partials.summary')

    <div class="row project-status">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li {!! $tab != '' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id]) }}"><span class="piplin piplin-clock"></span> {{ trans('projects.history') }}</a></li>
                    <li {!! $tab != 'hooks' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'hooks']) }}"><span class="piplin piplin-hook"></span> {{ trans('projects.integrations') }}</a></li>
                    <li {!! $tab != 'members' ?: 'class="active"' !!}><a href="{{ route('projects',['project_id'=>$project->id, 'tab'=>'members']) }}"><span class="piplin piplin-users"></span> {{ trans('members.label') }}</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        @if($tab == 'hooks')
                            @include('dashboard.projects._partials.hooks')
                        @elseif($tab == 'members')
                            @include('dashboard.projects._partials.members')
                        @else
                            @include('dashboard.projects._partials.tasks')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if($tab == 'hooks')
        @include('dashboard.projects._dialogs.hook')
    @elseif($tab == 'members')
        @include('dashboard.projects._dialogs.member')
    @endif
    @include('dashboard.projects._dialogs.recover')
    @include('dashboard.projects._dialogs.public_key')
    @include('dashboard.projects._dialogs.rollback')
    @include('dashboard.projects._dialogs.task_draft')
    @include('dashboard.projects._dialogs.create')
@stop

@if($project->can('deploy'))
@section('right-buttons')
    <div class="pull-right">
        @if($project->can('manage'))
        <div class="btn-group">
          <button type="button" class="btn btn-lg btn-default" data-toggle="dropdown" aria-expanded="false"><i class="piplin piplin-more"></i>
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
          </button>
          <ul class="dropdown-menu" role="menu">
            <li><a class="btn-edit" data-project-id="{{ $project->id }}" href="#" data-toggle="modal" data-target="#project_create"><i class="piplin piplin-setting"></i> {{ trans('projects.settings') }}</a></li>
            <li><a class="btn-edit" data-project-id="{{ $project->id }}" href="#" data-toggle="modal" data-target="#project-recover"><i class="piplin piplin-refresh"></i> {{ trans('projects.recover') }}</a></li>
            <li><a class="project-delete" data-project-id="{{ $project->id }}" href="#" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><span class="text-danger"><i class="piplin piplin-delete"></i> {{ trans('projects.delete') }}</span></a></li>
          </ul>
        </div>
        @endif
        @if($project->buildPlan)
        <a href="{{ route('builds', ['id' => $project->buildPlan->id]) }}" class="btn btn-lg btn-success"><i class="piplin piplin-build"></i> {{ trans('projects.build_plan') }}</a>
        @endif
        @if($project->deployPlan)
        <a href="{{ route('deployments', ['id' => $project->deployPlan->id]) }}" class="btn btn-lg btn-info"><i class="piplin piplin-deploy"></i> {{ trans('projects.deploy_plan') }}</a>
        @endif
    </div>
@stop
@endif

@push('javascript')
    <script type="text/javascript">

        @if($tab == 'hooks')
        new Piplin.HooksTab();
        Piplin.Hooks.add({!! $hooks->toJson() !!});

        @elseif($tab == 'members')
        new Piplin.MembersTab();
        Piplin.Members.add({!! $members !!});

        @endif

        Piplin.project_id = {{ $project->id }};

    </script>
    <script src="{{ cdn('js/ace.js') }}"></script>
@endpush
