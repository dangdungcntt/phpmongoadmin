<?php

namespace App\Http\Livewire;

use App\Helpers\BsonHelper;
use App\Models\Connection;
use ArrayAccess;
use Illuminate\Support\Str;
use Livewire\Component;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;
use Nddcoder\SqlToMongodbQuery\Model\Aggregate;
use Nddcoder\SqlToMongodbQuery\Model\FindQuery;
use Nddcoder\SqlToMongodbQuery\Model\Query;
use Nddcoder\SqlToMongodbQuery\SqlToMongodbQuery;
use Throwable;

class SqlResult extends Component
{
    const MAX_RESULT = 1000;

    public array|string $sql = '';
    public ?string $error = null;
    public ?string $viewColumn = null;
    public string $viewType = 'table';
    public int $connectionId;
    public string $database;
    public string $collectionName;
    public bool $executed = false;

    protected $listeners = ['execute'];

    protected $queryString = ['sql', 'viewColumn', 'viewType'];

    public function changeViewType(string $newViewType)
    {
        $this->viewType = $newViewType;
    }

    public function execute(string $sql)
    {
        $this->executed = false;
        $this->sql      = $sql;
    }

    public function viewColumn(string $column)
    {
        $this->viewColumn = $column;
    }

    public function render()
    {
        $this->error = '';
        try {
            /** @var Connection $connection */
            $connection           = Connection::query()->findOrFail($this->connectionId);
            $query                = $this->buildQuery();
            $this->collectionName = $query->collection;
            $data                 = $connection->execute(
                query: $query,
                database: $this->database,
                shouldCache: $this->executed,
                hardLimit: self::MAX_RESULT
            );
            $this->executed       = true;

            $breadcrumbs = empty($this->viewColumn) ? [] : explode('.', $this->viewColumn);
            $columns     = [];
            $docId       = 'doc_'.Str::random('5').'_id';

            if ($this->viewType == 'table') {
                [$columns, $data] = $this->extractData(
                    $data,
                    $docId
                );
            }

            return view(
                'livewire.sql-result',
                compact(
                    'columns',
                    'data',
                    'docId',
                    'breadcrumbs',
                    'query'
                )
            );
        } catch (Throwable $exception) {
            $this->error = $exception::class.' '.$exception->getMessage();
        }

        return view('livewire.sql-result');
    }

    protected function buildQuery(): Query
    {
        $sql = trim($this->sql);
        if (!$this->collectionName) {
            return (new SqlToMongodbQuery())->parse($sql);
        }

        if (str_starts_with($sql, '{') && !is_null($filter = BsonHelper::decode($sql))) {
            //Raw mongo query
            return new FindQuery(
                collection: $this->collectionName,
                filter: $filter
            );
        }

        if (str_starts_with($sql, '[') && ($pipelines = json_decode($sql, true))) {
            $pipelines = collect($pipelines)
                ->map(function ($pipeline) {
                    if (isset($pipeline['$match'])) {
                        $pipeline['$match'] = BsonHelper::decode($pipeline['$match']);
                        if (empty($pipeline['$match'])) {
                            $pipeline['$match'] = (object) [];
                        }
                    }
                    return $pipeline;
                })
                ->toArray();
            //Raw mongo aggregation
            return new Aggregate(
                collection: $this->collectionName,
                pipelines: $pipelines
            );
        }

        return (new SqlToMongodbQuery())->parse($sql);
    }

    protected function extractData(array $data, string $docId): array
    {
        $columns = [];

        if ($this->viewColumn && !str_starts_with($this->viewColumn, '_id')) {
            $columns[$docId] = 'default';
        }

        $results = [];

        foreach ($data as $index => $item) {
            $row = [];

            if (!empty($this->viewColumn)) {
                if (isset($item['_id'])) {
                    $row[$docId] = $item['_id'];
                }
                $item = data_get($item, $this->viewColumn) ?? [];
            }

            if (!$item instanceof ArrayAccess) {
                $results[] = $row;
                continue;
            }

            foreach ($item as $field => $value) {
                $row[$field] = match (true) {
                    $value instanceof BSONArray => '['.$value->count().' items]',
                    $value instanceof BSONDocument => '{'.$value->count().' fields}',
                    $value instanceof UTCDateTime => $value->toDateTime()->format(DATE_RFC3339_EXTENDED),
                    is_bool($value) => $value ? 'true' : 'false',
                    default => $value
                };
                $type        = match (true) {
                    $value instanceof BSONArray => 'array',
                    $value instanceof BSONDocument => 'object',
                    default => 'default'
                };

                if (!isset($columns[$field]) || $columns[$field] == 'default') {
                    $columns[$field] = $type;
                }
            }
            $results[] = $row;
        }

        return [$columns, $results];
    }
}
