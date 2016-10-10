<div class="modal fade" id="link">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-ios-browsers-outline"></i> <span>{{ trans('links.create') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="link_id" name="id" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('links.warning') }}
                    </div>

                    <div class="form-group">
                        <label for="link_title">{{ trans('links.title') }}</label>
                        <input type="text" class="form-control" name="title" id="link_title" placeholder="{{ trans('links.title') }}" />
                    </div>
                    <div class="form-group">
                        <label for="link_url">{{ trans('links.url') }}</label>
                        <input type="text" class="form-control" name="url" id="link_url" placeholder="{{ trans('links.url') }}" />
                    </div>
                    <div class="form-group">
                        <label>{{ trans('links.description') }}</label>
                        <i class="ion ion-help" data-toggle="tooltip" data-placement="right" title="{{ trans('links.description_info') }}"></i>
                        <textarea name="description" rows="10" id="link_description" class="form-control" placeholder="{{ trans('links.description') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-left btn-save">{{ trans('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
