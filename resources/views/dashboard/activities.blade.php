@extends('layouts.dashboard')

@section('content')
    <div class="row">
      <div class="col-md-12">
          <div class="box box-default">
              <div class="box-header">
                  <h3 class="box-title"><i class="piplin piplin-clock"></i> {{ trans_choice('dashboard.latest', 2) }}</h3>
              </div>
              <div class="box-body" id="timeline">
                  @include('dashboard.timeline')
              </div>
          </div>
      </div>
    </div>
@stop