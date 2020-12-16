@extends('layouts.app')

@push('content')
    <div>
        <h3>All Connections <a href="{{ route('connections.create') }}" class="btn btn-success btn-sm"><i class="bi bi-plus"></i>Add new connection</a></h3>
        <table class="table table-hover table-striped table-bordered">
            <thead>
            <tr>
                <th>Name</th>
                <th>DB Server</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse(\App\Models\Connection::all() as $connection)
                <tr>
                    <td>{!! $connection->getColorBox() !!}{{ $connection->name }}</td>
                    <td>{{ join(':', \Illuminate\Support\Arr::only($connection->host_port, ['host', 'port'])) }}</td>
                    <td>
                        <a href="{{ route('connections.show', $connection) }}" class="btn btn-success btn-sm">
                            <i class="bi bi-controller"></i> Connect
                        </a>
                        <a href="{{ route('connections.clone', $connection) }}" class="btn btn-secondary btn-sm">
                            Clone
                        </a>
                        <a href="{{ route('connections.edit', $connection) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <button class="btn btn-danger btn-sm" onclick="confirm('Are you sure?') && document.getElementById('destroy-{{ $connection->id }}').submit()">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                        <form method="POST" action="{{ route('connections.destory', $connection) }}" id="destroy-{{ $connection->id }}">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="100" class="text-center">Empty</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endpush

@push('styles')

@endpush

@push('scripts')

@endpush
