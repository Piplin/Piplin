<div class="modal fade" id="server" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="fixhub fixhub-server"></i> <span>{{ trans('servers.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="server_id" name="id" />
                <input type="hidden" name="targetable_type" value="{{ $targetable_type }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon fixhub fixhub-warning"></i> {{ trans('servers.warning') }}
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="server_targetable_id">{{ trans('servers.environment') }}</label>
                        <div class="col-sm-9">
                            <select id="server_targetable_id" name="targetable_id" class="form-control select2">
                                @foreach($environments as $each)
                                    <option value="{{ $each->id }}" {{ isset($targetable) && $targetable->id == $each->id ? 'selected="true"' : NULL }}>{{ $each->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="server_name">{{ trans('servers.name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="server_name" name="name" placeholder="{{ trans('servers.web') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="server_address">{{ trans('servers.ip_address') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="server_address" name="ip_address" placeholder="192.168.0.1" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="server_port">{{ trans('servers.port') }}</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="server_port" name="port" placeholder="22" value="22" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="server_user">{{ trans('servers.connect_as') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="server_user" name="user" placeholder="fixhub" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="server_path">{{ trans('servers.path') }}
                        <i class="fixhub fixhub-help" data-html="true" data-toggle="tooltip" data-placement="right" title="{!! trans('servers.example') !!}"></i></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="server_path" name="path" placeholder="/var/www/project" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('servers.options') }}</label>
                        <div class="col-sm-9 checkbox">
                            <label for="server_enabled">
                                <input type="checkbox" value="1" name="enabled" id="server_enabled" />
                                {{ trans('servers.enabled') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-save"><i class="fixhub fixhub-save"></i> {{ trans('app.save') }}</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
