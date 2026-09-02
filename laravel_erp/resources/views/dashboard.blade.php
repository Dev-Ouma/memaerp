@extends('layouts.app')
@section('title', auth()->user()->roleLabel().' Dashboard')
@section('section', auth()->user()->roleLabel().' workspace')
@section('content')
    @include('dashboards.'.$dashboardType)
@endsection
