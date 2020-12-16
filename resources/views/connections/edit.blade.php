@extends('layouts.app')

@include('partials.connection-databases')

@push('content')
    <h3>Edit Connection: {{ $connection->name }}</h3>
    <form action="{{ route('connections.update', $connection) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="id" class="form-label">ID</label>
            <input type="text" readonly class="form-control" id="id" value="{{ $connection->id }}">
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $connection->name }}">
        </div>
        <div class="mb-3">
            <label for="uri" class="form-label">URI</label>
            <textarea class="form-control" id="uri" name="uri">{{ $connection->uri }}</textarea>
        </div>
        <div class="mb-3">
            <label for="color" class="form-label">Color</label>
            <input type="color" class="form-control form-control-color" id="color" name="color" value="{{ $connection->color }}">
        </div>
        <div class="mb-3">
            <a href="{{ route('home') }}" class="btn btn-light">Back to list</a>
            <button class="btn btn-primary">Update</button>
        </div>
    </form>
@endpush

@push('styles')

@endpush

@push('scripts')

@endpush
