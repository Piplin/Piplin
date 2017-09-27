<div class="hook-config" id="hook-config-slack">
    <div class="form-group">
        <label for="hook_config_icon">{{ trans('hooks.icon') }}</label>
        <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="{{ trans('hooks.icon_info') }}"></i>
        <div class="input-group">
            <div class="input-group-addon"><i class="ion ion-image"></i></div>
            <input type="text" class="form-control" id="hook_config_icon" name="icon" placeholder=":ghost:" />
        </div>
    </div>
    <div class="form-group">
        <label for="hook_config_hook">{{ trans('hooks.channel') }}</label>
        <div class="input-group">
            <div class="input-group-addon"><i class="ion ion-android-share-alt"></i></div>
            <input type="text" class="form-control" id="hook_config_channel" name="channel" placeholder="#slack" />
        </div>
    </div>
    <div class="form-group">
        <label for="hook_config_webhook">{{ trans('hooks.webhook') }}</label>
        <div class="input-group">
            <div class="input-group-addon"><i class="ion ion-android-open"></i></div>
            <input type="text" class="form-control" id="hook_config_webhook" name="webhook" placeholder="https://hooks.slack.com/services/" />
        </div>
    </div>
</div>