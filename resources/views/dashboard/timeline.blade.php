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
            <i class="ion ion-{{ $deployment->icon }} text-{{ $deployment->timeline_css_class }}" title="{{ $deployment->readable_status }}"></i>
            <div class="timeline-item">
                <span class="time"><i class="ion ion-clock"></i> <abbr class="timeago" data-toggle="tooltip" data-placement="right" title="{{ $deployment->started_at }}" data-timeago="{{ $deployment->finished_at }}"></abbr></span>
                <h4 class="timeline-header"><i class="ion ion-{{ $deployment->is_webhook ? 'steam' : 'person' }}" title="{{ $deployment->user ? $deployment->user->name : trans('deployments.webhook') }}"></i> <a href="{{ route('deployments', ['id' => $deployment->id]) }}">{{ trans('dashboard.deployment_number', ['id' => $deployment->id]) }}</a> 
                </h4>
                @if (!empty($deployment->formatted_reason))
                <div class="timeline-body">
                     {!! $deployment->formatted_reason !!}
                </div>
                @endif
                <div class="timeline-footer small">
                    <span>
                        @if($deployment->project)
                        <a class="btn-default btn-xs" href="{{ route('projects', ['id' => $deployment->project_id]) }}"><i class="ion ion-social-codepen-outline"></i> {{ $deployment->project->group_name }}/{{ $deployment->project->name }}</a>
                        @endif
                    </span>
                    <span class="pull-right text-muted">
                    @if($deployment->committer)<i class="ion ion-merge"></i> {{ $deployment->committer }} @endif - @if($deployment->commit_url)<a class="btn-default btn-xs" href="{{ $deployment->commit_url }}" target="_blank">{{ $deployment->branch }}/{{ $deployment->short_commit }}</a>@else{{ $deployment->branch }}/{{ $deployment->short_commit }}@endif
                    </span>
                </div>
            </div>
        </li>
        @endforeach
    @endforeach
</ul>
{!! $deployments_raw->render() !!}
@endif
