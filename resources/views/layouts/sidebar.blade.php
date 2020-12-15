<aside class="bd-sidebar bd-subnavbar">
    <div class="dropdown">
        <button class="btn btn-bd-light dropdown-toggle d-block" id="bd-versions" style="width: 100%"  data-bs-toggle="dropdown"
                aria-expanded="false">
            {{ optional(request()->currentConnection)->name ?? 'Connect' }}
        </button>
        <ul class="dropdown-menu" style="width: 100%" aria-labelledby="bd-versions">
            @foreach($connections as $connection)
                <li>
                    <a class="dropdown-item {{ $connection->id == optional(request()->currentConnection)->id ? 'current' : ''}}"
                       href="{{ route('connections.show', $connection) }}">
                        {{ $connection->name }}
                    </a>
                </li>
            @endforeach
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="{{ route('connections.create') }}">Add new</a></li>
            <li><a class="dropdown-item" href="{{ route('home') }}">Manage connection</a></li>
        </ul>
    </div>
    <nav class="collapse bd-links" id="bd-docs-nav" aria-label="Docs navigation">
        @stack('connection_databases')
    </nav>

</aside>
