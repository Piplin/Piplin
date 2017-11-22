<ul class="nav nav-tabs">
    @foreach($sub_menu as $key=>$item)
        <li class="{{ isset($current_child) && $key == $current_child ? 'active' : null }}">
            <a href="{{ $item['url'] }}"><i class="piplin piplin-{{ $item['icon'] }}"></i> {{ $item['title'] }}</a>
        </li>
    @endforeach
</ul>