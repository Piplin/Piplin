<div class="modal fade" id="help" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="fixhub fixhub-help"></i> {{ trans('commands.webhook_help') }}</h4>
            </div>
            <div class="modal-body">

                <p>{{ trans('commands.webhook_example') }}</p>
                <h5><strong>{{ trans('commands.webhook_fields') }}</strong></h5>
                <dl class="dl-horizontal" id="hook_fields">
                    <dt><em>branch</em></dt>
                    <dd>{{ trans('commands.webhook_branch') }}</dd>
                    <dt><em>update_only</em></dt>
                    <dd>{{ trans('commands.webhook_update') }}</dd>
                    <dt><em>reason</em></dt>
                    <dd>{{ trans('commands.webhook_reason') }}</dd>
                    <dt><em>source</em></dt>
                    <dd>{{ trans('commands.webhook_source') }}</dd>
                    <dt><em>url</em></dt>
                    <dd>{{ trans('commands.webhook_url') }}</dd>
                    @if(isset($optional) && count($optional))
                        <dt><em>commands</em></dt>
                        <dd>{{ trans('commands.webhook_commands') }}</dd>
                    @endif
                    @if(isset($environments) && count($environments))
                        <dt><em>environments</em></dt>
                        <dd>{{ trans('environments.webhook_args') }}</dd>
                    @endif
                </dl>

                @if (isset($optional) && count($optional))
                    <h5><strong>{{ trans('commands.webhook_optional') }}</strong></h5>
                    <dl class="dl-horizontal hook_ids" id="hook_command_ids">
                        @foreach($optional as $command)
                        <dt><em>{{ $command->id }}</em></dt>
                        <dd>{{ $command->name }}</dd>
                        @endforeach
                    </dl>
                @endif

                 @if (isset($environments) && count($environments))
                    <h5><strong>{{ trans('environments.webhook_environment') }}</strong></h5>
                    <dl class="dl-horizontal hook_ids" id="hook_environment_ids">
                        @foreach($environments as $environment)
                        <dt><em>{{ $environment->id }}</em></dt>
                        <dd>{{ $environment->name }}</dd>
                        @endforeach
                    </dl>
                @endif

                <h5><strong>{{ trans('commands.webhook_curl') }}</strong></h5>
                <pre>curl -X POST {{ $project->webhook_url }} -d 'reason={{ urlencode(trans('commands.reason_example')) }}&amp;branch=master&amp;update_only=true'</pre>

                <hr />

                <h5><strong>{{ trans('commands.services') }} - Github,  Gitlab, Bitbucket &amp; Beanstalk</strong></h5>
                <p>{!! trans('commands.services_description') !!}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.close') }}</button>
            </div>
        </div>
    </div>
</div>
