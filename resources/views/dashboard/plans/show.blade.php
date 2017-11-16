@extends('layouts.dashboard')

@section('content')
    @include('dashboard.projects._partials.summary')
    {{ $plan->name }} of {{ $plan->project->name }}
@stop