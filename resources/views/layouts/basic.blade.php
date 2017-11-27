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

    </head>
    <body class="hold-transition login-page">

        @yield('content')

    </body>
</html>
