@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('projects.details') }}</h3>
                </div>
                <div class="box-body no-padding">
                    <ul class="nav nav-pills nav-stacked">
                        <li><a href="{{ $project->repository_url }}" target="_blank">{{ trans('projects.repository') }} <span class="pull-right" title="{{ $project->repository }}"><i class="ion {{ $project->type_icon }}"></i> {{ $project->repository_path }}</span></a></li>
                        <li><a href="{{ $project->branch_url }}" target="_blank">{{ trans('projects.branch') }} <span class="pull-right label label-default">{{ $project->branch }}</span></a></li>
                        @if(!empty($project->url))
                        <li><a href="{{ $project->url }}" target="_blank">{{ trans('projects.url') }} <span class="pull-right text-blue">{{ $project->url }}</span></a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('projects.deployments') }}</h3>
                </div>
                <div class="box-body no-padding">
                    <ul class="nav nav-pills nav-stacked">
                        <li><a href="#">{{ trans('projects.today') }} <span class="pull-right">{{ number_format($today) }}</span></a></li>
                        <li><a href="#">{{ trans('projects.last_week') }} <span class="pull-right">{{ number_format($last_week) }}</span></a></li>
                        <li><a href="#">{{ trans('projects.latest_duration') }}<span class="pull-right">{{ (count($deployments) == 0 OR !$deployments[0]->finished_at) ? trans('app.not_applicable') : $deployments[0]->readable_runtime }} </span></a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('projects.health') }}</h3>
                </div>
                <div class="box-body no-padding">
                    <ul class="nav nav-pills nav-stacked">
                        @if(!empty($project->build_url))
                        <li><a href="#">{{ trans('projects.build_status') }} <span class="pull-right"><img src="{{ $project->build_url }}" /></span></a></li>
                        @endif
                        <li><a href="#">{{ trans('projects.app_status') }} <span class="pull-right label label-{{ $project->app_status_css }}">{{ $project->app_status }}</span></a></li>
                        <li><a href="#">{{ trans('projects.heartbeats_status') }} <span class="pull-right label label-{{ $project->heartbeat_status_css }}">{{ $project->heartbeat_status }}</span></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row project-status">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#issues" data-toggle="tab"><span class="ion ion-ios-information-outline"></span> {{ trans('issues.label') }}</a></li>
                    <li><a href="#deployments" data-toggle="tab"><span class="ion ion-clock"></span> {{ trans('projects.latest') }}</a></li>
                    <li><a href="#servers" data-toggle="tab"><span class="ion ion-social-buffer-outline"></span> {{ trans('servers.label') }}</a></li>
                    <li><a href="#hooks" data-toggle="tab"><span class="ion ion-code"></span> {{ trans('commands.label') }}</a></li>
                    <li><a href="#files" data-toggle="tab"><span class="ion ion-document"></span> {{ trans('sharedFiles.tab_label') }}</a></li>
                    <li><a href="#notifications" data-toggle="tab"><span class="ion ion-paper-airplane"></span> {{ trans('app.notifications') }}</a></li>
                    <li><a href="#health" data-toggle="tab"><span class="ion ion-heart-broken"></span> {{ trans('heartbeats.tab_label') }}</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="issues">
                        @include('projects._partials.issues')
                    </div>
                    <div class="tab-pane" id="deployments">
                        @include('projects._partials.deployments')
                    </div>
                    <div class="tab-pane" id="servers">
                        @include('projects._partials.servers')
                    </div>
                    <div class="tab-pane" id="hooks">
                        @include('projects._partials.commands')
                        @include('projects._partials.variables')
                    </div>
                    <div class="tab-pane" id="files">
                        @include('projects._partials.shared_files')
                        @include('projects._partials.config_files')
                    </div>
                    <div class="tab-pane" id="notifications">
                        @include('projects._partials.notifications')
                    </div>
                    <div class="tab-pane" id="health">
                        @include('projects._partials.heartbeats')
                        @include('projects._partials.check_urls')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('projects.dialogs.server')
    @include('projects.dialogs.shared_files')
    @include('projects.dialogs.config_files')
    @include('projects.dialogs.webhook')
    @include('projects.dialogs.variable')
    @include('projects.dialogs.notify_slack')
    @include('projects.dialogs.notify_email')
    @include('projects.dialogs.heartbeat')
    @include('projects.dialogs.check_urls')
    @include('projects.dialogs.key')
    @include('projects.dialogs.reason')
    @include('projects.dialogs.redeploy')
    @include('projects.dialogs.issue')
@stop

@section('right-buttons')
    <div class="pull-right">
        <button type="button" class="btn btn-default" title="{{ trans('projects.view_ssh_key') }}" data-toggle="modal" data-target="#key"><span class="ion ion-key"></span> {{ trans('projects.ssh_key') }}</button>
        @if($current_user->isAdmin || $current_user->isOperator)
        <button id="deploy_project" data-toggle="modal" data-backdrop="static" data-target="#reason" type="button" class="btn btn-{{ ($project->isDeploying() OR !count($project->servers)) ? 'danger' : 'info' }}" title="{{ trans('projects.deploy_project') }}" {{ ($project->isDeploying() OR !count($project->servers)) ? 'disabled' : '' }}><span class="ion ion-ios-cloud-upload"></span> {{ trans('projects.deploy') }}</button>
        @endif
        <button type="button" class="btn btn-success" title="{{ trans('projects.view_ssh_key') }}" data-toggle="modal" data-target="#issue"><span class="ion ion-ios-information"></span> 申请上线</button>
    </div>
@stop

@push('javascript')
    <script type="text/javascript">
        new app.ServersTab();
        new app.SharedFilesTab();
        new app.ConfigFilesTab();
        new app.NotifySlacksTab();
        new app.NotifyEmailsTab();
        new app.HeartbeatsTab();
        new app.VariablesTab();
        new app.CheckUrlsTab();
        new app.IssuesTab();

        app.Servers.add({!! $servers->toJson() !!});
        app.SharedFiles.add({!! $sharedFiles->toJson() !!});
        app.ConfigFiles.add({!! $configFiles->toJson() !!});
        app.NotifySlacks.add({!! $notifySlacks->toJson() !!});
        app.NotifyEmails.add({!! $notifyEmails->toJson() !!});
        app.Heartbeats.add({!! $heartbeats->toJson() !!});
        app.CheckUrls.add({!! $checkUrls->toJson() !!});
        app.Variables.add({!! $variables->toJson() !!});
        app.Issues.add({!! $issues->toJson() !!});

        app.project_id = {{ $project->id }};
    </script>
@endpush
