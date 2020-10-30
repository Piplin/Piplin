@extends('layouts.dashboard')

@section('content')
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('tasks.reason') }} :</h3>
        <span>{{ $task->reason ?: trans('app.none') }}</span>
        <span  id="task_status_bar" class="text-{{ $task->css_class }} pull-right"><i class="piplin piplin-{{ $task->icon }}"></i> <span>{{ $task->readable_status }}</span></span>
    </div>
    <div class="box-body">
        @if(count($task->artifacts))
        <h4>{{ trans('artifacts.label') }}</h4>
        <table class="table">
            <th>#</th>
            <th>{{ trans('artifacts.file_name') }}</th>
            <th>{{ trans('artifacts.file_size') }}</th>
            @foreach($task->artifacts as $artifact)
            <tr>
                <td>{{ $artifact->id }}</td>
                <td><a href="{{ route('artifact.download', ['project_id' => $task->project_id, 'artifact' => $artifact->id])}}" target="_blank">{{ $artifact->file_name }}</a></td>
                <td>{{ bytes($artifact->file_size) }}</td>
            </tr>
            @endforeach
        </table>
        <div class="row">
            <div class="col-xs-12 text-center">
            <button id="release_create" data-toggle="modal" data-backdrop="static" data-target="#release" class="btn btn-default"><i class="piplin piplin-release"></i> <span>{{ trans('releases.create') }}</span></button> 
            @if(isset($releases) && count($releases))
            <div class="btn-group">
              <button type="button" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="piplin piplin-deploy"></i> {{ trans('tasks.deploy') }}
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
              </button>
              <ul class="dropdown-menu" role="menu">
                @foreach($releases as $release)
                <li><a  href="{{ route('deployments', ['deployment' => $project->deployPlan->id, 'tab' => 'deploy', 'release_id' => $release->id]) }}"><i class="piplin piplin-release"></i> {{ $release->name }}</a></li>
                @endforeach
              </ul>
            </div>
            @endif
            </div>
        </div>
        @endif
        <div class="row">
            <div class="col-xs-12">
                <p><div class="callout callout-danger {{ $task->run_failure ? null : 'hide' }}" id="task_status">
                        <h4><i class="icon piplin piplin-close"></i> {{ trans('tasks.run_failure') }}</h4>
                        <p>{{ $task->output }}</p>
                    </div></p>
            </div>
        </div>
    </div>
    <div class="box-footer">
        <span>
            <strong>{{ trans('tasks.environments') }}</strong>: {{ $task->environment_names }}
        </span>
        <span class="pull-right">{{ trans('tasks.started') }} : <abbr class="timeago" data-toggle="tooltip" data-placement="right" title="{{ $task->started_at }}" data-timeago="{{ $task->started_at }}"></abbr></span>
    </div>
</div>
<div class="row">
    @foreach($task->steps as $index => $step)
    <div class="col-xs-12">
        <div class="box task-step">
            <div class="box-header">
                <i class="piplin {{ $step->icon }}"></i>
                <h3 class="box-title">{{ $index+1 }}. <span>{{ $step->name }}</span> </h3>
            </div>
            <div class="box-body">
                <table class="table table-responsive">
                    <thead>
                        <tr>
                            <th width="10%">{{ trans('servers.environment') }}</th>
                            <th width="20%">{{ trans('tasks.server') }}</th>
                            <th width="20%">{{ trans('tasks.status') }}</th>
                            <th width="15%">{{ trans('tasks.started') }}</th>
                            <th width="15%">{{ trans('tasks.finished') }}</th>
                            <th width="10%">{{ trans('tasks.duration') }}</th>
                            <th width="10%">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody id="step_{{ $step->id }}">

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
</div>

@include('dashboard.tasks.log')
@include('dashboard.tasks.release')
@stop

@push('javascript')
    <script type="text/javascript">
        new Piplin.TaskView();
        Piplin.Task.add({!! $output !!});

        Piplin.project_id = {{ $task->project_id }};
    </script>
@endpush

@push('templates')
    <script type="text/template" id="log-template">
        <td><%- environment_name %></td>
        <td><%- server.name %>(<%- server.ip_address %>) <% if (cabinet) { %><i class="piplin piplin-cabinet"></i><% } %></td>
        <td>
             <span class="text-<%- label_class %>"><i class="status piplin piplin-<%- icon_css %>"></i> <span><%- label %></span></span>
        </td>
        <td>
            <% if (formatted_start_time) { %>
                <%- formatted_start_time %>
            <% } else { %>
                {{ trans('app.not_applicable') }}
            <% } %>
        </td>
        <td>
            <% if (formatted_end_time) { %>
                <%- formatted_end_time %>
            <% } else { %>
                {{ trans('app.not_applicable') }}
            <% } %>
        </td>
        <td>
         <% if (runtime !== null) { %>
                <%- runtime %>
            <% } else { %>
                {{ trans('app.not_applicable') }}
            <% } %>
            </td>
        <td>
            <div class="btn-group pull-right">
                <% if (output !== null) { %>
                    <button type="button" class="btn btn-default" title="{{ trans('tasks.output') }}" id="log_<%- id %>" data-toggle="modal" data-backdrop="static" data-target="#log"><i class="piplin piplin-copy"></i></button>
                <% } %>
            </div>
        </td>
    </script>
@endpush
