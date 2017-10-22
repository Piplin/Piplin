@extends('layouts.admin')

@section('admin-content')
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li  {!! !in_array($tab, ['', 'commands']) ?: 'class="active"' !!}><a href="{{ route('admin.templates.show', ['id' => $project->id]) }}"><span class="fixhub fixhub-command"></span> {{ trans('commands.label') }}</a></li>
                    <li {!! $tab != 'environments' ?: 'class="active"' !!}><a href="{{ route('admin.templates.show', ['id' => $project->id, 'tab' => 'environments']) }}"><span class="fixhub fixhub-environment"></span> {{ trans('environments.label') }}</a></li>
                    <li {!! $tab != 'config-files' ?: 'class="active"' !!}><a href="{{ route('admin.templates.show', ['id' => $project->id, 'tab' => 'config-files']) }}"><span class="fixhub fixhub-config-file"></span> {{ trans('configFiles.label') }}</a></li>
                    <li {!! $tab != 'shared-files' ?: 'class="active"' !!}><a href="{{ route('admin.templates.show', ['id' => $project->id, 'tab' => 'shared-files']) }}"><span class="fixhub fixhub-shared-file"></span> {{ trans('sharedFiles.tab_label') }}</a></li>
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

    @include('dashboard.projects._dialogs.server')
    @include('dashboard.projects._dialogs.variable')
    @include('dashboard.projects._dialogs.environment')
    @include('dashboard.projects._dialogs.shared_files')
    @include('dashboard.projects._dialogs.config_files')
@stop

@push('javascript')
    <script type="text/javascript">
        new Fixhub.SharedFilesTab();
        new Fixhub.ConfigFilesTab();
        new Fixhub.VariablesTab();
        new Fixhub.EnvironmentsTab();

        Fixhub.SharedFiles.add({!! $sharedFiles->toJson() !!});
        Fixhub.ConfigFiles.add({!! $configFiles->toJson() !!});
        Fixhub.Variables.add({!! $variables->toJson() !!});
        Fixhub.Environments.add({!! $environments->toJson() !!});

        Fixhub.project_id = {{ $project->id }};
    </script>
    <script src="{{ cdn('js/ace.js') }}"></script>
@endpush
