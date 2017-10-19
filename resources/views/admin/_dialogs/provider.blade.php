<div class="modal fade" id="provider">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-ios-browsers-outline"></i> <span>{{ trans('providers.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="provider_id" name="id" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('providers.warning') }}
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="provider_title">{{ trans('providers.name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="title" id="provider_name" placeholder="{{ trans('providers.name') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="provider_slug">{{ trans('providers.slug') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="slug" id="provider_slug" placeholder="{{ trans('providers.slug') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="provider_icon">{{ trans('providers.icon') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="icon" id="provider_icon" placeholder="ion-android-open" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="provider_description">{{ trans('providers.description') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="description" id="provider_description" placeholder="{{ trans('providers.description') }}" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-save">{{ trans('app.save') }}</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
