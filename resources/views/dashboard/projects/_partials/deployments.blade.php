<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ trans('deployments.latest') }}</h3>
    </div>

    @if (!count($deployments))
    <div class="box-body">
        <p>{{ trans('deployments.none') }}</p>
    </div>
    @else
    <div class="box-body table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ trans('deployments.started') }}</th>
                    <th>{{ trans('deployments.environments') }}</th>
                    <th>{{ trans('deployments.started_by') }}</th>
                    <th>{{ trans('deployments.deployer') }}</th>
                    <!--<th>{{ trans('deployments.committer') }}</th>-->
                    <th>{{ trans('deployments.commit') }}</th>
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
                        {{ $deployment->is_webhook ? trans('deployments.webhook') : trans('deployments.manually') }}
                        @if (!empty($deployment->reason))
                            <i class="fixhub fixhub-chatbox deploy-reason" data-toggle="tooltip" data-placement="right" title="{{ $deployment->reason }}"></i>
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
                        <span class="text-{{$deployment->css_class}}"><i class="fixhub fixhub-{{ $deployment->icon }}"></i> <span>{{ $deployment->readable_status }}</span></span>
                    </td>
                    <td>
                        <div class="btn-group pull-right">
                            @if ($deployment->isSuccessful())
                                <button type="button" data-toggle="modal" data-backdrop="static" data-target="#rollback" data-optional-commands="{{ $deployment->optional_commands_used }}" data-deployment-id="{{ $deployment->id }}" class="btn btn-default btn-rollback @if ($deployment->isCurrent()) hide @endif" title="{{ trans('deployments.rollback') }}"><i class="fixhub fixhub-rollback"></i></button>
                            @endif
                            @if ($deployment->isDraft())
                                <button type="button" data-toggle="modal" data-backdrop="static" data-target="#deploy_draft" data-deployment-id="{{ $deployment->id }}" class="btn btn-info btn-draft"><i class="fixhub fixhub-check"></i></button>
                            @endif

                            @if ($deployment->isPending() || $deployment->isRunning())
                                <a href="{{ route('deployments.abort', ['id' => $deployment->id]) }}" class="btn btn-default btn-cancel" title="{{ trans('deployments.cancel') }}"><i class="fixhub fixhub-cancel"></i></a>
                            @endif
                            <a href="{{ route('deployments', ['id' => $deployment->id]) }}" type="button" class="btn btn-default" title="{{ trans('app.details') }}"><i class="fixhub fixhub-go"></i></a>
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
