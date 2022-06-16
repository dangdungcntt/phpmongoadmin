<?php

namespace App\Http\Livewire;

use App\Encoders\UTCDateTimeEncoder;
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
    const DEFAULT_LIMIT = 50;

    public array|string $sql = '';
    public ?string $error = null;
    public ?string $viewColumn = null;
    public string $viewType = 'table';
    public int $connectionId;
    public string $database;
    public string $collectionName;
    public bool $executed = false;
    public bool $shouldCount = false;
    public int $page = 0;
    public bool $lastPage = false;

    protected $listeners = ['execute'];

    protected $queryString = ['sql', 'viewColumn', 'viewType', 'page'];

    public function changeViewType(string $newViewType)
    {
        $this->viewType = $newViewType;
    }

    public function nextPage()
    {
        if (!$this->lastPage) {
            $this->page++;
            $this->executed = false;
        } else {
            $this->executed = true;
        }
        $this->shouldCount = false;
    }

    public function prevPage()
    {
        if ($this->page > 0) {
            $this->page--;
            $this->executed = false;
        } else {
            $this->executed = true;
        }
        $this->shouldCount = false;
    }

    public function execute(string $sql)
    {
        $this->executed    = false;
        $this->shouldCount = false;
        $this->sql         = $sql;
    }

    public function count()
    {
        $this->executed    = true;
        $this->shouldCount = true;
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
            $skip                 = 0;
            $limit                = self::DEFAULT_LIMIT;
            if ($query instanceof FindQuery) {
                $limit = $query->limit ?: self::DEFAULT_LIMIT;
                $skip  = $query->skip + ($this->page * $limit);
            }
            if ($query instanceof Aggregate) {
                $skip = $this->page * $limit;
            }
            $data           = $connection->execute(
                query: $query,
                database: $this->database,
                shouldCache: $this->executed,
                defaultLimit: self::DEFAULT_LIMIT,
                hardLimit: self::MAX_RESULT,
                skip: $skip
            );
            $this->executed = true;
            $this->lastPage = count($data) < $limit;

            $breadcrumbs = empty($this->viewColumn) ? [] : explode('.', $this->viewColumn);
            $columns     = [];
            $docId       = 'doc_'.Str::random('5').'_id';

            if ($this->viewType == 'table') {
                [$columns, $data] = $this->extractData(
                    $data,
                    $docId
                );
            }

            $countDocuments = null;

            if ($this->shouldCount) {
                if ($query instanceof FindQuery) {
                    $countDocuments = $connection->countQuery($query, $this->database);
                } elseif ($query instanceof Aggregate) {
                    $countDocuments = $connection->countAggregate($query, $this->database);
                }
            }

            return view(
                'livewire.sql-result',
                compact(
                    'columns',
                    'data',
                    'docId',
                    'breadcrumbs',
                    'query',
                    'countDocuments',
                    'skip',
                    'limit'
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

        if (str_starts_with($sql, '[') && ($pipelines = json_decode($sql))) {
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

        foreach ($data as $item) {
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
                    $value instanceof UTCDateTime => $value->toDateTime()->setTimezone(new \DateTimeZone(config('app.timezone')))->format(UTCDateTimeEncoder::STUDIO_3T_DATE_FORMAT),
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
