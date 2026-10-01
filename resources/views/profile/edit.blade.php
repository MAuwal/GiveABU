@extends('layouts.admin')

@section('content')
<div class="container mt-4 max-w-xl mx-auto">
    <h2 class="text-2xl font-bold mb-4">Edit Profile</h2>
    <div class="bg-white rounded-lg shadow p-6">
        @auth
            <livewire:edit-profile-modal :asCard="true" />
        @endauth
    </div>
</div>
@endsection 