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
                <span class="time">@if($deployment->committer)<i class="ion ion-person-add"></i> {{ $deployment->committer }} @endif <span class="label label-default"><i class="ion ion-pricetag"></i> {{ $deployment->branch }}</span> <i class="ion ion-code-working"></i> {{ $deployment->short_commit }} <i class="ion ion-clock"></i> {{ $deployment->started_at->format('H:i:s') }}</span>
                <h4 class="timeline-header"><i class="ion ion-{{ $deployment->is_webhook ? 'steam' : 'person' }}" title="{{ $deployment->user ? $deployment->user->name : trans('deployments.webhook') }}"></i> <a href="{{ route('deployments', ['id' => $deployment->id]) }}">{{ trans('dashboard.deployment_number', ['id' => $deployment->id]) }}</a> <a class="btn-default btn-xs" href="{{ route('projects', ['id' => $deployment->project_id]) }}"><i class="ion ion-social-codepen-outline"></i><span class="small">{{ $deployment->project->group_name }}/{{ $deployment->project->name }}</span> </a></h4>

                @if (!empty($deployment->reason))
                <div class="timeline-body">
                     {{ $deployment->reason }}
                </div>
                @endif
            </div>
        </li>
        @endforeach
    @endforeach
</ul>
{!! $deployments_raw->render() !!}
@endif
