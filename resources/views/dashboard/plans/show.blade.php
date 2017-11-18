@extends('layouts.dashboard')

@section('content')
    @include('dashboard.projects._partials.summary')
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li><a href="">Builds</a></li>
                    <li {!! $tab != 'commands' ?: 'class="active"' !!}><a href="{{ route('plans',['id'=>$plan->id, 'tab'=>'commands']) }}"><span class="fixhub fixhub-command"></span> {{ trans('plans.commands') }}</a></li>
                    <li {!! $tab != 'agents' ?: 'class="active"' !!}><a href="{{ route('plans',['id'=>$plan->id, 'tab'=>'agents']) }}"><span class="fixhub fixhub-server"></span> {{ trans('plans.agents') }}</a></li>
                    <li><a href="">Artifact definitions</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        @if($tab == 'commands')
                            @include('dashboard.plans._partials.commands')
                        @elseif($tab == 'agents')
                            @include('dashboard.environments._partials.servers')
                        @else

                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('dashboard.projects._dialogs.deploy')
    @if($tab == 'agents')
        @include('dashboard.environments._dialogs.server')
    @endif
@stop

@section('right-buttons')
    <div class="pull-right">
        @if($project->can('build'))
        <button id="plan_build" data-toggle="modal" data-backdrop="static" data-target="#deploy" type="button" class="btn btn-lg btn-info" title="{{ trans('plans.build') }}"><span class="fixhub fixhub-build"></span> {{ trans('plans.build') }}</button>
        @endif
    </div>
@stop

@push('javascript')
    <script type="text/javascript">
        @if($tab == 'agents')
            new Fixhub.ServersTab();
            Fixhub.Servers.add({!! $servers->toJson() !!});
        @endif
        Fixhub.targetable_id = {{ $plan->id }};
    </script>
@endpush