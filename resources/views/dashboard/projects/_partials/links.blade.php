<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ trans('links.label') }}</h3>
        <div class="pull-right">
            @if($project->can('manage'))
            <button type="button" class="btn btn-primary" title="{{ trans('links.create') }}" data-toggle="modal" data-target="#link"><span class="fixhub fixhub-plus"></span> {{ trans('links.create') }}</button>
            @endif
        </div>
    </div>

    <div class="box-body" id="link_list2">
        <p>
            @foreach($oppositeEnvironments as $item)
                {{ $item->name }}
            @endforeach
        </p>
    </div>

</div>