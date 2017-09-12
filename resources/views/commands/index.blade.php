@extends('layouts.dashboard')

@section('content')
    <div class="row">
        @include('commands._partials.list', [ 'step' => 'before', 'action' => $action - 1 ])
        @include('commands._partials.list', [ 'step' => 'after', 'action' => $action + 1 ])
    </div>

    @include('commands.dialog')
@stop

@push('javascript')
    <script type="text/javascript">
        new app.CommandsTab();
        app.Commands.add({!! $commands !!});
    </script>
    <script src="{{ cdn('js/ace.js') }}"></script>
@endpush

@push('templates')
    <script type="text/template" id="command-template">
        <td data-command-id="<%- id %>"><%- name %></td>
        <td>
            <%= user ? user : '{{ trans('commands.default') }}' %>
        </td>
        <td>
            <% if (optional) { %>
                {{ trans('app.yes') }}
            <% } else { %>
                {{ trans('app.no') }}
            <% } %>
        </td>
        <td>
            <div class="btn-group pull-right">
                <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-default btn-edit" title="{{ trans('commands.edit') }}" data-toggle="modal" data-target="#command"><i class="ion ion-compose"></i></button>
                <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-danger btn-delete" title="{{ trans('commands.delete') }}" data-toggle="modal" data-target="#model-trash"><i class="ion ion-trash-a"></i></button>
            </div>
        </td>
    </script>
@endpush
