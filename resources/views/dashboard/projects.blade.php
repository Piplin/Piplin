@extends('layouts.dashboard')

@section('content')

@if (!count($projects))
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">{{ trans('dashboard.projects') }}</h3>
        </div>
        <div class="box-body">
            <p>{{ trans('dashboard.no_projects') }}</p>
        </div>
    </div>
@else
@foreach ($projects as $group => $group_projects)
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">{{ $group_projects['group'] }}</h3>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="30%">{{ trans('projects.name') }}</th>
                        <th width="25%">{{ trans('projects.deployed') }}</th>
                        <th width="25%">{{ trans('dashboard.status') }}</th>
                        <th class="text-right" width="20%">{{ trans('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($group_projects['projects'] as $group_project)
                    <tr id="project_{{ $group_project->id }}">
                        <td class="name"><a href="{{ route('projects', ['id' => $group_project->id]) }}" title="{{ trans('projects.details') }}">{{ $group_project->name }}</a></td>
                        <td class="time small">
                          @if($group_project->last_run)
                          <abbr class="timeago" data-toggle="tooltip" data-placement="right" title="{{ $group_project->last_run }}" data-timeago="{{ $group_project->last_run }}"></abbr>
                          @else
                          {{ trans('app.never') }}
                          @endif
                        </td>
                        <td class="status"><span class="text-{{$group_project->css_class}}"><i class="piplin piplin-{{ $group_project->icon }}"></i> <span>{{ $group_project->readable_status }}</span></span></td>
                        <td class="text-right">
                            @if($group_project->deployPlan)
                            <a href="{{ route('deployments', ['id' => $group_project->deployPlan->id, 'tab' => 'deploy']) }}" type="button" class="btn btn-primary" title="{{ trans('projects.deploy') }}"><i class="piplin piplin-deploy"></i></a>
                            @endif
                            <a href="{{ route('projects', ['id' => $group_project->id]) }}" type="button" class="btn btn-default" title="{{ trans('app.details') }}"><i class="piplin piplin-go"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
@endif
@include('dashboard.projects._dialogs.create')
@stop

@section('right-buttons')
<div class="pull-right">
    <a href="#" data-toggle="modal" data-target="#project_create" class="btn btn-lg btn-primary"><i class="piplin piplin-plus"></i> {{ trans('projects.create') }}</a>
</div>
@stop
