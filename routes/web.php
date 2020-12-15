<?php

use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\ShowSqlEditorController;
use App\Http\Middleware\ShareCurrentConnection;
use Illuminate\Support\Facades\Route;

Route::prefix('connections')
    ->group(
        function () {
            Route::get('create', [ConnectionController::class, 'create'])->name('connections.create');
            Route::post('/', [ConnectionController::class, 'store'])->name('connections.store');
            Route::get('{connection}/edit', [ConnectionController::class, 'edit'])->name('connections.edit');
            Route::put('{connection}', [ConnectionController::class, 'update'])->name('connections.update');

            Route::middleware([ShareCurrentConnection::class])->group(
                function () {
                    Route::get('{connection}', [ConnectionController::class, 'show'])->name('connections.show');
                    Route::get('{connection}/databases/{database}', ShowSqlEditorController::class)->name('sql-editor');
                    Route::get('{connection}/databases/{database}/collections/{collection}', ShowSqlEditorController::class)->name(
                        'sql-editor.collection'
                    );
                }
            );
        }
    );

Route::view('/', 'home')->name('home');
