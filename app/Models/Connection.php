<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use MongoDB\Client;
use MongoDB\Model\CollectionInfo;
use MongoDB\Model\DatabaseInfo;

/**
 * @property int id
 * @property string name
 * @property string uri
 */
class Connection extends Model
{
    protected $table = 'connections';
    protected $guarded = [];

    public function getMongoClient(): Client
    {
        return new Client($this->uri);
    }

    public function getHostPortAttribute(): array
    {
        return parse_url($this->uri);
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
                                    )->toArray()
                            ];
                        }
                    );
            }
        );
    }
}
