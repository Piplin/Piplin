@if ($is_outdated)
<div class="alert alert-success alert-dismissible" id="update-available">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h4><i class="icon ion ion-checkmark"></i> {{ trans('app.update_available') }}</h4>
    {!! trans('app.outdated', ['current' => $current_version, 'latest' => $latest_version, 'link' => 'https://github.com/fixhub/fixhub/releases/latest' ]) !!}
</div>
@endif
