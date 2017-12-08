@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li {!! $tab != '' ?: 'class="active"' !!}><a href="{{ route('environments.show',['id' => $deployPlan->id, 'environment_id'=>$targetable->id]) }}"><span class="piplin piplin-server"></span> {{ trans('environments.servers') }}</a></li>
                    <li {!! $tab != 'deployments' ?: 'class="active"' !!}><a href="{{ route('environments.show',['id' => $deployPlan->id, 'environment_id'=>$targetable->id, 'tab'=>'deployments']) }}"><span class="piplin piplin-clock"></span> {{ trans('tasks.label') }}</a></li>
                    <li {!! $tab != 'links' ?: 'class="active"' !!}><a href="{{ route('environments.show',['id' => $deployPlan->id, 'environment_id'=>$targetable->id, 'tab'=>'links']) }}"><span class="piplin piplin-link"></span> {{ trans('environments.links') }}</a></li>
                </ul>
                <div class="tab-content">
                <div class="tab-pane active">
                @if($tab == 'deployments')
                    @include('dashboard.projects._partials.tasks')
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
    @include('dashboard.projects._dialogs.rollback')
@stop

@push('javascript')
    <script type="text/javascript">

        @if(empty($tab))
            new Piplin.ServersTab();
            Piplin.Servers.add({!! $servers->toJson() !!});
            new Piplin.CabinetsTab();
            Piplin.Cabinets.add({!! $cabinets !!});
        @elseif($tab == 'links')
            new Piplin.EnvironmentLinksTab();
            Piplin.EnvironmentLinks.add({!! $environmentLinks->toJson() !!});
        @endif


        Piplin.project_id = {{ $project->id }};
        Piplin.targetable_id = {{ $targetable->id }};
    </script>
    <script src="{{ cdn('js/ace.js') }}"></script>
@endpush