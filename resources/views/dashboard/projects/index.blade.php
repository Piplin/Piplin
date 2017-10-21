@extends('layouts.dashboard')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-flush">
            <div class="panel-body">
                @if(!count($projects))
                <div class="empty-state">
                    <h4>Join a project to get started!</h4>
                </div>
                @else
                <table class="table table-relaxed">
                    <thead>
                        <tr><th class="column-status"></th>
                                <th class="column-name">Name</th>
                                <th class="column-repo">Repository</th>
                                <th class="column-status">Status</th>
                                <th class="column-deployment">Last Deployed</th>
                                <th class="text-right">{{ trans('app.actions') }}</th>
                            </tr>
                    </thead>
                    <tbody>
                    @foreach ($projects as $project)
                    <tr>
                        <td class="column-status">
                            <i class="fixhub fixhub-warning"></i>
                        </td>
                        <th>
                            <a href="{{ route('projects', ['id' => $project->id]) }}" title="{{ trans('projects.details') }}">
                                {{ $project->name }}
                            </a>
                        </th>
                        <td>
                            <i class="ion {{ $project->type_icon }}"></i> <a href="{{ $project->repository_url }}" target="_blank">{{ $project->repository_path }}</a>
                        </td>

                        <td class="status"><span class="text-{{$project->css_class}}"><i class="fixhub fixhub-{{ $project->icon }}"></i> <span>{{ $project->readable_status }}</span></span>
                        </td>
                        <td>@if($project->last_run)
                            <abbr class="timeago" data-toggle="tooltip" data-placement="right" title="{{ $project->last_run }}" data-timeago="{{ $project->last_run }}"></abbr>
                            @else
                            {{ trans('app.never') }}
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('projects', ['id' => $project->id]) }}" class="btn btn-default" data-toggle="tooltip" title="View Project"> <i class="fixhub fixhub-right"></i></a>
                                </td>
                            </tr>
                            @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>

@stop