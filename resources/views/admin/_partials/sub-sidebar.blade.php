<div class="box box-default">
<div class="box-body">
    <ul class="list-group list-group-unbordered">
        @foreach($sub_menu as $key=>$item)
        @if($item['active'])
        <li class="list-group-item active">
            <i class="ion {{ $item['icon'] }}"></i> {{ $item['title'] }}
        </li>
        @else
        <li class="list-group-item">
            <a href="{{ $item['url'] }}"><i class="ion {{ $item['icon'] }}"></i> {{ $item['title'] }}</a>
        </li>
        @endif
        @endforeach
    </ul>
</div>
</div>