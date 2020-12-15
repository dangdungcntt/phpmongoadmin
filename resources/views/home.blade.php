@extends('layouts.app')

@push('content')
    <div>
        <h3>All Connections <a href="{{ route('connections.create') }}" class="btn btn-success btn-sm"><i class="bi bi-plus"></i>Add new connection</a></h3>
        <table class="table table-hover table-striped table-bordered">
            <thead>
            <tr>
                <th></th>
                <th>Name</th>
                <th>DB Server</th>
            </tr>
            </thead>
            <tbody>
            @foreach(\App\Models\Connection::all() as $connection)
                <tr>
                    <td style="width: 1%">
                        <div style="width: 25px;height: 25px;background-color: {{ $connection->color }}"></div>
                    </td>
                    <td>{{ $connection->name }}</td>
                    <td>{{ join(':', \Illuminate\Support\Arr::only($connection->host_port, ['host', 'port'])) }}</td>
                    <td>
                        <a href="{{ route('connections.show', $connection) }}" class="btn btn-success btn-sm"><i class="bi bi-controller"></i>
                            Connect</a>
                        <a href="{{ route('connections.edit', $connection) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i>
                            Edit</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endpush

@push('styles')

@endpush

@push('scripts')

@endpush
