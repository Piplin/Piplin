@extends('layouts.admin')

@section('admin-content')
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li  {!! !in_array($tab, ['', 'commands']) ?: 'class="active"' !!}><a href="{{ route('admin.templates.show', ['id' => $project->id]) }}"><span class="ion ion-code"></span> {{ trans('commands.label') }}</a></li>
                    <li {!! $tab != 'environments' ?: 'class="active"' !!}><a href="{{ route('admin.templates.show', ['id' => $project->id, 'tab' => 'environments']) }}"><span class="ion ion-cube"></span> {{ trans('environments.label') }}</a></li>
                    <li {!! $tab != 'config-files' ?: 'class="active"' !!}><a href="{{ route('admin.templates.show', ['id' => $project->id, 'tab' => 'config-files']) }}"><span class="ion ion-android-settings"></span> {{ trans('configFiles.label') }}</a></li>
                    <li {!! $tab != 'shared-files' ?: 'class="active"' !!}><a href="{{ route('admin.templates.show', ['id' => $project->id, 'tab' => 'shared-files']) }}"><span class="ion ion-document"></span> {{ trans('sharedFiles.tab_label') }}</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane {{ !in_array($tab, ['', 'commands']) ?: 'active' }}" id="commands">
                        @include('dashboard.projects._partials.commands')
                        @include('dashboard.projects._partials.variables')
                    </div>
                    <div class="tab-pane {{ $tab != 'environments' ?: 'active' }}" id="environments">
                        @include('dashboard.projects._partials.environments')
                    </div>
                    <div class="tab-pane {{ $tab != 'config-files' ?: 'active' }}" id="config-files">
                        @include('dashboard.projects._partials.config_files')
                    </div>
                    <div class="tab-pane {{ $tab != 'shared-files' ?: 'active' }}" id="shared-files">
                        @include('dashboard.projects._partials.shared_files')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('dashboard.projects.dialogs.server')
    @include('dashboard.projects.dialogs.variable')
    @include('dashboard.projects.dialogs.environment')
    @include('dashboard.projects.dialogs.shared_files')
    @include('dashboard.projects.dialogs.config_files')
@stop

@push('javascript')
    <script type="text/javascript">
        new app.SharedFilesTab();
        new app.ConfigFilesTab();
        new app.VariablesTab();
        new app.EnvironmentsTab();

        app.SharedFiles.add({!! $sharedFiles->toJson() !!});
        app.ConfigFiles.add({!! $configFiles->toJson() !!});
        app.Variables.add({!! $variables->toJson() !!});
        app.Environments.add({!! $environments->toJson() !!});

        app.project_id = {{ $project->id }};
    </script>
    <script src="{{ cdn('js/ace.js') }}"></script>
@endpush
