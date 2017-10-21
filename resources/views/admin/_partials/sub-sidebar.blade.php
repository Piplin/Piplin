<div class="box box-default">
<div class="box-body">
    <ul class="nav nav-pills nav-stacked">
        @foreach($sub_menu as $key=>$item)
        <li class="{{ $item['active'] ? 'active' : null }}">
            <a href="{{ $item['url'] }}"><i class="fixhub fixhub-{{ $item['icon'] }}"></i> {{ $item['title'] }}</a>
        </li>
        @endforeach
    </ul>
</div>
</div>