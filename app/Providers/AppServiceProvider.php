<?php

namespace App\Providers;

use App\Models\Connection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('layouts.sidebar', fn($view) => $view->with('connections', Connection::all()));
        View::composer('layouts.app', fn($view) => $view->with('currentConnection', new Connection()));
    }
}
