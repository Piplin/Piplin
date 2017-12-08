<ol class="breadcrumb">
    @if($in_admin)
        <li><a href="{{ route('admin') }}">{{ trans('admin.title') }}</a>
    @else
        <li><a href="{{ route('dashboard') }}">{{ trans('dashboard.title') }}</a>
    @endif
    @if(isset($breadcrumb))
        @foreach($breadcrumb as $entry)
            <li><a href="{{ $entry['url'] }}">{{ $entry['label'] }}</a></li>
        @endforeach
    @endif
    @if(isset($title))
        <li>{{ $title }}</li>
    @endif
</ol>