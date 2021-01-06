@extends('layouts.app')

@include('partials.connection-databases')

@push('content')
    <h3>Create new Connection</h3>
    <form action="{{ route('connections.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Name <span class="required">*</span></label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') ?? ($connection->name ? 'Copy of ' . $connection->name : '') }}">
        </div>
        <div class="mb-3">
            <label for="uri" class="form-label">URI <span class="required">*</span></label>
            <textarea class="form-control" id="uri" name="uri">{{ old('uri') ?? $connection->uri }}</textarea>
        </div>
        <div class="mb-3">
            <label for="color" class="form-label">Color <span class="required">*</span></label>
            <input type="color" class="form-control form-control-color" id="color" name="color" value="{{ old('color') ?? $connection->color ?? '#1AB700' }}">
        </div>
        <div class="mb-3">
            <a href="{{ route('home') }}" class="btn btn-light">Back to list</a>
            <button class="btn btn-primary">Create</button>
        </div>
    </form>
@endpush

@push('styles')
    <link rel="icon" href="{{ asset('favicon.png')}}?v={{ microtime(true) }}" type="image/png" sizes="20x20">
@endpush

@push('scripts')

@endpush
