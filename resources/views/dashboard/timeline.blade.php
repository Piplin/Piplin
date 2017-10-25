@if (!count($latest))
    <p>{{ trans('dashboard.no_deployments') }}</p>
@else
<ul class="timeline">
    @foreach ($latest as $date => $deployments)
        <li class="time-label">
            <span>{{ $date }}</span>
        </li>

        @foreach ($deployments as $deployment)
        <li>
            <i class="fixhub fixhub-{{ $deployment->icon }} text-{{ $deployment->timeline_css_class }}" title="{{ $deployment->readable_status }}"></i>
            <div class="timeline-item">
                <span class="time"><i class="fixhub fixhub-clock"></i> <abbr class="timeago" data-toggle="tooltip" data-placement="right" title="{{ $deployment->started_at }}" data-timeago="{{ $deployment->finished_at }}"></abbr></span>
                <h4 class="timeline-header"><i class="fixhub fixhub-{{ $deployment->is_webhook ? 'hook text-navy' : 'user text-gray' }}" title="{{ $deployment->deployer_name }}"></i> <a href="{{ route('deployments', ['id' => $deployment->id]) }}">{{ trans('dashboard.deployment_number', ['id' => $deployment->id]) }}</a> 
                <span class="small">[{{ $deployment->environment_names }}]</span>
                </h4>
                @if (!empty($deployment->formatted_reason))
                <div class="timeline-body">
                     {!! $deployment->formatted_reason !!}
                </div>
                @endif
                <div class="timeline-footer small">
                    <span>
                        @if($deployment->project)
                        <a class="btn-default btn-xs" href="{{ route('projects', ['id' => $deployment->project_id]) }}"><i class="fixhub fixhub-project"></i> {{ $deployment->project->group_name ? $deployment->project->group_name.'/': null }}{{ $deployment->project->name }}</a>
                        @endif
                    </span>
                    <span class="pull-right text-muted hidden-xs">
                    @if($deployment->committer)<i class="fixhub fixhub-commit"></i> {{ $deployment->committer }} @endif - @if($deployment->commit_url)<a class="btn-default btn-xs" href="{{ $deployment->commit_url }}" target="_blank">{{ $deployment->branch }}/{{ $deployment->short_commit }}</a>@else{{ $deployment->branch }}/{{ $deployment->short_commit }}@endif
                    </span>
                </div>
            </div>
        </li>
        @endforeach
    @endforeach
</ul>
{!! $deployments_raw->render() !!}
@endif
