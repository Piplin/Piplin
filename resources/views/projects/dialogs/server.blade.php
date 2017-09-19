<div class="modal fade" id="server">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-social-buffer-outline"></i> <span>{{ trans('servers.create') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="server_id" name="id" />
                <input type="hidden" name="project_id" value="{{ $project->id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('servers.warning') }}
                    </div>

                    <div class="form-group">
                        <label for="server_name">{{ trans('servers.name') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-pricetag"></i></div>
                            <input type="text" class="form-control" id="server_name" name="name" placeholder="{{ trans('servers.web') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="server_user">{{ trans('servers.connect_as') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-person"></i></div>
                            <input type="text" class="form-control" id="server_user" name="user" placeholder="deploy" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="server_address">{{ trans('servers.ip_address') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-monitor"></i></div>
                            <input type="text" class="form-control" id="server_address" name="ip_address" placeholder="192.168.0.1" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="server_port">{{ trans('servers.port') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-shuffle"></i></div>
                            <input type="number" class="form-control" id="server_port" name="port" placeholder="22" value="22" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="server_path">{{ trans('servers.path') }}</label>
                        <i class="ion ion-help" data-toggle="tooltip" data-placement="right" title="{{ trans('servers.example') }}"></i>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-folder"></i></div>
                            <input type="text" class="form-control" id="server_path" name="path" placeholder="/var/www/project" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ trans('servers.options') }}</label>
                        <div class="checkbox">
                            <label for="server_deploy_code">
                                <input type="checkbox" value="1" name="deploy_code" id="server_deploy_code" />
                                {{ trans('servers.deploy_code') }}
                            </label>
                        </div>
                        @if ($project->commands->count() > 0)
                        <div class="checkbox" id="add-server-command">
                            <label for="server_commands">
                                <input type="checkbox" value="1" name="commands" id="server_commands" checked />
                                {{ trans('servers.add_command') }}
                            </label>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-left btn-save">{{ trans('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
