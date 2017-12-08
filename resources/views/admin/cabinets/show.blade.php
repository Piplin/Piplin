@extends('layouts.admin')

@section('admin-content')

    @include('dashboard.environments._partials.servers')

    @include('dashboard.environments._dialogs.server')
@stop

@push('javascript')
    <script type="text/javascript">
        new Piplin.ServersTab();
        Piplin.Servers.add({!! $servers !!});
        Piplin.targetable_id = {{ $targetable->id }};
    </script>
@endpush
