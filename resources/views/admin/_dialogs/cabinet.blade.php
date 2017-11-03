<div class="modal fade" id="cabinet" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="fixhub fixhug-cabinet"></i> <span>{{ trans('cabinets.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="cabinet_id" name="id" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon fixhub fixhub-warning"></i> {{ trans('cabinets.warning') }}
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="cabinet_name">{{ trans('cabinets.name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="name" id="cabinet_name" placeholder="{{ trans('cabinets.name_placeholder') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="cabinet_name">{{ trans('cabinets.description') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="description" id="cabinet_description" placeholder="{{ trans('cabinets.description') }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="cabinet_key_id">{{ trans('cabinets.key') }}</label>
                        <div class="col-sm-9">
                        <select id="cabinet_key_id" name="key_id" class="select2 form-control">
                            @foreach($keys as $key)
                                <option value="{{ $key->id }}">{{ $key->name }}</option>
                            @endforeach
                        </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-save"><i class="fixhub fixhub-save"></i> {{ trans('app.save') }}</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>