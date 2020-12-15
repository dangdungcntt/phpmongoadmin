<div>
    @if(!empty($error))
        <div class="alert alert-danger">
            {{ $error }}
        </div>
    @endif

    @isset($results)
        <p class="mb-2"><strong>Results: showing {{ count($results) }} row(s)</strong></p>
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
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                <tr>
                    @foreach($columns as $field => $type)
                        <th>{{ $field }}
                            @if($type != 'default')
                                <button type="button" class="btn btn-sm"
                                        wire:click="viewColumn('{{ $viewColumn ? "$viewColumn.$field" : $field }}')">view
                                </button>
                            @endif
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($results as $row)
                    <tr>
                        @foreach($columns as $field => $_)
                            <td>{{ $row[$field] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endisset
</div>
