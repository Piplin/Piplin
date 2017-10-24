<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ trans('links.label') }}</h3>
        <div class="pull-right">
            @if($project->can('manage'))
            <button type="button" class="btn btn-primary" title="{{ trans('links.create') }}" data-toggle="modal" data-target="#link"><span class="fixhub fixhub-plus"></span> {{ trans('links.create') }}</button>
            @endif
        </div>
    </div>

    <div class="box-body" id="no_links">
        <p>{{ trans('links.none') }}</p>
    </div>

    <div class="box-body table-responsive">
        <table class="table table-striped" id="link_list">
            <thead>
                <tr>
                    <th>{{ trans('links.name') }}</th>
                    <th>{{ trans('links.type') }}</th>
                    <th>{{ trans('links.enabled') }}</th>
                    <th class="text-right">{{ trans('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="link-template">
        <td><%- name %></td>
        <td><i class="fixhub fixhub-<%- icon %>"></i> <%- label %></td>
        <td><% if (enabled) { %>{{ trans('app.yes') }}<% } else { %>{{ trans('app.no') }}<% } %></td>
        <td>
            <div class="btn-group pull-right">
                @if($project->can('manage'))
                <button type="button" class="btn btn-default btn-edit" title="{{ trans('links.edit') }}" data-toggle="modal" data-backdrop="static" data-target="#link"><i class="fixhub fixhub-edit"></i></button>
                <button type="button" class="btn btn-danger btn-delete" title="{{ trans('links.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="fixhub fixhub-delete"></i></button>
                @endif
            </div>
        </td>
    </script>
@endpush