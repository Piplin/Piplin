<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ trans('commands.label') }} <i class="text-gray fixhub fixhub-help" data-toggle="tooltip" data-placement="right" data-original-title="{{ trans('tasks.help') }}"></i></h3>
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
                @foreach(['prepare', 'build', 'test', 'result'] as $index => $stage)
                <tr>
                    <td>{{ $index+1 }}</td>
                    <td>{{ $plan->{'before_'.$stage} }}</td>
                    <td><a href="{{ route('builds.step', ['id' => $plan->id, 'command' => $stage]) }}">{{ trans('commands.'.$stage) }}</a> <i class="fixhub fixhub-info" data-toggle="tooltip" data-placement="right" data-html="true" data-original-title="{!! trans('commands.'.$stage.'_help') !!}"></i></td>
                    <td>{{ $plan->{'after_'.$stage} }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>