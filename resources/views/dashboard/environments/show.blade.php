@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li {!! $tab != '' ?: 'class="active"' !!}><a href="{{ route('environments.show',['id' => $project->id, 'environment_id'=>$environment->id]) }}"><span class="ion ion-social-buffer-outline"></span> {{ trans('environments.servers') }}</a></li>
                    <li {!! $tab != 'deployments' ?: 'class="active"' !!}><a href="{{ route('environments.show',['id' => $project->id, 'environment_id'=>$environment->id, 'tab'=>'deployments']) }}"><span class="ion ion-clock"></span> {{ trans('deployments.label') }}</a></li>
                </ul>
                <div class="tab-content">
                <div class="tab-pane active">
                @if($tab == 'deployments')
                    @include('dashboard.projects._partials.deployments')
                @else
                    @include('dashboard.projects._partials.servers')
                @endif
                </div>
                </div>
            </div>
        </div>
    </div>
    @if(empty($tab))
        @include('dashboard.projects._dialogs.server')
    @endif
    @include('dashboard.projects._dialogs.redeploy')
@stop


@section('right-buttons')
<div class="pull-right">
    <button type="button" class="btn btn-lg btn-default" title="{{ trans('keys.view_ssh_key') }}" data-toggle="modal" data-target="#show_key"><span class="ion ion-key"></span> {{ trans('keys.ssh_key') }}</button>
    @if(($current_user->isAdmin || $current_user->isOperator))
    <button id="deploy_project" data-toggle="modal" data-backdrop="static" data-target="#deploy" type="button" class="btn btn-lg btn-{{ ($project->isDeploying() OR !count($project->environments)) ? 'danger' : 'info' }}" title="{{ trans('projects.deploy_project') }}" {{ ($project->isDeploying() OR !count($project->environments)) ? 'disabled' : '' }}><span class="ion ion-ios-cloud-upload"></span> {{ trans('projects.deploy') }}</button>
    @endif
</div>
@stop

@push('javascript')
    <script type="text/javascript">

        @if(empty($tab))
            new Fixhub.ServersTab();
            Fixhub.Servers.add({!! $servers->toJson() !!});
        @endif

        Fixhub.project_id = {{ $project->id }};
        Fixhub.environment_id = {{ $environment->id }};
        @if(isset($action) && $action == 'apply')
            $('button#deploy_project').trigger('click');
        @endif
    </script>
    <script src="{{ cdn('js/ace.js') }}"></script>
@endpush