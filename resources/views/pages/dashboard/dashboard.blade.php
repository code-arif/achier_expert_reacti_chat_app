@extends('layouts.master_layout')

@section('title', 'Dashboard')

@section('content')
    @include('components.dashboard.stats')
    @include('components.dashboard.chart')
    @include('components.profile.profile')
@endsection
