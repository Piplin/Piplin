        <div class="box box-defaut">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('users.avatar') }}</h3>
            </div>
            <div class="box-body">
                <div class="row">

                    <div class="col-md-12 avatar-message">
                        <div class="alert alert-success hide" role="alert">{{ trans('users.avatar_success') }}</div>
                        <div class="alert alert-danger hide" role="alert">{{ trans('users.avatar_failed') }}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="avatar">
                            <img src="{{ url('img/cropper.jpg') }}" class="img-rounded img-responsive" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <img src="{{ $current_user->avatar_url }}" class="current-avatar-preview" />

                        <div class="avatar-preview preview-md hide"></div>

                        <div id="avatar-save-buttons">
                            <button type="button" class="btn btn-primary btn-flat hide" id="save-avatar">{{ trans('users.save') }}</button>
                            @if(config('piplin.gravatar'))
                            <button type="button" class="btn btn-warning btn-flat @if (!$current_user->avatar) hide @endif " id="use-gravatar">{{ trans('users.reset_gravatar') }}</button>
                            @endif
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary btn-flat" id="upload">{{ trans('users.upload') }}</button>
                    </div>
                </div>
            </div>
            <div class="overlay" id="upload-overlay">
                <i class="piplin piplin-refresh piplin-spin"></i>
            </div>
        </div>