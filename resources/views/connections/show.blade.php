@extends('layouts.app')

@include('partials.connection-databases')

@push('content')

@endpush

@push('styles')
    <link rel="icon" href="{{ route('connections.favicon', $currentConnection) }}" type="image/png" sizes="20x20">
@endpush

@push('scripts')

@endpush
