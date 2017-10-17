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
                        <label for="server_environment_id">{{ trans('servers.environment') }}</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="ion ion-cube"></i></div>
                            <select id="server_environment_id" name="environment_id" class="form-control">
                                @foreach($environments as $each)
                                    <option value="{{ $each->id }}" {{ isset($environment) && $environment->id == $each->id ? 'selected="true"' : NULL }}>{{ $each->name }}</option>
                                @endforeach
                            </select>
                        </div>
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
                            <label for="server_enabled">
                                <input type="checkbox" value="1" name="enabled" id="server_enabled" />
                                {{ trans('servers.enabled') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group pull-left">
                        <button type="button" class="btn btn-primary btn-save">{{ trans('app.save') }}</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
