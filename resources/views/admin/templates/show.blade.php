@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li  {!! $tab != '' ?: 'class="active"' !!}><a href="{{ route('admin.templates.show', ['id' => $project->id]) }}"><span class="ion ion-code"></span> {{ trans('commands.label') }}</a></li>
                    <li {!! $tab != 'variables' ?: 'class="active"' !!}><a href="{{ route('admin.templates.show', ['id' => $project->id, 'tab' => 'variables']) }}"><span class="ion ion-social-usd"></span> {{ trans('variables.label') }}</a></li>
                    <li {!! $tab != 'shared-files' ?: 'class="active"' !!}><a href="{{ route('admin.templates.show', ['id' => $project->id, 'tab' => 'shared-files']) }}"><span class="ion ion-document"></span> {{ trans('sharedFiles.label') }}</a></li>
                    <li {!! $tab != 'config-files' ?: 'class="active"' !!}><a href="{{ route('admin.templates.show', ['id' => $project->id, 'tab' => 'config-files']) }}"><span class="ion ion-android-settings"></span> {{ trans('configFiles.label') }}</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane {!! $tab != '' ?: 'active' !!}" id="commands">
                        @include('projects._partials.commands')
                    </div>
                    <div class="tab-pane {!! $tab != 'variables' ?: 'active' !!}" id="variables">
                        @include('projects._partials.variables')
                    </div>
                    <div class="tab-pane {!! $tab != 'shared-files' ?: 'active' !!}" id="shared-files">
                        @include('projects._partials.shared_files')
                    </div>
                    <div class="tab-pane {!! $tab != 'config-files' ?: 'active' !!}" id="config-files">
                        @include('projects._partials.config_files')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('projects.dialogs.server')
    @include('projects.dialogs.variable')
    @include('projects.dialogs.shared_files')
    @include('projects.dialogs.config_files')
@stop

@push('javascript')
    <script type="text/javascript">
        new app.SharedFilesTab();
        new app.ConfigFilesTab();
        new app.VariablesTab();

        app.SharedFiles.add({!! $sharedFiles->toJson() !!});
        app.ConfigFiles.add({!! $configFiles->toJson() !!});
        app.Variables.add({!! $variables->toJson() !!});

        app.project_id = {{ $project->id }};
    </script>
@endpush
