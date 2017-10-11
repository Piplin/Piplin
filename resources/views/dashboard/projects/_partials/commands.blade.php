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