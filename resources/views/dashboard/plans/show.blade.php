@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li {!! $tab != '' ?: 'class="active"' !!}><a href="{{ route('plans',['id'=>$plan->id]) }}"><span class="piplin piplin-clock"></span> {{ trans('plans.builds') }}</a></li>
                    <li {!! $tab != 'commands' ?: 'class="active"' !!}><a href="{{ route('plans',['id'=>$plan->id, 'tab'=>'commands']) }}"><span class="piplin piplin-command"></span> {{ trans('plans.commands') }}</a></li>
                    <li {!! $tab != 'agents' ?: 'class="active"' !!}><a href="{{ route('plans',['id'=>$plan->id, 'tab'=>'agents']) }}"><span class="piplin piplin-server"></span> {{ trans('plans.agents') }}</a></li>
                    <li {!! $tab != 'patterns' ?: 'class="active"' !!}><a href="{{ route('plans',['id'=>$plan->id, 'tab'=>'patterns']) }}"><span class="piplin piplin-pattern"></span> {{ trans('patterns.label') }}</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        @if($tab == 'commands')
                            @include('dashboard.plans._partials.commands')
                        @elseif($tab == 'agents')
                            @include('dashboard.environments._partials.servers')
                        @elseif($tab == 'patterns')
                            @include('dashboard.plans._partials.patterns')
                        @else
                            @include('dashboard.projects._partials.tasks')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('dashboard.projects._dialogs.task')
    @if($tab == 'agents')
        @include('dashboard.environments._dialogs.server')
    @elseif($tab == 'patterns')
        @include('dashboard.plans._dialogs.pattern')
    @endif
@stop

@section('right-buttons')
    <div class="pull-right">
        @if($project->can('build'))
        <button id="plan_build" data-toggle="modal" data-backdrop="static" data-target="#task" type="button" class="btn btn-lg btn-primary" title="{{ trans('plans.build') }}"><span class="piplin piplin-build"></span> {{ trans('plans.build') }}</button>
        @endif
    </div>
@stop

@push('javascript')
    <script type="text/javascript">
        @if($tab == 'agents')
            new Piplin.ServersTab();
            Piplin.Servers.add({!! $servers->toJson() !!});
        @elseif($tab == 'patterns')
            new Piplin.PatternsTab();
            Piplin.Patterns.add({!! $patterns->toJson() !!});
        @endif
        Piplin.targetable_id = {{ $plan->id }};
    </script>
@endpush