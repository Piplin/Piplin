@if (!count($latest))
    <p>{{ trans('dashboard.no_tasks') }}</p>
@else
<ul class="timeline">
    @foreach ($latest as $date => $tasks)
        <li class="time-label">
            <span>{{ $date }}</span>
        </li>

        @foreach ($tasks as $task)
        <li>
            <i class="piplin piplin-{{ $task->icon }} text-{{ $task->timeline_css_class }}" title="{{ $task->readable_status }}"></i>
            <div class="timeline-item">
                <span class="time"><i class="piplin piplin-clock"></i> <abbr class="timeago" data-toggle="tooltip" data-placement="right" title="{{ $task->started_at }}" data-timeago="{{ $task->finished_at }}"></abbr></span>
                <h4 class="timeline-header"><i class="piplin piplin-{{ $task->is_webhook ? 'hook text-navy' : 'user text-gray' }}" title="{{ $task->author_name }}"></i> <a href="{{ route('tasks.show', ['id' => $task->id]) }}">{{ $task->title }}</a> 
                <span class="small">[{{ $task->environment_names }}]</span>
                @if ($task->isDraft())
                <span>
                    <button type="button" data-toggle="modal" data-backdrop="static" data-target="#task_draft" data-task-id="{{ $task->id }}" class="btn btn-xs btn-info btn-draft"><i class="piplin piplin-check"></i></button>
                </span>
                @endif
                </h4>
                @if (!empty($task->formatted_reason))
                <div class="timeline-body">
                     {!! $task->formatted_reason !!}
                </div>
                @endif
                <div class="timeline-footer small">
                    <span>
                        @if($task->project)
                        <a class="btn-default btn-xs" href="{{ route('projects', ['id' => $task->project_id]) }}"><i class="piplin piplin-project"></i> {{ $task->project->group_name ? $task->project->group_name.'/': null }}{{ $task->project->name }}</a>
                        @endif
                    </span>
                    <span class="pull-right text-muted hidden-xs">
                    @if($task->committer)<i class="piplin piplin-commit"></i> {{ $task->committer }} @endif - @if($task->commit_url)<a class="btn-default btn-xs" href="{{ $task->commit_url }}" target="_blank">{{ $task->branch }}/{{ $task->short_commit }}</a>@else{{ $task->branch }}/{{ $task->short_commit }}@endif
                    </span>
                </div>
            </div>
        </li>
        @endforeach
    @endforeach
</ul>
{!! $tasks_raw->render() !!}

@include('dashboard.projects._dialogs.task_draft')
@endif
