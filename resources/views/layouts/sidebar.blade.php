<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="collapse d-md-none" id="navbarSupportedContent">
        @stack('nav-right')
    </div>
    <div class="dropdown" style="border-bottom: 1px solid #ddd">
        <button class="btn btn-bd-light dropdown-toggle d-block" id="bd-versions" style="width: 100%"
                data-bs-toggle="dropdown"
                aria-expanded="false">
            {!! request()->currentConnection?->name ? request()->currentConnection->getColorBox(18) . request()->currentConnection->name : 'Connect' !!}
        </button>
        <ul class="dropdown-menu" style="width: 100%" aria-labelledby="bd-versions">
            @foreach($connections as $connection)
                <li>
                    <a class="dropdown-item {{ $connection->id == optional(request()->currentConnection)->id ? 'current' : ''}}"
                       href="{{ route('connections.show', $connection) }}">
                        {!! $connection->getColorBox(18) !!} {{ $connection->name }}
                    </a>
                </li>
            @endforeach
            @if(!empty($connections))
                <li>
                    <hr class="dropdown-divider">
                </li>
            @endif
            <li><a class="dropdown-item" href="{{ route('connections.create') }}">Add new</a></li>
            <li><a class="dropdown-item" href="{{ route('home') }}">Manage connection</a></li>
        </ul>
    </div>
    <div class="position-sticky bd-links" style="height: calc(100% - 39px);overflow-x: auto">
        @stack('connection_databases')
    </div>
</nav>
