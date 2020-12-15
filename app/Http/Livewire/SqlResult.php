<?php

namespace App\Http\Livewire;

use App\Models\Connection;
use Livewire\Component;
use MongoDB\Driver\Cursor;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;
use Nddcoder\SqlToMongodbQuery\Model\FindQuery;
use Nddcoder\SqlToMongodbQuery\SqlToMongodbQuery;

class SqlResult extends Component
{
    public string $sql = '';
    public ?string $error = null;
    public ?string $viewColumn = null;
    public int $connectionId;
    public string $database;

    protected $listeners = ['execute' => 'execute'];

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
            $connection = Connection::query()->findOrFail($this->connectionId);
            $query      = (new SqlToMongodbQuery())->parse($this->sql);
            $collection = $connection->getMongoClient()
                ->selectDatabase($this->database)
                ->selectCollection($query->collection);
            $data       = $query instanceof FindQuery ? $collection->find(
                $query->filter,
                array_merge(
                    $query->getOptions(),
                    [
                        'limit' => $query->limit ?: 50
                    ]
                )
            ) : $collection->aggregate(
                $query->pipelines,
                $query->getOptions()
            );
            [$columns, $results] = $this->extractData($data);

            $breadcrumbs = [];

            if (!empty($this->viewColumn)) {
                $breadcrumbs = explode('.', $this->viewColumn);
            }

            $collectionName = $query->collection;

            return view('livewire.sql-result', compact('columns', 'results', 'breadcrumbs', 'collectionName'));
        } catch (\Exception $exception) {
            $this->error = $exception::class.' '.$exception->getMessage();
        }

        return view('livewire.sql-result', compact('data'));
    }

    protected function extractData(Cursor $data)
    {
        $columns = [
            '_id' => 'default'
        ];
        $results = [];
        foreach ($data as $item) {
            $row = [
                '_id' => data_get($item, '_id')
            ];
            if (!empty($this->viewColumn)) {
                $item = data_get($item, $this->viewColumn) ?? [];
            }

            if (!$item instanceof \ArrayAccess) {
                $results[] = $row;
                continue;
            }

            foreach ($item as $field => $value) {
                $row[$field]     = match (true) {
                    $value instanceof BSONArray => '['.$value->count().' elements]',
                    $value instanceof BSONDocument => '{'.$value->count().' fields}',
                    default => $value
                };
                $type = match (true) {
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
