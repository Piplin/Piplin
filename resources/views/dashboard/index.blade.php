@extends('layouts.dashboard')

@section('content')
    @if($current_user->isAdmin)
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
          <a href="{{ route('admin.projects.index') }}">
          <div class="info-box">
            <span class="info-box-icon bg-red"><i class="ion ion-social-codepen-outline"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">{{ trans('dashboard.projects') }}</span>
              <span class="info-box-number">{{ $project_count }}</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          </a>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="{{ route('admin.groups.index') }}">
          <div class="info-box">
            <span class="info-box-icon bg-green"><i class="ion ion-ios-browsers-outline"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">{{ trans('app.groups') }}</span>
              <span class="info-box-number">{{ $group_count }}</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
          </a>
        </div>
        <!-- fix for small devices only -->
        <div class="clearfix visible-sm-block"></div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12">
          <a href="{{ route('admin.templates.index') }}">
          <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="ion ion-ios-paper-outline"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">{{ trans('app.templates') }}</span>
              <span class="info-box-number">{{ $template_count }}</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
          </a>
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12">
          <a href="{{ route('admin.users.index') }}">
          <div class="info-box">
            <span class="info-box-icon bg-purple"><i class="ion ion-ios-people-outline"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">{{ trans('app.users') }}</span>
              <span class="info-box-number">{{ $user_count }}</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
          </a>
        </div>
        <!-- /.col -->
    </div>
    @endif
    <div class="row">
      <div class="col-md-8">
          <div class="box box-info">
              <div class="box-header">
                  <h3 class="box-title">{{ trans_choice('dashboard.latest', 2) }}</h3>
              </div>
              <div class="box-body" id="timeline">
                  @include('dashboard.timeline')
              </div>
          </div>
          <!-- AREA CHART -->
          <div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">{{ trans('dashboard.stats') }}</h3>
            </div>
            <div class="box-body chart-responsive">
              <div class="chart" id="revenue-chart" style="height: 300px;"></div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
      </div>
      <div class="col-md-4">
        @include('dashboard._partials.sidebar')
      </div>
    </div>
  </div>
@stop

@push('javascript')
<script type="text/javascript">
$(function () {
    "use strict";

    // AREA CHART
    var area = new Morris.Area({
      element: 'revenue-chart',
      resize: true,
      data: [
        {y: '2016-02', item1: 4767, item2: 3597},
        {y: '2016-03', item1: 6810, item2: 1914},
        {y: '2016-04', item1: 3670, item2: 4293},
        {y: '2016-05', item1: 4820, item2: 5795},
        {y: '2016-06', item1: 15073, item2: 5967},
        {y: '2016-07', item1: 10687, item2: 4460},
        {y: '2016-08', item1: 8432, item2: 5713}
      ],
      xkey: 'y',
      ykeys: ['item1', 'item2'],
      labels: ['Projects', 'Deployments'],
      lineColors: ['#2faa60', '#3498db'],
      hideHover: 'auto'
    });
});
</script>
@endpush