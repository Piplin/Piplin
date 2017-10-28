@if ($errors->any())
@include('_partials._error', ['level' => 'danger', 'title' => Session::get('title'), 'message' => $errors->all(':message')])
@endif

@if ($message = Session::get('success'))
@include('_partials._error', ['level' => 'success', 'title' => Session::get('title'), 'message' => $message])
@endif

@if ($message = Session::get('warning'))
@include('_partials._error', ['level' => 'warning', 'title' => Session::get('title'), 'message' => $message])
@endif

@if ($message = Session::get('info'))
@include('_partials._error', ['level' => 'info', 'title' => Session::get('title'), 'message' => $message])
@endif