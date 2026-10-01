@extends('layouts.admin')

@section('title', 'Edit Email Template')

@section('content')
    @livewire('admin.notifications.template-form', ['templateId' => $id])
@endsection
