<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('page_title', config('app.name'))</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.css') }}?v={{ config('app.version') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/docs-sidebar.css') }}">
    @livewireStyles
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{ config('app.version') }}">
</head>

<body>

<header class="navbar navbar-expand-md navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="{{ route('home') }}">{{ config('app.name') }}</a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu, #navbarSupportedContent" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="navbar-collapse collapse justify-content-end text-end">
        @stack('nav-right')
    </div>
</header>

<div class="container-fluid">
    <div class="row">
        @include('layouts.sidebar')
    </div>
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @stack('content')
    </main>
</div>

<script src="{{ asset('js/popper.min.js') }}?v={{ config('app.version') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}?v={{ config('app.version') }}"></script>
@livewireScripts
@stack('scripts')
</body>
</html>
