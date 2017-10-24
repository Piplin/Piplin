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
                        <th width="40%">{{ trans('projects.name') }}</th>
                        <th width="30%">{{ trans('projects.deployed') }}</th>
                        <th width="30%">{{ trans('dashboard.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($group_projects['projects'] as $group_project)
                    <tr id="project_{{ $group_project->id }}">
                        <td class="name"><a href="{{ route('projects', ['id' => $group_project->id]) }}" title="{{ trans('projects.details') }}">{{ $group_project->name }}</a></td>
                        <td class="time small">
                          @if($group_project->last_run)
                          <abbr class="timeago" data-toggle="tooltip" data-placement="right" title="{{ $group_project->last_run }}" data-timeago="{{ $group_project->last_run }}"></abbr>
                          @else
                          {{ trans('app.never') }}
                          @endif
                        </td>
                        <td class="status small"><span class="text-{{$group_project->css_class}}"><i class="fixhub fixhub-{{ $group_project->icon }}"></i> <span>{{ $group_project->readable_status }}</span></span></td>
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
