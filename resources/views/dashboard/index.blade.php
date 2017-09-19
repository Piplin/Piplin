@extends('layouts.dashboard')

@section('content')
    @if($current_user->isAdmin)
      @include('dashboard._partials.shortcut')
    @endif
    <div class="row">
      <div class="col-md-8">
          <div class="box box-info">
              <div class="box-header">
                  <h3 class="box-title"><i class="ion ion-clock"></i> {{ trans_choice('dashboard.latest', 2) }}</h3>
              </div>
              <div class="box-body" id="timeline">
                  @include('dashboard.timeline')
              </div>
          </div>
      </div>
      <div class="col-md-4">
        @include('dashboard._partials.sidebar')
      </div>
    </div>
  </div>
@stop