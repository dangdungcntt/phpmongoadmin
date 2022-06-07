@push('connection_databases')
    <ul class="list-unstyled mb-0 px-2 py-3 pt-md-1">
        @if(isset($currentConnection))
            @foreach($currentConnection->databases as $database)
                @php
                    $isCurrentDatabase = isset($currentDatabase) ? $currentDatabase == $database['name'] : false;
                @endphp
                <li class="mb-1 {{ $isCurrentDatabase ? 'active' : '' }}">
                    <button class="btn d-inline-flex align-items-center rounded" data-bs-toggle="collapse" style="color: {{ $currentConnection->color }};font-weight: {{ $isCurrentDatabase ? '600' : '400' }}"
                            data-bs-target="#db-{{ $database['name'] }}" aria-expanded="{{ $isCurrentDatabase ? 'true' : 'false' }}" aria-current="{{ $isCurrentDatabase ? 'true' : 'false' }}">
                        {{ $database['name'] }}
                    </button>
                    <div class="collapse {{ $isCurrentDatabase ? 'show' : '' }}" id="db-{{ $database['name'] }}">
                        <ul class="list-unstyled fw-normal pb-1">
                            @foreach($database['collections'] as $collection)
                                @php
                                    $isCurrentCollection = isset($currentCollection) ? $currentCollection == $collection['name'] : false;
                                @endphp
                                <li>
                                    @if($isCurrentCollection)
                                        <button class="btn d-inline-flex align-items-center rounded active" data-bs-toggle="collapse" style="color: {{ $currentConnection->color }};font-weight: 600;font-size: 14px;margin-left: 25px;padding-left: 5px"
                                                data-bs-target="#collection-{{ $collection['name'] }}" aria-expanded="false" aria-current="false">
                                            {{ $collection['name'] }}
                                        </button>
                                        @if(!empty($indexes))
                                            <div class="collapse" id="collection-{{ $collection['name'] }}">
                                                <ul class="list-unstyled fw-normal pb-1">
                                                    @foreach($indexes as $index)
                                                        <li style="padding-left: 50px">
                                                            <small
                                                                style="color: {{ $currentConnection->color }}">{{ $index['name'] }}</small>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    @else
                                        <a href="{{ route('sql-editor.collection', [$currentConnection, $database['name'], $collection['name']]) }}" style="color: {{ $currentConnection->color }};font-weight: {{ $isCurrentCollection ? '600' : '400' }}"
                                           class="d-inline-flex align-items-center rounded">{{ $collection['name'] }}</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </li>
            @endforeach
        @endif
    </ul>
@endpush
