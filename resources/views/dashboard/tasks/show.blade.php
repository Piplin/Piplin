@extends('layouts.dashboard')

@section('content')
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('tasks.reason') }} :</h3>
        <span>{{ $task->reason }}</span>
    </div>
    @foreach($task->artifacts as $artifact)
    {{ $artifact->file_name }}
    @endforeach
    <div class="box-body">
        <div class="row">
            <div class="col-xs-12 text-center">
                <button type="button" class="btn btn-lg btn-default"><span  id="task_status_bar" class="text-{{ $task->css_class }}"><i class="piplin piplin-{{ $task->icon }}"></i> <span>{{ $task->readable_status }}</span></span></button>
            </div>
        </div>
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
@stop

@push('javascript')
    <script type="text/javascript">
        new Piplin.DeploymentView();
        Piplin.Deployment.add({!! $output !!});

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
