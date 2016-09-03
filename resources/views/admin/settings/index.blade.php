@extends('layouts.dashboard')

@section('content')

<div class="row">

<div class="col-md-12">
    <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="{{ !Session::get('current_tab') || Session::get('current_tab') =='setup' ? 'active' : null }}"><a href="#setup" data-toggle="tab">{{ trans('settings.setup') }}</a></li>
              <li class="{{ Session::get('current_tab') =='localization' ? 'active' : null }}"><a href="#localization" data-toggle="tab" aria-expanded="true">{{ trans('settings.localization') }}</a></li>
            </ul>
            <div class="tab-content">
                @include('admin.settings._partials.setup')

                @include('admin.settings._partials.localization')
            </div>
            </div>
            <!-- /.tab-content -->
          </div>

</div>
</div>

@stop