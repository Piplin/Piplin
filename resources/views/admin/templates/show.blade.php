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
                    <div class="tab-pane active">
                        @if($tab == 'environments')
                            @include('dashboard.projects._partials.environments')
                        @elseif($tab == 'config-files')
                            @include('dashboard.projects._partials.config_files')
                        @elseif($tab == 'shared-files')
                            @include('dashboard.projects._partials.shared_files')
                        @else
                            @include('dashboard.projects._partials.commands')
                            @include('dashboard.projects._partials.variables')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if($tab == 'environments')
        @include('dashboard.projects._dialogs.environment')
    @elseif($tab == 'config-files')
        @include('dashboard.projects._dialogs.config_files')
    @elseif($tab == 'shared-files')
        @include('dashboard.projects._dialogs.shared_files')
    @else
         @include('dashboard.projects._dialogs.variable')
    @endif
@stop

@push('javascript')
    <script type="text/javascript">
        @if($tab == 'environments')
            new Fixhub.EnvironmentsTab();
            Fixhub.Environments.add({!! $environments->toJson() !!});
        @elseif($tab == 'variables')
            new Fixhub.VariablesTab();
            Fixhub.Variables.add({!! $variables->toJson() !!});
        @elseif($tab == 'config-files')
            new Fixhub.ConfigFilesTab();
            Fixhub.ConfigFiles.add({!! $configFiles->toJson() !!});
        @elseif($tab == 'shared-files')
            new Fixhub.SharedFilesTab();
            Fixhub.SharedFiles.add({!! $sharedFiles->toJson() !!});
        @else
            new Fixhub.VariablesTab();
            Fixhub.Variables.add({!! $variables->toJson() !!});
        @endif
        Fixhub.project_id = {{ $project->id }};
    </script>
    <script src="{{ cdn('js/ace.js') }}"></script>
@endpush
