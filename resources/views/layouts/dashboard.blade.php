<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>{{ $app_name }} | Piplin - A continuous integration and delivery system</title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport" />
        <link rel="icon" type="image/png" href="/img/favicon.ico">
        <link rel="shortcut icon" href="/img/favicon.png" type="image/x-icon">
        <!-- Style -->
        <link href="{{ cdn('css/vendor.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ cdn('css/app.css') }}" rel="stylesheet" type="text/css" />

        <meta name="token" content="{{ Session::token() }}" />
        <meta name="socket_url" content="{{ config('piplin.socket_url') }}" />
        <meta name="jwt" content="{{ Session::get('jwt') }}" />
        <meta name="locale" content="{{ $language }}" />
        <meta name="user_id"  content="{{ $current_user->id }}" />  
    </head>
    <body class="hold-transition skin-default {{ isset($sub_menu) ? 'has-sub-sidebar' : null }}">
        <div class="wrapper">
            @include('_partials.sidebar')
            @if(isset($sub_menu))
                @include('_partials.sub-sidebar')
            @endif
            <div class="content-wrapper">
                <div class="row">

                <div class="col-md-12">

                @include('_partials.errors')
                <section class="content-header">
                  <div class="content-title">
                    @include('_partials.breadcrumb')
                  </div>
                    @yield('right-buttons')
                    <div class="alert alert-danger" id="socket_offline">
                        <h4><i class="icon piplin piplin-warning"></i> {{ trans('app.socket_error') }}</h4>
                        {!! trans('app.socket_error_info') !!}
                    </div>
                </section>
                <section class="content" id="app">
                    @yield('content')
                </section>
                @include('_partials.footer')
                @if(!$in_admin)
                    @include('dashboard.projects._dialogs.create')
                @endif
                @include('dashboard._partials.trash_dialog')
            </div>
            </div>
            </div>
        
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
