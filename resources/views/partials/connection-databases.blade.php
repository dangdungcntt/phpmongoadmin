@push('connection_databases')
    <ul class="list-unstyled mb-0 py-3 pt-md-1">
        @if(isset($currentConnection))
            @foreach($currentConnection->databases as $database)
                @php
                    $isCurrentDatabase = isset($currentDatabase) ? $currentDatabase == $database['name'] : false;
                @endphp
                <li class="mb-1 {{ $isCurrentDatabase ? 'active' : '' }}">
                    <button class="btn d-inline-flex align-items-center rounded" data-bs-toggle="collapse"
                            data-bs-target="#db-{{ $database['name'] }}" aria-expanded="{{ $isCurrentDatabase ? 'true' : 'false' }}" aria-current="{{ $isCurrentDatabase ? 'true' : 'false' }}">
                        {{ $database['name'] }}
                    </button>
                    <div class="collapse {{ $isCurrentDatabase ? 'show' : '' }}" id="db-{{ $database['name'] }}">
                        <ul class="list-unstyled fw-normal pb-1">
                            @foreach($database['collections'] as $collection)
                                @php
                                    $isCurrentCollection = isset($currentCollection) ? $currentCollection == $collection['name'] : false;
                                @endphp
                                <li><a href="{{ route('sql-editor.collection', [$currentConnection, $database['name'], $collection['name']]) }}"
                                       class="d-inline-flex align-items-center rounded {{ $isCurrentCollection ? 'active' : '' }}" @if($isCurrentCollection) aria-current="page" @endif>{{ $collection['name'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </li>
            @endforeach
        @endif
    </ul>
@endpush
