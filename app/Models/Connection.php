<?php

namespace App\Models;

use App\EncryptableDbAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use MongoDB\Client;
use MongoDB\Model\CollectionInfo;
use MongoDB\Model\DatabaseInfo;
use MongoDB\Model\IndexInfo;
use Nddcoder\SqlToMongodbQuery\Model\Aggregate;
use Nddcoder\SqlToMongodbQuery\Model\FindQuery;
use Nddcoder\SqlToMongodbQuery\Model\Query;

/**
 * @property int id
 * @property string $name
 * @property string $uri
 * @property string $color
 * @property int $order
 */
class Connection extends Model
{
    use EncryptableDbAttribute;

    protected $table = 'connections';
    protected $guarded = [];

    protected array $encryptable = [
        'uri',
    ];

    public function getMongoClient(): Client
    {
        return new Client($this->uri);
    }

    public function getHostPortAttribute(): array
    {
        return parse_url($this->uri);
    }

    public function getColorBox(int $size = 25)
    {
        return "<span class='d-inline-block me-2' style='border-radius: 50%;vertical-align: middle;width: {$size}px;height: {$size}px;background-color: {$this->color}'></span>";
    }

    public function getDatabasesAttribute()
    {
        $mongoClient = $this->getMongoClient();
        return Cache::remember(
            'connection_databases_'.$this->id,
            600,
            fn() => collect($mongoClient->listDatabases())
                ->map(fn(DatabaseInfo $databaseInfo) => [
                    'name'        => $databaseInfo->getName(),
                    'sizeOnDisk'  => $databaseInfo->getSizeOnDisk(),
                    'empty'       => $databaseInfo->isEmpty(),
                    'collections' => collect($mongoClient->selectDatabase($databaseInfo->getName())->listCollections())
                        ->map(fn(CollectionInfo $collectionInfo) => [
                            'name'    => $collectionInfo->getName(),
                            'indexes' => collect($mongoClient->selectCollection($databaseInfo->getName(),
                                $collectionInfo->getName())->listIndexes())
                                ->map(fn(IndexInfo $indexInfo) => [
                                    'name'   => $indexInfo->getName(),
                                    'keys'   => $indexInfo->getKey(),
                                    'unique' => $indexInfo->isUnique(),
                                    'text'   => $indexInfo->isText(),
                                    'sparse' => $indexInfo->isSparse(),
                                ])
                        ])
                        ->sortBy('name')
                ])
                ->sortBy('name')
        );
    }

    public function execute(
        Query $query,
        string $database,
        bool $shouldCache = false,
        int $defaultLimit = 50,
        int $hardLimit = 1000,
        int $skip = 0
    ) {
        $cacheKey = md5("$this->id|$database|$query->collection|$skip|".json_encode($query));

        if ($shouldCache && $cachedData = Cache::get($cacheKey)) {
            return $cachedData;
        }

        $mongoCollection = $this->getMongoClient()
            ->selectDatabase($database)
            ->selectCollection($query->collection);

        $cursor = $query instanceof FindQuery ? $mongoCollection->find(
            $query->filter,
            array_merge(
                $query->getOptions(),
                [
                    'skip'  => $skip,
                    'limit' => $query->limit ?: $defaultLimit
                ]
            )
        ) : $mongoCollection->aggregate(
            array_merge($query->pipelines, [['$skip' => $skip], ['$limit' => $defaultLimit]]),
            $query->getOptions()
        );

        logger()->info('Executed query: '.json_encode($query));

        $data = [];
        foreach ($cursor as $index => $item) {
            if ($index >= $hardLimit) {
                break;
            }

            $data[] = $item;
        }

        if ($shouldCache) {
            Cache::put($cacheKey, $data, config('query.cache.ttl'));
        }

        return $data;
    }

    public function countQuery(FindQuery $findQuery, string $database): int
    {
        return $this->getMongoClient()
            ->selectDatabase($database)
            ->selectCollection($findQuery->collection)
            ->countDocuments($findQuery->filter);
    }

    public function countAggregate(Aggregate $aggregate, string $database): int
    {
        $pipelines   = $aggregate->pipelines;
        $pipelines[] = [
            '$group' => [
                '_id'   => null,
                'count' => [
                    '$sum' => 1
                ]
            ]
        ];

        $cursor = $this->getMongoClient()
            ->selectDatabase($database)
            ->selectCollection($aggregate->collection)
            ->aggregate($pipelines, $aggregate->getOptions());

        foreach ($cursor as $item) {
            return $item['count'] ?? 0;
        }

        return 0;
    }
}
