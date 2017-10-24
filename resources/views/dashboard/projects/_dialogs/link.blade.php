<div class="modal fade" id="link">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="fixhub fixhub-link"></i> <span>{{ trans('configFiles.create') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="environment_link_id" name="id" />
                <input type="hidden" name="environment_id" value="{{ $environment->id }}" />
            <div class="modal-body">
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="link_links">Links</label>
                    <div class="col-sm-9">
                        <select name="link_id" id="link_id" class="select2 form-control">
                        @foreach ($links as $item)
                            <option value="{{ $item->id }}">{{ $item->title }}</option>
                        @endforeach
                    </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="link_environments">{{ trans('deployments.environments') }}</label>
                    <div class="col-sm-9">
                        <ul class="list-unstyled">
                            @foreach ($environments as $each)
                            @if($each->id != $environment->id)
                            <li>
                                <div class="checkbox">
                                    <label for="link_opposite_environment_{{ $each->id }}">
                                        <input type="checkbox" class="link-environment" name="opposite_environments[]" id="link_opposite_environment_{{ $each->id }}" value="{{ $each->id }}" @if ($each->default_on === true) checked @endif/> {{ $each->name }}
                                    </label>
                                </div>
                            </li>
                            @endif
                            @endforeach
                        </ul>
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
