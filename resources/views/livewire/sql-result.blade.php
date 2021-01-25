<div>
    @if(!empty($error))
        <div class="alert alert-danger">
            {{ $error }}
        </div>
    @endif

    @isset($query)
        <div>
            <strong>Executed query:</strong>
        </div>
        @if($query instanceof \Nddcoder\SqlToMongodbQuery\Model\FindQuery)
            <textarea class="form-control">{{ json_encode((object)$query->filter) }}</textarea>
        @endif
        @if($query instanceof \Nddcoder\SqlToMongodbQuery\Model\Aggregate)
            <textarea class="form-control">{{ json_encode($query->pipelines) }}</textarea>
        @endif
    @endisset

    <div class="input-group mb-2 mt-2">
        <button wire:click="changeViewType('table')" class="btn btn-sm {{ $viewType == 'table' ? 'btn-primary' : 'btn-light' }}">Table View</button>
        <button wire:click="changeViewType('json')" class="btn btn-sm {{ $viewType == 'json' ? 'btn-primary' : 'btn-light' }}">Json View</button>
    </div>

    <div wire:loading>
        Executing...
    </div>

    @isset($data)
        <div wire:loading.remove>
            <p class="mb-2"><strong>Results: showing {{ count($data) }} row(s)</strong></p>
            <p>
                <strong>{{ $database }}</strong> >
                @if(empty($breadcrumbs))
                    {{ $collectionName }}
                @else
                    <a href="#" wire:click.prevent="viewColumn('')">{{ $collectionName }}</a>
                @endif
                @php($breadcrumbPrefix = '')
                @foreach($breadcrumbs as $breadcrumb)
                    @php($breadcrumbPrefix .= $breadcrumb)
                    >
                    @if($loop->last)
                        <span>{{ $breadcrumb }}</span>
                    @else
                        <button class="btn btn-sm" wire:click.prevent="viewColumn('{{ $breadcrumbPrefix }}')">
                            {{ $breadcrumb }}
                        </button>
                    @endif
                    @php($breadcrumbPrefix .= '.')
                @endforeach
            </p>
            @if($viewType == 'json')
                <div>
                    @foreach($data as $row)
                        <pre><code class="sql">{{ \App\Helpers\BsonHelper::encode($row) }}</code></pre>
                        @if(!$loop->last)
                            <div style="color: green">-----------------------------------------<br><br></div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            @foreach($columns as $field => $type)
                                <th>
                                    {{ $field == $docId ? '{Document id}' : $field }}
                                    @if($type != 'default')
                                        <a href="#" type="button"
                                           class="btn btn-sm btn-link"
                                           wire:click.prevent="viewColumn('{{ $viewColumn ? "$viewColumn.$field" : $field }}')">view
                                        </a>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($data as $row)
                            <tr>
                                @foreach($columns as $field => $_)
                                    <td>{{ $row[$field] ?? '' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endisset
</div>
