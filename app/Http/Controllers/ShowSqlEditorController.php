<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use MongoDB\Driver\Exception\CommandException;
use MongoDB\Model\IndexInfo;

class ShowSqlEditorController
{
    public function __invoke(Request $request, Connection $connection, string $database, ?string $collection = null)
    {
        $sql     = $request->get('sql');
        $sql     ??= $collection ? "SELECT * FROM $collection" : '';
        $indexes = collect();
        if ($collection) {
            $indexes = Cache::remember(
                "connection_{$connection->id}_database_{$database}_collection_{$collection}_indexes",
                600,
                function () use ($connection, $database, $collection) {
                    $dbCollection = $connection->getMongoClient()->selectCollection($database, $collection);
                    try {
                        return collect($dbCollection->listIndexes())
                            ->map(fn(IndexInfo $indexInfo) => [
                                'name'   => $indexInfo->getName(),
                                'keys'   => $indexInfo->getKey(),
                                'unique' => $indexInfo->isUnique(),
                                'text'   => $indexInfo->isText(),
                                'sparse' => $indexInfo->isSparse(),
                            ]);
                    } catch (CommandException) {
                        return collect();
                    }
                }
            );
        }

        return view('sql-editor', [
            'sql'               => $sql,
            'currentDatabase'   => $database,
            'currentCollection' => $collection,
            'indexes'           => $indexes
        ]);
    }
}
