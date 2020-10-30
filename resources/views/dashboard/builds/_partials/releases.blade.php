<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ trans('releases.label') }}</h3>
    </div>


    <div class="box-body" id="no_releases">
        <p>{{ trans('releases.none') }}</p>
    </div>

    <div class="box-body table-responsive">
        <table class="table table-striped" id="release_list">
            <thead>
                <tr>
                    <th width="30%">{{ trans('releases.name') }}</th>
                    <th class="text-right">{{ trans('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('templates')
    <script type="text/template" id="release-template">
        <td><a href="/task/<%- task_id %>"><%- name %></a></td>
        <td>
            <div class="btn-group pull-right">
                @if($project->can('manage'))
                <a  href="{{ route('deployments', ['deployment' => $project->deployPlan->id, 'tab' => 'deploy']) }}?release_id=<%- id %>" class="btn btn-info"><i class="piplin piplin-deploy"></i></a>
                <button type="button" class="btn btn-danger btn-delete" title="{{ trans('releases.delete') }}" data-toggle="modal" data-backdrop="static" data-target="#model-trash"><i class="piplin piplin-delete"></i></button>
                @endif
            </div>
        </td>
    </script>
@endpush
