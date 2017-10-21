@extends('layouts.dashboard')

@section('content')
<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="ion ion-ios-paperplane-outline"></i> {{ trans('deployments.reason') }} :</h3>
        <span>{{ $deployment->reason }}</span>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-xs-12 text-center">
                <button type="button" class="btn btn-lg btn-default"><span  id="deploy_status_bar" class="text-{{ $deployment->css_class }}"><i class="ion ion-{{ $deployment->icon }}"></i> <span>{{ $deployment->readable_status }}</span></span></button>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <p><div class="callout callout-danger {{ $deployment->deploy_failure ? null : 'hide' }}" id="deploy_status">
                        <h4><i class="icon ion ion-close"></i> {{ trans('deployments.deploy_failure') }}</h4>
                        <p>{{ $deployment->output }}</p>
                    </div></p>
            </div>
        </div>
    </div>
    <div class="box-footer">
        <span>
            <strong>{{ trans('deployments.environments') }}</strong>: {{ $deployment->environment_names }}
        </span>
        <span class="pull-right">{{ trans('deployments.started') }} : <abbr class="timeago" data-toggle="tooltip" data-placement="right" title="{{ $deployment->started_at }}" data-timeago="{{ $deployment->started_at }}"></abbr></span>
    </div>
</div>
<div class="row">
    @foreach($deployment->steps as $index => $step)
    <div class="col-xs-12">
        <div class="box deploy-step {{ $step->isCustom() ?: 'box-primary' }}">
            <div class="box-header">
                <i class="{{ $step->icon }}"></i>
                <h3 class="box-title">{{ $index+1 }}. <span>{{ $step->name }}</span> </h3>
            </div>
            <div class="box-body">
                <table class="table table-responsive">
                    <thead>
                        <tr>
                            <th width="10%">{{ trans('servers.environment') }}</th>
                            <th width="20%">{{ trans('deployments.server') }}</th>
                            <th width="20%">{{ trans('deployments.status') }}</th>
                            <th width="15%">{{ trans('deployments.started') }}</th>
                            <th width="15%">{{ trans('deployments.finished') }}</th>
                            <th width="10%">{{ trans('deployments.duration') }}</th>
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

@include('dashboard.deployments.log')
@stop

@push('javascript')
    <script type="text/javascript">
        new Fixhub.DeploymentView();
        Fixhub.Deployment.add({!! $output !!});

        Fixhub.project_id = {{ $deployment->project_id }};
    </script>
@endpush

@push('templates')
    <script type="text/template" id="log-template">
        <td><%- server.environment_name %></td>
        <td><%- server.name %></td>
        <td>
             <span class="text-<%- label_class %>"><i class="status ion ion-<%- icon_css %>"></i> <span><%- label %></span></span>
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
                    <button type="button" class="btn btn-default" title="{{ trans('deployments.output') }}" id="log_<%- id %>" data-toggle="modal" data-backdrop="static" data-target="#log"><i class="ion ion-ios-copy-outline"></i></button>
                <% } %>
            </div>
        </td>
    </script>
@endpush
