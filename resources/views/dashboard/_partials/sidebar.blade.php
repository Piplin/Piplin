@if($current_user->isAdmin)
<div class="box box-info">
  <div class="box-header">
    <h3 class="box-title">{{ $app_name }}</h3>
  </div>
  <div class="box-body text-center">
    <p>
      <div class="btn-group">
      <a href="/admin/projects" class="btn btn-info"><i class="ion ion-edit"></i>{{ trans('projects.manage') }}</a>
      <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
        <span class="caret"></span>
        <span class="sr-only">Toggle Dropdown</span>
      </button>
      <ul class="dropdown-menu" role="menu">
        <li><a href="/admin/groups">{{ trans('app.groups') }}</a></li>
      </ul>
      </div>
    </p>
  </div>
  <div class="box-footer text-center">
    {{ $app_about }}
  </div>
<!-- /.box-footer -->
</div>
@endif

@if (!count($projects))
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">{{ trans('dashboard.projects') }}</h3>
        </div>
        <div class="box-body">
            <p>{{ trans('dashboard.no_projects') }}</p>
        </div>
    </div>
@else
@foreach ($projects as $group => $group_projects)
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">{{ $group_projects['group'] }}</h3>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="30%">{{ trans('projects.name') }}</th>
                        <th width="35%">{{ trans_choice('dashboard.latest', 1) }}</th>
                        <th width="20%">{{ trans('dashboard.status') }}</th>
                        <th width="10%"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($group_projects['projects'] as $group_project)
                    <tr id="project_{{ $group_project->id }}">
                        <td>{{ $group_project->id }}</td>
                        <td><a href="{{ route('projects', ['id' => $group_project->id]) }}" title="{{ trans('projects.project_details') }}">{{ $group_project->name }}</a></td>
                        <td class="small">{{ $group_project->last_run ? $group_project->last_run->format('m-d H:i') : trans('app.never') }}</td>
                        <td><span class="label label-{{ $group_project->css_class }}"><i class="ion ion-{{ $group_project->icon }}"></i> <span>{{ $group_project->readable_status }}</span></span></td>
                        <td>
                            <div class="btn-group pull-right">
                                @if(isset($group_project->url))
                                <a href="{{ $group_project->url }}" class="btn btn-xs btn-default" title="{{ trans('dashboard.site') }}" target="_blank"><i class="ion ion-earth"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
@endif

<div class="box">
  <div class="box-header">
    <h3 class="box-title">{{ trans('app.tips') }}</h3>
  </div>
  <div class="box-body">
    <p>
    {!! (isset($tip) && $tip) ? $tip->body : null !!}
    </p>
  </div>
<!-- /.box-footer -->
</div>

<div class="box">
<div class="box-header with-border">
  <h3 class="box-title">{{ trans('app.links') }}</h3>
</div>
<!-- /.box-header -->
<div class="box-body">
  <ul class="links-list link-list-in-box">
    @foreach($links as $link)
    <li class="item">
      <div class="link-info">
        <a href="{{ $link->url }}" target="_blank" class="link-title">@if($link->cover)<img src="{{ $link->cover }}"> @else {{ $link->title }} @endif</a>
        @if(!$link->cover)
            <span class="link-description">
              {{ $link->description }}
            </span>
        @endif
      </div>
    </li>
    <!-- /.item -->
    @endforeach
  </ul>
</div>
<!-- /.box-body -->