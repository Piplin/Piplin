<div class="tab-pane {{ !Session::get('current_tab') || Session::get('current_tab') =='setup' ? 'active' : null }}" id="setup">
  <form class="form-horizontal" method="POST">
  <input type="hidden" name="_token" value="{{ csrf_token() }}" />
  <input type="hidden" name="current_tab" value="setup" />
  <!-- The setup -->

    <div class="form-group">
      <label for="app_name" class="col-sm-2 control-label">{{ trans('app.name') }}</label>

      <div class="col-sm-10">
        <input type="text" name="app_name" class="form-control" id="app_name" placeholder="App Name" value="{{ $app_name }}">
      </div>
    </div>
    <div class="form-group">
      <label for="app_url" class="col-sm-2 control-label">{{ trans('settings.url') }}</label>

      <div class="col-sm-10">
        <input text="text" name="app_url" class="form-control" id="app_url" placeholder="{{ trans('settings.url') }}" value="{{ $app_url }}">
      </div>
    </div>

    <div class="form-group">
      <label for="app_about" class="col-sm-2 control-label">{{ trans('app.about') }}</label>

      <div class="col-sm-10">
        <textarea class="form-control" name="app_about" id="app_about" placeholder="{{ trans('app.about') }}">{{ $app_about }}</textarea>
      </div>
    </div>

    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10">
        <button type="submit" class="btn btn-info">{{ trans('app.save') }}</button>
      </div>
    </div>
    </form>
</div>
<!-- /.tab-pane -->