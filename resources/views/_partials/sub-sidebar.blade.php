<div class="sub-sidebar">
    <div class="panel-heading">
        <h3 class="text-center">{{ trans('admin.title') }}</h3>
    </div>
    <div class="panel-body">
        <ul class="nav nav-stacked">
        @foreach($sub_menu as $key=>$val)
        <li @if($key == $current_menu) class="active"@endif><a href="{{ $val['url'] }}"><i class="piplin piplin-{{ $val['icon'] }}"></i> {{ $val['title'] }}</a></li>
        @endforeach
        </ul>
    </div>
</div>