<div class="tab-pane {{ Session::get('current_tab') =='localization' ? 'active' : null }}" id="localization">
  <form class="form-horizontal" method="POST">
  <input type="hidden" name="_token" value="{{ csrf_token() }}" />
  <input type="hidden" name="current_tab" value="localization" />
  <!-- The localization -->
    <div class="form-group">
      <label for="locale" class="col-sm-2 control-label">{{ trans('settings.language') }}</label>

      <div class="col-sm-10">
        <select name="app_locale" id="app_locale" class="form-control">
            @foreach (['en', 'zh-CN'] as $item)
                <option value="{{ $item }}" @if ($item === $app_locale) selected @endif>{{ trans('users.' . $item )}}</option>
            @endforeach
         </select>
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