<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ trans('tasks.label') }} <i class="text-gray fixhub fixhub-help" data-toggle="tooltip" data-placement="right" data-original-title="{{ trans('tasks.help') }}"></i></h3>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ trans('tasks.stage') }}</th>
                    <th>{{ trans('tasks.before') }}</th>
                    <th>{{ trans('tasks.action') }}</th>
                    <th>{{ trans('tasks.after') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['clone', 'install', 'build', 'finish'] as $index => $stage)
                <tr>
                    <td>{{ $index+1 }}</td>
                    <td>{{ $project->{'before_'.$stage} }}</td>
                    <td><a href="{{ route('commands.step', ['id' => $project->id, 'command' => $stage]) }}">{{ trans('tasks.'.$stage) }}</a> <i class="fixhub fixhub-info" data-toggle="tooltip" data-placement="right" data-html="true" data-original-title="{!! trans('tasks.'.$stage.'_help') !!}"></i></td>
                    <td>{{ $project->{'after_'.$stage} }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>