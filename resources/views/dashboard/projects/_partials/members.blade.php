<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ trans('members.label') }}</h3>
        <div class="pull-right">
            <button type="button" class="btn btn-primary" title="{{ trans('members.create') }}" data-toggle="modal" data-target="#member"><span class="ion ion-plus"></span> {{ trans('members.create') }}</button>
        </div>
    </div>

    <div class="box-body" id="no_members">
        <p>{{ trans('members.none') }}</p>
    </div>

    <div class="box-body table-responsive">
        <table class="table table-striped" id="member_list">
            <thead>
                <tr>
                    <th>{{ trans('users.name') }}</th>
                    <th>{{ trans('members.level') }}</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="member-template">
        <td><%- name %></td>
        <td><%- label %></td>
        <td>
            <div class="btn-group pull-right">
                <button type="button" class="btn btn-default btn-edit" title="{{ trans('members.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#member"><i class="ion ion-edit"></i></button>
                <button type="button" class="btn btn-danger btn-delete" title="{{ trans('members.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="ion ion-android-exit"></i></button>
            </div>
        </td>
    </script>
@endpush