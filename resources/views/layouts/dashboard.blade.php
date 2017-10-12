<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>{{ $app_name }} | Fixhub - A web deployment system</title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport" />

        <!-- Style -->
        <link href="{{ cdn('css/vendor.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ cdn('css/app.css') }}" rel="stylesheet" type="text/css" />

        <meta name="token" content="{{ Session::token() }}" />
        <meta name="socket_url" content="{{ config('fixhub.socket_url') }}" />
        <meta name="jwt" content="{{ Session::get('jwt') }}" />
        <meta name="locale" content="{{ $language }}" />
    </head>
    <body class="hold-transition skin-{{ $theme }}">
        <div class="wrapper">
            @include('dashboard._partials.nav')
                <div class="content-wrapper">
                    <div class="container">
                    <section class="content-header">
                        @yield('right-buttons')

                        <h1>{{ $title }} @if(isset($subtitle)) <small>{{ $subtitle }}</small>@endif</h1>
                        @if(Request::is('/'))
                            @include('dashboard._partials.update')
                        @endif

                        <div class="alert alert-danger" id="socket_offline">
                            <h4><i class="icon ion ion-eye-disabled"></i> {{ trans('app.socket_error') }}</h4>
                            {!! trans('app.socket_error_info') !!}
                        </div>

                        @if(isset($breadcrumb))
                        <ol class="breadcrumb">
                            <li><a href="{{ route('dashboard') }}">{{ trans('dashboard.title') }}</a>
                            @foreach($breadcrumb as $entry)
                            <li><a href="{{ $entry['url'] }}">{{ $entry['label'] }}</a></li>
                            @endforeach
                            <li class="active">{{ $title }}</li>
                        </ol>
                        @endif
                    </section>
                     @yield('subsider')
                    <section class="content" id="app">
                        @yield('content')
                    </section>
                </div>
            </div>
            @include('dashboard._partials.trash_dialog')
            @include('dashboard._partials.footer')
        </div>

        <script src="{{ cdn('js/vendor.js') }}"></script>
        <script src="/js-localization/messages"></script>
        <script src="{{ cdn('js/app.js') }}"></script>
        @if (\Route::is('admin*'))
        <script src="{{ cdn('js/admin.js') }}"></script>
        @else
        <script src="{{ cdn('js/dashboard.js') }}"></script>
        @endif

        @stack('templates')
        @stack('javascript')
    </body>
</html>
