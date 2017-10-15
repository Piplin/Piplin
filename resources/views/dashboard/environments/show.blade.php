@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li {!! $tab != '' ?: 'class="active"' !!}><a href="{{ route('environments.show',['id' => $project->id, 'environment_id'=>$environment->id]) }}"><span class="ion ion-clock"></span> {{ trans('environments.servers') }}</a></li>
                    <li {!! $tab != 'deployments' ?: 'class="active"' !!}><a href="{{ route('environments.show',['id' => $project->id, 'environment_id'=>$environment->id, 'tab'=>'deployments']) }}"><span class="ion ion-cube"></span> {{ trans('deployments.label') }}</a></li>
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

@push('javascript')
    <script type="text/javascript">

        @if(empty($tab))
            new app.ServersTab();
            app.Servers.add({!! $servers->toJson() !!});
        @endif

        app.project_id = {{ $project->id }};
        app.environment_id = {{ $environment->id }};
        @if(isset($action) && $action == 'apply')
            $('button#deploy_project').trigger('click');
        @endif
    </script>
    <script src="{{ cdn('js/ace.js') }}"></script>
@endpush