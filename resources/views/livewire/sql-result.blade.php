<div>
    @if(!empty($error))
        <div class="alert alert-danger">
            {{ $error }}
        </div>
    @endif

    @isset($query)
        @if($query instanceof \Nddcoder\SqlToMongodbQuery\Model\FindQuery)
            <div>
                Executed query on collection: <strong>{{ $query->collection }}</strong>
            </div>
            <textarea class="form-control">{{ json_encode((object)$query->filter) }}</textarea>
        @endif
        @if($query instanceof \Nddcoder\SqlToMongodbQuery\Model\Aggregate)
            <div>
                Executed aggregate on collection: <strong>{{ $query->collection }}</strong>
            </div>
            <textarea class="form-control">{{ json_encode($query->pipelines) }}</textarea>
        @endif
    @endisset

    <div class="row">
        <div class="col-6">
            <div class="input-group mb-2 mt-2">
                <button class="btn btn-light" wire:click="prevPage()" @if($page == 0) disabled style="color: #cccccc;cursor: not-allowed" @endif>&lt;</button>
                <button class="btn btn-light" wire:click="nextPage()" @if(count($data) < $limit) disabled style="color: #cccccc;cursor: not-allowed" @endif>&gt;</button>
                <button wire:click="count()" class="btn btn-sm btn-warning">
                    {{ is_null($countDocuments) ? 'Count' : number_format($countDocuments) }} {{ is_null($countDocuments) || $countDocuments >= 2 ? 'documents' : 'document' }}
                </button>
                <img style="margin-left: 5px" wire:loading.delay src="{{ asset('loading.gif') }}" height="31" alt="">
            </div>
        </div>
        <div class="col-6">
            <div class="justify-content-end input-group mb-2 mt-2">
                <button wire:click="changeViewType('table')" class="btn btn-sm {{ $viewType == 'table' ? 'btn-primary' : 'btn-light' }}">Table View</button>
                <button wire:click="changeViewType('json')" class="btn btn-sm {{ $viewType == 'json' ? 'btn-primary' : 'btn-light' }}">Json View</button>
            </div>
        </div>
    </div>

    @isset($data)
        <div>
            <p class="mb-2">
                <strong>
                    Documents:
                    @if(count($data) == 0)
                        0 to 0
                    @else
                        {{ $skip + 1 }} to {{ $skip + count($data) }}
                    @endif
                </strong>
            </p>
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
