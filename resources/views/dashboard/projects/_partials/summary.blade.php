<div class="row">
    <div class="col-md-5">
        <div class="panel panel-flush">
            <div class="panel-heading">
                <h4>{{ trans('projects.repository') }}</h4>
            </div>
            <div class="panel-body">
                <table class="table table-relaxed">
                    <tbody>
                        <tr>
                            <td>{{ trans('projects.repository_path') }}</td>
                            <td class="text-right">
                                <i class="piplin {{ $project->type_icon }}"></i> <a href="{{ $project->repository_url }}" target="_blank">{{ $project->repository_path }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ trans('projects.branch') }}</td>
                            <td class="text-right"><a href="{{ $project->branch_url?:'#' }}"><span class="label label-default">{{ $project->branch }}</span></td>
                        </tr>
                        <tr>
                            <td>{{ trans('projects.change_branch') }}</td>
                            <td class="text-right">
                                {{ $project->allow_other_branch ? trans('app.yes') : trans('app.no') }}</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel panel-flush">
            <div class="panel-heading">
                <h4>{{ trans('projects.tasks') }}</h4>
            </div>
            <div class="panel-body">
                <table class="table table-relaxed">
                    <tbody>
                        <tr>
                            <td>{{ trans('projects.today') }}</td>
                            <td class="text-right">{{ number_format($today) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('projects.last_week') }}</td>
                            <td class="text-right">{{ number_format($last_week) }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('projects.total_count') }}</td>
                            <td class="text-right">{{ number_format($total_count) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel panel-flush">
            <div class="panel-heading">
                <h4>{{ trans('projects.details') }}</h4>
            </div>
            <div class="panel-body">
                <table class="table table-relaxed">
                    <tbody>
                        <tr>
                            <td>{{ trans('projects.deploy_path') }}</td>
                            <td class="text-right small">
                                {{ $project->clean_deploy_path }}
                            </td>
                        </tr>
                        <tr>
                            <td>{{ trans('projects.key') }}</td>
                            <td class="text-right"><a href="#" title="{{ trans('keys.view_ssh_key') }}" class="label label-warning" data-toggle="modal" data-target="#show_key">{{ trans('keys.ssh_key') }}</a></td>
                        </tr>
                        <tr>
                            <td>{{ trans('projects.status') }}</td>
                            <td class="text-right project-status">
                                <span class="text-{{$project->css_class}}"><i class="piplin piplin-{{ $project->icon }}"></i> <span>{{ $project->readable_status }}</span></span> / <abbr class="timeago" data-toggle="tooltip" data-placement="right" title="{{ $project->last_run }}" data-timeago="{{ $project->last_run }}"></abbr>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@if($project->description)
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-warning">
            <p>{!! nl2br($project->description) !!}</p>
        </div>
    </div>
</div>
@endif