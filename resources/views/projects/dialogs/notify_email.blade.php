<div class="modal fade" id="notifyemail">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-email"></i> <span>{{ trans('notifyEmails.create') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="notifyemail_id" name="id" />
                <input type="hidden" name="project_id" value="{{ $project->id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('notifyEmails.warning') }}
                    </div>

                    <div class="form-group">
                        <label for="notifyemail_name">{{ trans('notifyEmails.name') }}</label>
                        <input type="text" class="form-control" id="notifyemail_name" name="name" placeholder="{{ trans('notifyEmails.name') }}" />
                    </div>
                    <div class="form-group">
                        <label for="notifyemail_address">{{ trans('notifyEmails.email') }}</label>
                        <input type="text" class="form-control" id="notifyemail_address" name="address" placeholder="{{ trans('notifyEmails.address') }}" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-left btn-save">{{ trans('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
