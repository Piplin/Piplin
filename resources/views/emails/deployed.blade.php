@extends('emails.layout')

@section('content')
    <p>
        <span>{{ trans('notifyEmails.project_name') }}:</span>
        <small>{{ $project['name'] }}</small>
    </p>
    <p>
        <span>{{ trans('notifyEmails.deployed_branch') }}:</span>
        <small>{{ $deployment['branch'] }}</small>
    </p>
    <p>
        <span>{{ trans('notifyEmails.started_at') }}:</span>
        <small>{{ $deployment['started_at'] }}</small>
    </p>
    <p>
        <span>{{ trans('notifyEmails.finished_at') }}:</span>
        <small>{{ $deployment['finished_at'] }}</small>
    </p>
    <div class="deployment-info">
        <p>
            <span>{{ trans('notifyEmails.last_committer') }}:</span>
            <small>{{ $deployment['committer'] }}</small>
        </p>
        <p>
            <span>{{ trans('notifyEmails.last_committ') }}:</span>
            <a href="{{ $deployment['commitURL'] }}">
                <small>{{ $deployment['shortCommit'] }}</small>
            </a>
        </p>
        @if($deployment['reason'])
        <p>
            <span>{{ trans('notifyEmails.reason') }}:</span>
            <small>{{ $deployment['reason'] }}</small>
        </p>
        @endif
    </div>
@stop
