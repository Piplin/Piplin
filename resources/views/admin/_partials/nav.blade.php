<ul class="nav nav-tabs">
    @foreach($sub_menu as $key=>$item)
        <li class="{{ $item['active'] ? 'active' : null }}">
            <a href="{{ $item['url'] }}"><i class="fixhub fixhub-{{ $item['icon'] }}"></i> {{ $item['title'] }}</a>
        </li>
    @endforeach
</ul>