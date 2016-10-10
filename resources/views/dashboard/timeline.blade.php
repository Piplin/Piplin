@if (!count($latest))
    <p>{{ trans('dashboard.no_deployments') }}</p>
@else
<ul class="timeline">
    @foreach ($latest as $date => $deployments)
        <li class="time-label">
            <span class="bg-gray">{{ $date }}</span>
        </li>

        @foreach ($deployments as $deployment)
        <li>
            <i class="ion ion-{{ $deployment->icon }} text-{{ $deployment->timeline_css_class }}" title="{{ $deployment->readable_status }}"></i>
            <div class="timeline-item">
                <span class="time"><i class="ion ion-code-working"></i> {{ $deployment->short_commit }} <span class="label label-default">{{ $deployment->branch }}</span> <i class="ion ion-clock"></i> {{ $deployment->started_at->format('H:i:s') }}</span>
                <h4 class="timeline-header"><i class="ion ion-{{ $deployment->is_webhook ? 'paper-airplane' : 'person' }}"></i> <a href="{{ route('deployments', ['id' => $deployment->id]) }}">{{ trans('dashboard.deployment_number', ['id' => $deployment->id]) }}</a>  <a class="btn-default btn-xs" href="{{ route('projects', ['id' => $deployment->project_id]) }}"><i class="ion ion-social-codepen-outline"></i> {{ $deployment->project->name }} </a> </h4>

                @if (!empty($deployment->reason))
                <div class="timeline-body">
                     {{ $deployment->reason }}
                </div>
                @endif
            </div>
        </li>
        @endforeach
    @endforeach
    <li>
        <i class="ion ion-clock bg-gray"></i>
    </li>
</ul>
@endif
