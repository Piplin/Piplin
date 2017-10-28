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
        <meta name="user_id"  content="{{ $current_user->id }}" />  
    </head>
    <body class="hold-transition skin-{{ $theme }}">
        <div class="wrapper">
            @include('_partials.nav')
                <div class="content-wrapper">
                    <div class="container">
                    @include('_partials.errors')
                    <section class="content-header">
                      <div class="content-title">
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
                      </div>
                        @yield('right-buttons')
                        @if(Request::is('/'))
                            @include('dashboard._partials.update')
                        @endif
                        <div class="alert alert-danger" id="socket_offline">
                            <h4><i class="icon fixhub fixhub-warning"></i> {{ trans('app.socket_error') }}</h4>
                            {!! trans('app.socket_error_info') !!}
                        </div>
                    </section>
                    <section class="content" id="app">
                        @yield('content')
                    </section>
                </div>
            </div>
            @include('dashboard._partials.trash_dialog')
            @include('_partials.footer')
        </div>

        <script src="{{ cdn('js/vendor.js') }}"></script>
        <script src="/js-localization/config"></script>
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
