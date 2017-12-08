<div class="modal fade" id="link" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="piplin piplin-setting"></i> <span>{{ trans('environments.link_settings') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" id="environment_link_id" name="id" />
                <input type="hidden" name="environment_id" value="{{ $targetable->id }}" />
            <div class="modal-body">
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="link_types">{{ trans('environments.link_type') }}</label>
                    <div class="col-sm-9">
                        <select name="link_type" id="link_type" class="select2 form-control">
                        @foreach ($links as $item)
                            <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                        @endforeach
                    </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="link_environments">{{ trans('tasks.environments') }}</label>
                    <div class="col-sm-9">
                        <ul class="list-unstyled">
                            @foreach ($environments as $each)
                            @if($each->id != $targetable->id)
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
                        <button type="button" class="btn btn-primary btn-save"><i class="piplin piplin-save"></i> {{ trans('app.save') }}</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
