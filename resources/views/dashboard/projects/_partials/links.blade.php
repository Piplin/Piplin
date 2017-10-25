<div class="box">
    <div class="box-header">
        <div class="pull-right">
            @if($project->can('manage'))
            <button type="button" class="btn btn-primary btn-edit" title="{{ trans('environments.link_settings') }}" data-toggle="modal" data-backdrop="static" data-target="#link"><span class="fixhub fixhub-setting"></span> {{ trans('environments.link_settings') }}</button>
            @endif
        </div>
        <h3 class="box-title">{{ trans('environments.links') }}</h3>
    </div>

    <div class="box-body" id="no_links">
        <p>{{ trans('environments.link_none') }}</p>
    </div>

    <div class="box-body table-responsive" id="link_list">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ trans('environments.link_opposite') }}</th>
                    <th>{{ trans('environments.link_type') }}</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
@include('dashboard.projects._dialogs.link')
@include('dashboard.projects._dialogs.key')

@push('templates')
    <script type="text/template" id="link-template">
        <td><%- name %></td>
        <td><%- link_type %></td>
    </script>
@endpush
