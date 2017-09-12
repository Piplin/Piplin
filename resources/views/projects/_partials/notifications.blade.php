<div class="box">
    <div class="box-header">
        <div class="pull-right">
            <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-success" title="{{ trans('notifySlacks.create') }}" data-toggle="modal" data-target="#notifyslack"><span class="ion ion-plus"></span> {{ trans('notifySlacks.create') }}</button>
        </div>
        <h3 class="box-title">{{ trans('notifySlacks.label') }}</h3>
    </div>


    <div class="box-body" id="no_notifyslacks">
        <p>{{ trans('notifySlacks.none') }}</p>
    </div>

    <div class="box-body table-responsive">
        <table class="table table-striped" id="notifyslack_list">
            <thead>
                <tr>
                    <th width="20%">{{ trans('notifySlacks.name') }}</th>
                    <th width="40%">{{ trans('notifySlacks.channel') }}</th>
                    <th width="20%">{{ trans('notifySlacks.notify_failure_only') }}</th>
                    <th width="20%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <div class="pull-right">
            <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-success" title="{{ trans('notifyEmails.create') }}" data-toggle="modal" data-target="#notifyemail"><span class="ion ion-plus"></span> {{ trans('notifyEmails.create') }}</button>
        </div>
        <h3 class="box-title">{{ trans('notifyEmails.label') }}</h3>
    </div>


    <div class="box-body" id="no_notifyemails">
        <p>{{ trans('notifyEmails.none') }}</p>
    </div>

    <div class="box-body table-responsive">
        <table class="table table-striped" id="notifyemail_list">
            <thead>
                <tr>
                    <th width="20%">{{ trans('notifyEmails.name') }}</th>
                    <th width="40%">{{ trans('notifyEmails.email') }}</th>
                    <th width="20%"></th>
                    <th width="20%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="notifyslack-template">
        <td><%- name %></td>
        <td><%- channel %></td>
        <td>
            <% if (failure_only) { %>
                {{ trans('app.yes') }}
            <% } else { %>
                {{ trans('app.no') }}
            <% } %>
        </td>
        <td>
            <div class="btn-group pull-right">
                <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-default btn-edit" title="{{ trans('notifySlacks.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#notifyslack"><i class="ion ion-compose"></i></button>
                <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-danger btn-delete" title="{{ trans('notifySlacks.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="ion ion-trash-a"></i></button>
            </div>
        </td>
    </script>

    <script type="text/template" id="notifyemail-template">
        <td><%- name %></td>
        <td><%- email %></td>
        <td></td>
        <td>
            <div class="btn-group pull-right">
                <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-default btn-edit" title="{{ trans('notifyEmails.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#notifyemail"><i class="ion ion-compose"></i></button>
                <button type="button" {{ $current_user->is_admin ?: 'disabled="true"' }} class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="ion ion-trash-a"></i></button>
            </div>
        </td>
    </script>
@endpush
