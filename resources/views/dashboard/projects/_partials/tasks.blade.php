<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ trans('tasks.latest') }}</h3>
    </div>

    @if (!count($deployments))
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
                    <th>{{ trans('tasks.deployer') }}</th>
                    <!--<th>{{ trans('tasks.committer') }}</th>-->
                    <th>{{ trans('tasks.commit') }}</th>
                    <th>{{ trans('app.status') }}</th>
                    <th class="text-right">{{ trans('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deployments as $deployment)
                <tr id="deployment_{{ $deployment->id }}">
                    <td><abbr class="timeago" data-toggle="tooltip" data-placement="right" title="{{ $deployment->finished_at }}" data-timeago="{{ $deployment->finished_at }}"></abbr></td>
                    <td class="small">{{ $deployment->environment_names }}</td>
                    <td>
                        {{ $deployment->is_webhook ? trans('tasks.webhook') : trans('tasks.manually') }}
                        @if (!empty($deployment->reason))
                            <i class="piplin piplin-chatbox task-reason" data-toggle="tooltip" data-placement="right" title="{{ $deployment->reason }}"></i>
                        @endif
                    </td>
                    <td>
                        @if ($deployment->build_url)
                            <a href="{{ $deployment->build_url }}" target="_blank">{{ $deployment->deployer_name }}</a>
                        @else
                            {{ $deployment->deployer_name }}
                        @endif
                    </td>
                    <!--<td class="committer">{{ $deployment->committer_name }}</td>-->
                    <td class="commit">
                        @if ($deployment->commit_url)
                        <a href="{{ $deployment->commit_url }}" target="_blank">{{ $deployment->short_commit_hash }}</a>
                        @else
                        {{ $deployment->short_commit_hash }}
                        @endif
                        ({{ $deployment->branch }})
                    </td>
                    <td class="status">
                        <span class="text-{{$deployment->css_class}}"><i class="piplin piplin-{{ $deployment->icon }}"></i> <span>{{ $deployment->readable_status }}</span></span>
                    </td>
                    <td>
                        <div class="btn-group pull-right">
                            @if ($deployment->isSuccessful())
                                <button type="button" data-toggle="modal" data-backdrop="static" data-target="#rollback" data-optional-commands="{{ $deployment->optional_commands_used }}" data-deployment-id="{{ $deployment->id }}" class="btn btn-default btn-rollback @if ($deployment->isCurrent()) hide @endif" title="{{ trans('tasks.rollback') }}"><i class="piplin piplin-rollback"></i></button>
                            @endif
                            @if ($deployment->isDraft())
                                <button type="button" data-toggle="modal" data-backdrop="static" data-target="#deploy_draft" data-deployment-id="{{ $deployment->id }}" class="btn btn-info btn-draft"><i class="piplin piplin-check"></i></button>
                            @endif

                            @if ($deployment->isPending() || $deployment->isRunning())
                                <a href="{{ route('tasks.abort', ['id' => $deployment->id]) }}" class="btn btn-default btn-cancel" title="{{ trans('tasks.cancel') }}"><i class="piplin piplin-cancel"></i></a>
                            @endif
                            <a href="{{ route('tasks.show', ['id' => $deployment->id]) }}" type="button" class="btn btn-default" title="{{ trans('app.details') }}"><i class="piplin piplin-go"></i></a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {!! $deployments->render() !!}
    </div>

    @endif
</div>
