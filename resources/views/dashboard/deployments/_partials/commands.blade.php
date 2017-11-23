@if (Route::currentRouteName() == 'projects' && $current_user->is_admin)
<div class="callout">
    <h4>{{ trans('commands.deploy_webhook') }} <i class="piplin piplin-help text-gray" id="show_help" data-toggle="modal" data-backdrop="static" data-target="#help"></i></h4>
    <input id="webhook" value="{{ $project->webhook_url }}"> <button class="clipboard btn-link" data-clipboard-target="#webhook"><i class="piplin piplin-copy"></i></button> <button class="btn-link" id="new_webhook" title="{{ trans('commands.generate_webhook') }}" data-project-id="{{ $project->id }}"><i class="piplin piplin-refresh"></i></button>
</div>
@endif

<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ trans('commands.label') }} <i class="text-gray piplin piplin-help" data-toggle="tooltip" data-placement="right" data-original-title="{{ trans('commands.help') }}"></i></h3>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ trans('commands.stage') }}</th>
                    <th>{{ trans('commands.before') }}</th>
                    <th>{{ trans('commands.action') }}</th>
                    <th>{{ trans('commands.after') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['clone', 'install', 'activate', 'purge'] as $index => $stage)
                <tr>
                    <td>{{ $index+1 }}</td>
                    <td>{{ $deployPlan->{'before_'.$stage} }}</td>
                    <td><a href="{{ route($route, ['id' => $deployPlan->id, 'command' => $stage]) }}">{{ trans('commands.'.$stage) }}</a> <i class="piplin piplin-info" data-toggle="tooltip" data-placement="right" data-html="true" data-original-title="{!! trans('commands.'.$stage.'_help') !!}"></i></td>
                    <td>{{ $deployPlan->{'after_'.$stage} }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if(!$in_admin)
@include('dashboard.projects._dialogs.webhook_help')
@endif
