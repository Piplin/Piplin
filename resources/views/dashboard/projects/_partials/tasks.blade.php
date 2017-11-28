<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ trans('tasks.latest') }}</h3>
    </div>

    @if (!count($tasks))
    <div class="box-body">
        <p>{{ trans('tasks.none') }}</p>
    </div>
    @else
    <div class="box-body table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ trans('tasks.started') }}</th>
                    <th>{{ trans('tasks.environments') }}</th>
                    <th>{{ trans('tasks.started_by') }}</th>
                    <th>{{ trans('tasks.author') }}</th>
                    <!--<th>{{ trans('tasks.committer') }}</th>-->
                    <th>{{ trans('tasks.commit') }}</th>
                    <th>{{ trans('app.status') }}</th>
                    <th class="text-right">{{ trans('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tasks as $task)
                <tr id="task_{{ $task->id }}">
                    <td>@if($task->is_build)<i class="piplin piplin-build"></i>@endif <abbr class="timeago" data-toggle="tooltip" data-placement="right" title="{{ $task->finished_at }}" data-timeago="{{ $task->finished_at }}"></abbr></td>
                    <td class="small">{{ $task->environment_names }}</td>
                    <td>
                        {{ $task->is_webhook ? trans('tasks.webhook') : trans('tasks.manually') }}
                        @if (!empty($task->reason))
                            <i class="piplin piplin-chatbox task-reason" data-toggle="tooltip" data-placement="right" title="{{ $task->reason }}"></i>
                        @endif
                    </td>
                    <td>
                        @if ($task->build_url)
                            <a href="{{ $task->build_url }}" target="_blank">{{ $task->author_name }}</a>
                        @else
                            {{ $task->author_name }}
                        @endif
                    </td>
                    <!--<td class="committer">{{ $task->committer_name }}</td>-->
                    <td class="commit">
                        @if ($task->commit_url)
                        <a href="{{ $task->commit_url }}" target="_blank">{{ $task->short_commit_hash }}</a>
                        @else
                        {{ $task->short_commit_hash }}
                        @endif
                        ({{ $task->branch }})
                    </td>
                    <td class="status">
                        <span class="text-{{$task->css_class}}"><i class="piplin piplin-{{ $task->icon }}"></i> <span>{{ $task->readable_status }}</span></span>
                    </td>
                    <td>
                        <div class="btn-group pull-right">
                            @if ($task->isSuccessful())
                                <button type="button" data-toggle="modal" data-backdrop="static" data-target="#rollback" data-optional-commands="{{ $task->optional_commands_used }}" data-deployment-id="{{ $task->id }}" class="btn btn-default btn-rollback @if ($task->isCurrent()) hide @endif" title="{{ trans('tasks.rollback') }}"><i class="piplin piplin-rollback"></i></button>
                            @endif
                            @if ($task->isDraft())
                                <button type="button" data-toggle="modal" data-backdrop="static" data-target="#deploy_draft" data-deployment-id="{{ $task->id }}" class="btn btn-info btn-draft"><i class="piplin piplin-check"></i></button>
                            @endif

                            @if ($task->isPending() || $task->isRunning())
                                <a href="{{ route('tasks.abort', ['id' => $task->id]) }}" class="btn btn-default btn-cancel" title="{{ trans('tasks.cancel') }}"><i class="piplin piplin-cancel"></i></a>
                            @endif
                            <a href="{{ route('tasks.show', ['id' => $task->id]) }}" type="button" class="btn btn-default" title="{{ trans('app.details') }}"><i class="piplin piplin-go"></i></a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {!! $tasks->render() !!}
    </div>

    @endif
</div>
