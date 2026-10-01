@extends('layouts.charifit')

@section('title', 'Charifit')

@section('body')
    <livewire:home.header-section />
    <livewire:home.auth-modal />

    <livewire:home.slider-area />
    <livewire:home.reason-area />
    <livewire:home.project-donations :limit="4" />
    <livewire:home.about />
    <livewire:home.latest-activities />
    <!-- <livewire:home.popular-causes /> -->
    <!-- <livewire:home.counter-area /> -->
    <!-- <livewire:home.volunteers-area /> -->
    <!-- <livewire:home.news-area /> -->
    <livewire:home.make-donation-area />
    <livewire:home.footer-area />
@endsection
