<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use Illuminate\Http\Request;

class ShowSqlEditorController
{
    public function __invoke(Request $request, Connection $connection, string $database, ?string $collection = null)
    {
        $sql = $request->get('sql');
        $sql ??= $collection ? "SELECT * FROM {$collection}" : '';

        return view('sql-editor', [
            'sql' => $sql,
            'currentDatabase' => $database,
            'currentCollection' => $collection
        ]);
    }
}
