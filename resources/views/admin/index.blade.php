@extends('layouts.dashboard')

@section('content')

<div class="box">

        @include('admin._partials.nav')
        <div class="box-body">
        Welcome to admin control panel.
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
@stop

@section('right-buttons')
    <div class="pull-right">
        <a href="/" class="btn btn-default" title="Back to dashboard"><span class="ion ion-ios-undo-outline"></span> Back to dashboard</a>
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