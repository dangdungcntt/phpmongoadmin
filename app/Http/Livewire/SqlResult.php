<?php

namespace App\Http\Livewire;

use App\Models\Connection;
use Illuminate\Support\Str;
use Livewire\Component;
use MongoDB\Driver\Cursor;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;
use Nddcoder\SqlToMongodbQuery\SqlToMongodbQuery;

class SqlResult extends Component
{
    const MAX_RESULT = 1000;

    public string $sql = '';
    public ?string $error = null;
    public ?string $viewColumn = null;
    public int $connectionId;
    public string $database;
    public string $collectionName;

    protected $listeners = ['execute'];

    protected $queryString = ['sql', 'viewColumn'];

    public function execute(string $sql)
    {
        $this->sql = $sql;
    }

    public function viewColumn(string $column)
    {
        $this->viewColumn = $column;
    }

    public function render()
    {
        try {
            /** @var Connection $connection */
            $connection           = Connection::query()->findOrFail($this->connectionId);
            $query                = (new SqlToMongodbQuery())->parse($this->sql);
            $this->collectionName = $query->collection;
            $data                 = $connection->execute($query, $this->database);

            [$columns, $results] = $this->extractData(
                $data,
                $docId = 'doc_'.Str::random('5').'_id',
                self::MAX_RESULT
            );

            $breadcrumbs = empty($this->viewColumn) ? [] : explode('.', $this->viewColumn);

            return view(
                'livewire.sql-result',
                compact(
                    'columns',
                    'results',
                    'docId',
                    'breadcrumbs',
                )
            );
        } catch (\Exception $exception) {
            $this->error = $exception::class.' '.$exception->getMessage();
        }

        return view('livewire.sql-result');
    }

    protected function extractData(Cursor $data, string $docId, int $maxResult)
    {
        $columns = [];

        if ($this->viewColumn && !str_starts_with($this->viewColumn, '_id')) {
            $columns[$docId] = 'default';
        }

        $results = [];

        foreach ($data as $index => $item) {
            if ($index >= $maxResult) {
                break;
            }

            $row = [];

            if (!empty($this->viewColumn)) {
                if (isset($item['_id'])) {
                    $row[$docId] = $item['_id'];
                }
                $item = data_get($item, $this->viewColumn) ?? [];
            }

            if (!$item instanceof \ArrayAccess) {
                $results[] = $row;
                continue;
            }

            foreach ($item as $field => $value) {
                $row[$field] = match (true) {
                    $value instanceof BSONArray => '['.$value->count().' items]',
                    $value instanceof BSONDocument => '{'.$value->count().' fields}',
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
