<div class="box box-primary">
<div class="box-body">
    <h3>{{ isset($sub_title) ? $sub_title : null }}</h3>
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