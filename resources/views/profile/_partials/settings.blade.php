<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('users.settings') }}</h3>
    </div>
    <div class="box-body">
        <form action="{{ route('profile.settings') }}" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <div class="form-group">
                <label for="skin">{{ trans('users.theme') }}</label>
                <select name="skin" id="skin" class="select2 form-control">
                    @foreach (['white', 'black', 'yellow', 'red', 'green', 'purple', 'blue'] as $colour)
                        <option value="{{ $colour }}" @if ($colour === $theme) selected @endif>{{ trans('users.' . $colour )}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="language">{{ trans('users.language') }}</label>
                <select name="language" id="language" class="select2 form-control">
                    @foreach (['en', 'zh-CN'] as $item)
                        <option value="{{ $item }}" @if ($item === $language) selected @endif>{{ trans('users.' . $item )}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-flat">{{ trans('users.save') }}</button>
            </div>
        </form>
    </div>
</div>