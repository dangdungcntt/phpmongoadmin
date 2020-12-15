<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ShareCurrentConnection
{
    public function handle(Request $request, Closure $next)
    {
        $request->currentConnection = $request->route('connection');
        View::share('currentConnection', $request->currentConnection);
        return $next($request);
    }
}
