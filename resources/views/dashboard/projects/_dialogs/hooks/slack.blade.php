<div class="hook-config" id="hook-config-slack">
    <div class="form-group">
        <label class="col-sm-3 control-label" for="hook_config_icon">{{ trans('hooks.icon') }}
        <i class="ion ion-help-circled" data-toggle="tooltip" data-placement="right" title="{{ trans('hooks.icon_info') }}"></i></label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="hook_config_icon" name="icon" placeholder=":ghost:" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label" for="hook_config_hook">{{ trans('hooks.channel') }}</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="hook_config_channel" name="channel" placeholder="#slack" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label" for="hook_config_webhook">{{ trans('hooks.webhook') }}</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="hook_config_webhook" name="webhook" placeholder="https://hooks.slack.com/services/" />
        </div>
    </div>
</div>