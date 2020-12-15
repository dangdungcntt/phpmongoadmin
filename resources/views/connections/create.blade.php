@extends('layouts.app')

@include('partials.connection-databases')

@push('content')
    <h3>Create new Connection</h3>
    <form action="{{ route('connections.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name">
        </div>
        <div class="mb-3">
            <label for="uri" class="form-label">URI</label>
            <textarea class="form-control" id="uri" name="uri"></textarea>
        </div>
        <div class="mb-3">
            <a href="{{ route('home') }}" class="btn btn-light">Back to list</a>
            <button class="btn btn-primary">Create</button>
        </div>
    </form>
@endpush

@push('styles')

@endpush

@push('scripts')

@endpush
