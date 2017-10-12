<div class="trigger-config" id="trigger-config-schedule">
    <div class="form-group">
        <label for="trigger_config_interval">{{ trans('triggers.interval') }}</label>
        <div class="input-group">
            <span class="input-group-addon"><i class="ion ion-clock"></i></span>
            <input type="text" class="form-control" id="trigger_config_interval" name="interval" placeholder="30" />
        </div>
    </div>
    <div class="form-group">
        <label for="trigger_config_interval2">{{ trans('triggers.interval') }}</label>
            <div class="row">
                <div class="col-xs-6">
                    <input type="radio" class="schedule-editor" name="interval2" value="daily" /> Daily
                </div>
                <div class="col-xs-6">
                    <input type="radio" class="schedule-editor" name="interval2" value="advanced" /> Cron expression
                </div>
            </div>
            <div class="schedule-editor-container" id="daily-form">
                <select class="select2" name="incrementInMinutes">
                    <option value="0" selected="selected">once per day</option>
                    <option value="180">every 3 hours</option>
                    <option value="120">every 2 hours</option>
                    <option value="60">every hour</option>
                </select>
            </div>
            <div class="schedule-editor-container" id="advanced-form">
                <input type="text" class="form-control" name="cronString" value="0 0 0 ? * *" />
            </div>
    </div>
</div>