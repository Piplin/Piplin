<div class="modal fade" id="command">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-code"></i> <span>{{ trans('commands.create') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="command_id" name="id" />
                <input type="hidden" name="targetable_type" value="{{ $targetable_type }}" />
                <input type="hidden" name="targetable_id" value="{{ $targetable_id }}" />
                <input type="hidden" id="command_step" name="step" value="After" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('commands.warning') }}
                    </div>

                    <div class="form-group">
                        <label for="command_name">{{ trans('commands.name') }}</label>
                        <input type="text" class="form-control" name="name" id="command_name" placeholder="{{ trans('commands.migrations') }}" />
                    </div>
                    <div class="form-group">
                        <label for="command_user">{{ trans('commands.run_as') }}</label>
                        <input type="text" class="form-control" name="user" id="command_user" placeholder="{{ trans('commands.default') }}" />
                    </div>
                    <div class="form-group">
                        <label for="command_script">{{ trans('commands.bash') }}</label>
                        <div id="command_script" class="form-control"></div>
                        <h5><a data-toggle="collapse" data-parent="#accordion" href="#tokens">{{ trans('commands.options') }}</a></h5>

                        <div class="panel-collapse collapse" id="tokens">
                            <ul class="list-unstyled">
                                <li><code>@{{ project_path }}</code> - {{ trans('commands.project_path') }}, {{ trans('commands.example') }} <span class="label label-default">/var/www</span></li>
                                <li><code>@{{ release }}</code> - {{ trans('commands.release_id') }}, {{ trans('commands.example') }} <span class="label label-default">{{ date('YmdHis') }}</span></li>
                                <li><code>@{{ release_path }}</code> - {{ trans('commands.release_path') }}, {{ trans('commands.example') }} <span class="label label-default">/var/www/releases/{{ date('YmdHis') }}</span></li>
                                <li><code>@{{ branch }}</code> - {{ trans('commands.branch') }}, {{ trans('commands.example') }} <span class="label label-default">master</span></li>
                                <li><code>@{{ sha }}</code> - {{ trans('commands.sha') }}, {{ trans('commands.example') }} <span class="label label-default">1def37e6f6fd15c50efe53e090308861ec8a8288</span></li>
                                <li><code>@{{ short_sha }}</code> - {{ trans('commands.short_sha') }}, {{ trans('commands.example') }} <span class="label label-default">1def37e</span></li>
                                <li><code>@{{ deployer_email }}</code> - {{ trans('commands.deployer_email') }}, {{ trans('commands.example') }} <span class="label label-default">{{ $current_user->email }}</span></li>
                                <li><code>@{{ deployer_name }}</code> - {{ trans('commands.deployer_name') }}, {{ trans('commands.example') }} <span class="label label-default">{{ $current_user->name }}</span></li>
                                <li><code>@{{ committer_email }}</code> - {{ trans('commands.committer_email') }}, {{ trans('commands.example') }} <span class="label label-default">phecho@phecho.com</span></li>
                                <li><code>@{{ committer_name }}</code> - {{ trans('commands.committer_name') }}, {{ trans('commands.example') }} <span class="label label-default">Phecho</span></li>
                            </ul>
                        </div>
                    </div>
                    @if (count($project->servers))
                    <div class="form-group">
                        <label for="command_servers">{{ trans('commands.servers') }}</label>
                        <ul class="list-unstyled">
                            @foreach ($project->servers as $server)
                            <li>
                                <div class="checkbox">
                                    <label for="command_server_{{ $server->id }}">
                                        <input type="checkbox" class="command-server" name="servers[]" id="command_server_{{ $server->id }}" value="{{ $server->id }}" /> {{ $server->name }} ({{ $server->user }}&commat;{{ $server->ip_address }})
                                    </label>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <div class="form-group">
                        <label>{{ trans('commands.optional') }}</label>
                        <div class="checkbox">
                            <label for="command_optional">
                                <input type="checkbox" value="1" name="optional" id="command_optional" />
                                {{ trans('commands.optional_description') }}
                            </label>
                        </div>

                        <div class="checkbox hide" id="command_default_on_row">
                            <label for="command_default_on">
                                <input type="checkbox" value="1" name="default_on" id="command_default_on" />
                                {{ trans('commands.default_description') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-left btn-save">{{ trans('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
