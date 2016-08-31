<div class="modal fade" id="heartbeat">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="ion ion-heartbeat"></i> <span>{{ trans('heartbeats.create') }}</span></h4>
            </div>
            <form role="form">
                <input type="hidden" id="heartbeat_id" name="id" />
                <input type="hidden" name="project_id" value="{{ $project->id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon ion ion-alert"></i> {{ trans('heartbeats.warning') }}
                    </div>

                    <div class="form-group">
                        <label for="heartbeat_name">{{ trans('heartbeats.name') }}</label>
                        <input type="text" class="form-control" id="heartbeat_name" name="name" placeholder="{{ trans('heartbeats.my_cronjob') }}" />
                    </div>

                    <div class="form-group">
                        <label for="heartbeat_interval">{{ trans('heartbeats.interval') }}</label>
                        <ul class="list-unstyled">
                            @foreach ([10, 30, 60, 120, 720, 1440, 10080] as $time)
                            <li>
                                <div class="radio">
                                    <label for="heartbeat_interval_{{ $time }}">
                                        <input type="radio" class="heartbeat-interval" name="interval" id="heartbeat_interval_{{ $time }}" value="{{ $time }}" /> {{ trans('heartbeats.interval_' . $time) }}
                                    </label>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-right btn-save">{{ trans('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
