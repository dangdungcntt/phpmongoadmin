<?php

namespace App\Models;

use App\EncryptableDbAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use MongoDB\Client;
use MongoDB\Model\CollectionInfo;
use MongoDB\Model\DatabaseInfo;
use Nddcoder\SqlToMongodbQuery\Model\FindQuery;
use Nddcoder\SqlToMongodbQuery\Model\Query;

/**
 * @property int id
 * @property string name
 * @property string uri
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
        return Cache::remember(
            'connection_databases_'.$this->id,
            300,
            function () {
                $mongoClient = $this->getMongoClient();
                return collect($mongoClient->listDatabases())
                    ->map(
                        function (DatabaseInfo $databaseInfo) use ($mongoClient) {
                            return [
                                'name'        => $databaseInfo->getName(),
                                'sizeOnDisk'  => $databaseInfo->getSizeOnDisk(),
                                'empty'       => $databaseInfo->isEmpty(),
                                'collections' => collect(
                                    $mongoClient->selectDatabase(
                                        $databaseInfo->getName()
                                    )->listCollections()
                                )
                                    ->map(
                                        function (CollectionInfo $collectionInfo) {
                                            return [
                                                'name' => $collectionInfo->getName(),
                                            ];
                                        }
                                    )
                                    ->sortBy('name')
                            ];
                        }
                    )
                    ->sortBy('name');
            }
        );
    }

    public function execute(
        Query $query,
        string $database,
        bool $shouldCache = false,
        int $defaultLimit = 50,
        int $hardLimit = 1000
    ) {
        $cacheKey = md5("$this->id|$database|$query->collection|".json_encode($query));

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
                    'limit' => $query->limit ?: $defaultLimit
                ]
            )
        ) : $mongoCollection->aggregate(
            $query->pipelines,
            $query->getOptions()
        );

        $data = [];
        foreach ($cursor as $index => $item) {
            if ($index >= $hardLimit) {
                break;
            }

            $data[] = $item;
        }

        if ($shouldCache) {
            Cache::put($cacheKey, $data, 60);
        }

        return $data;
    }
}
