@extends('layouts.dashboard')

@section('content')
    {{ $plan->name }} of {{ $plan->project->name }}
@stop