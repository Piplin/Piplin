@if (Route::currentRouteName() == 'projects' && $current_user->is_admin)
<div class="callout">
    <h4>{{ trans('commands.deploy_webhook') }} <i class="ion ion-help-buoy" id="show_help" data-toggle="modal" data-backdrop="static" data-target="#help"></i></h4>
    <code id="webhook">{{ $project->webhook_url }}</code><button class="btn btn-xs btn-link" id="new_webhook" title="{{ trans('commands.generate_webhook') }}" data-project-id="{{ $project->id }}"><i class="ion ion-refresh"></i></button>
</div>
@endif

<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ trans('commands.label') }}</h3>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ trans('commands.step') }}</th>
                    <th>{{ trans('commands.before') }}</th>
                    <th>{{ trans('commands.current') }}</th>
                    <th>{{ trans('commands.after') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['clone', 'install', 'activate', 'purge'] as $index => $stage)
                <tr>
                    <td>{{ $index+1 }}</td>
                    <td>{{ $project->{'before_'.$stage} }}</td>
                    <td><a href="{{ route($route, ['id' => $project->id, 'command' => $stage]) }}">{{ trans('commands.'.$stage) }}</a></td>
                    <td>{{ $project->{'after_'.$stage} }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
