<div class="box">
    <div class="box-header">
        <div class="pull-right">
            <button type="button" class="btn btn-success" title="{{ trans('checkUrls.create') }}" data-toggle="modal" data-target="#checkurl"><span class="ion ion-plus"></span> {{ trans('checkUrls.create') }}</button>
        </div>
        <h3 class="box-title">{{ trans('checkUrls.label') }}</h3>
    </div>


    <div class="box-body" id="no_checkurls">
        <p>{{ trans('checkUrls.none') }}</p>
    </div>

    <div class="box-body table-responsive">
        <table class="table table-striped" id="checkurl_list">
            <thead>
                <tr>
                    <th width="10%">{{ trans('checkUrls.title') }}</th>
                    <th width="45%">{{ trans('checkUrls.url') }}</th>
                    <th width="10%">{{ trans('checkUrls.frequency') }}</th>
                    <th width="15%">最近检测</th>
                    <th width="10%">{{ trans('checkUrls.last_status') }}</th>
                    <th width="10%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="checkUrls-template">
        <td><%- title %></td>
        <td><%- url %></td>
        <td><%- interval_label %></td>
        <td><%- updated_at %></td>
        <td>
            <span class="label label-<%- status_css %>">
                <i class="ion ion-<%-icon_css %>"></i>
                <%- status %>
            </span>
        </td>
        <td>
            <div class="btn-group pull-right">
                <button type="button" class="btn btn-default btn-edit" title="{{ trans('checkUrls.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#checkurl"><i class="ion ion-compose"></i></button>
                <button type="button" class="btn btn-danger btn-delete" title="{{ trans('app.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="ion ion-trash-a"></i></button>
            </div>
        </td>
    </script>
@endpush
