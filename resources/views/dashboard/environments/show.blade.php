@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li {!! $tab != '' ?: 'class="active"' !!}><a href="{{ route('environments.show',['id' => $project->id, 'environment_id'=>$environment->id]) }}"><span class="fixhub fixhub-server"></span> {{ trans('environments.servers') }}</a></li>
                    <li {!! $tab != 'deployments' ?: 'class="active"' !!}><a href="{{ route('environments.show',['id' => $project->id, 'environment_id'=>$environment->id, 'tab'=>'deployments']) }}"><span class="fixhub fixhub-clock"></span> {{ trans('deployments.label') }}</a></li>
                    <li {!! $tab != 'links' ?: 'class="active"' !!}><a href="{{ route('environments.show',['id' => $project->id, 'environment_id'=>$environment->id, 'tab'=>'links']) }}"><span class="fixhub fixhub-link"></span> {{ trans('environments.links') }}</a></li>
                </ul>
                <div class="tab-content">
                <div class="tab-pane active">
                @if($tab == 'deployments')
                    @include('dashboard.projects._partials.deployments')
                @elseif($tab == 'links')
                    @include('dashboard.environments._partials.links')
                @else
                    @include('dashboard.environments._partials.servers')
                    @include('dashboard.environments._partials.cabinets')
                @endif
                </div>
                </div>
            </div>
        </div>
    </div>
    @if(empty($tab))
        @include('dashboard.environments._dialogs.server')
        @include('dashboard.environments._dialogs.cabinet')
    @elseif($tab == 'links')
        @include('dashboard.environments._dialogs.link')
    @endif
    @include('dashboard.projects._dialogs.public_key')
    @include('dashboard.projects._dialogs.deploy')
    @include('dashboard.projects._dialogs.redeploy')
@stop


@section('right-buttons')
<div class="pull-right">
    @if($project->can('deploy'))
    <button type="button" class="btn btn-lg btn-default" title="{{ trans('keys.view_ssh_key') }}" data-toggle="modal" data-target="#show_key"><span class="fixhub fixhub-key"></span> {{ trans('keys.ssh_key') }}</button>
    <button id="deploy_project" data-toggle="modal" data-backdrop="static" data-target="#deploy" type="button" class="btn btn-lg btn-{{ ($project->isDeploying() OR !count($project->environments)) ? 'danger' : 'info' }}" title="{{ trans('projects.deploy_project') }}" {{ ($project->isDeploying() OR !count($project->environments)) ? 'disabled' : '' }}><span class="fixhub fixhub-deploy"></span> {{ trans('projects.deploy') }}</button>
    @endif
</div>
@stop

@push('javascript')
    <script type="text/javascript">

        @if(empty($tab))
            new Fixhub.ServersTab();
            Fixhub.Servers.add({!! $servers->toJson() !!});
            new Fixhub.CabinetsTab();
            Fixhub.Cabinets.add({!! $cabinets !!});
        @elseif($tab == 'links')
            new Fixhub.EnvironmentLinksTab();
            Fixhub.EnvironmentLinks.add({!! $environmentLinks->toJson() !!});
        @endif


        Fixhub.project_id = {{ $project->id }};
        Fixhub.environment_id = {{ $environment->id }};
    </script>
    <script src="{{ cdn('js/ace.js') }}"></script>
@endpush