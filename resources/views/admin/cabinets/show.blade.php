@extends('layouts.admin')

@section('admin-content')

    @include('dashboard.environments._partials.servers')

    @include('dashboard.environments._dialogs.server')
@stop

@push('javascript')
    <script type="text/javascript">
        new Fixhub.ServersTab();
        Fixhub.Servers.add({!! $servers !!});
        Fixhub.targetable_id = {{ $targetable->id }};
    </script>
@endpush
