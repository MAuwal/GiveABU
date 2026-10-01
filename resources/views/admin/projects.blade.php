@extends('layouts.admin')

@section('title', 'Project Donations Overview')

@section('content')

    @livewire('admin.projects-manager')
    @livewire('admin.add-project')

@endsection