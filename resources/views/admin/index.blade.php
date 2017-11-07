@extends('layouts.admin')

@section('admin-content')
<div class="panel-body">
    <div class="col-md-12">
        <div class="box box-default">
              <div class="box-header">
                  <h3 class="box-title"><i class="fixhub fixhub-clock"></i> {{ trans_choice('dashboard.latest', 2) }}</h3>
              </div>
              <div class="box-body" id="timeline">
                  @include('dashboard.timeline')
              </div>
          </div>
    </div>
</div>
@stop

@section('right-buttons')
    <div class="pull-right">
        <a href="/" class="btn btn-default" title="Back to dashboard"><span class="fixhub fixhub-left"></span> {{ trans('dashboard.title') }}</a>
    </div>
@stop