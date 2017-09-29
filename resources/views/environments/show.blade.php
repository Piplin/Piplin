@extends('layouts.dashboard')

@section('content')
    <div class="row">
        @include('projects._partials.servers')
    </div>
    @include('projects.dialogs.server')
@stop

@push('javascript')
    <script type="text/javascript">
        new app.ServersTab();

        app.Servers.add({!! $servers->toJson() !!});

        app.project_id = {{ $project->id }};
        @if(isset($action) && $action == 'apply')
            $('button#deploy_project').trigger('click');
        @endif
    </script>
    <script src="{{ cdn('js/ace.js') }}"></script>
@endpush