<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/docs.css') }}">
    @livewireStyles
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{ config('app.version') }}">
</head>

<body>

<nav class="bd-subnavbar py-2" aria-label="Secondary navigation">
    <div class="container-fluid d-flex align-items-md-center">
        <a href="{{ route('home') }}" style="text-decoration: none">{{ config('app.name') }}</a>
    </div>
</nav>

<div class="container-fluid my-md-3 bd-layout">
    @include('layouts.sidebar')
    <div>
        <div class="col-12">
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
        </div>
    </div>
</div>

<script src="{{ asset('js/popper.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
@livewireScripts
@stack('scripts')
</body>
</html>
