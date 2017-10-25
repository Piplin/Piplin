<div class="hook-config" id="hook-config-dingtalk">
    <div class="form-group">
        <label class="col-sm-3 control-label" for="hook_config_webhook">{{ trans('hooks.webhook') }}</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="hook_config_webhook" name="webhook" placeholder="https://oapi.dingtalk.com/robot/send?access_token=access_token" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label" for="hook_config_at_mobiles">{{ trans('hooks.at_mobiles') }}</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="hook_config_at_mobiles" name="at_mobiles" placeholder="156xxxx8827,189xxxx8325" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label" for="hook_config_is_at_all">{{ trans('hooks.is_at_all') }}</label>
        <div class="col-sm-9">
            <div class="checkbox">
                <label for="hook_config_is_at_all">
                    <input type="checkbox" value="1" name="is_at_all" id="hook_config_is_at_all" />{{ trans('app.yes') }}
                </label>
            </div>
        </div>
    </div>
</div>