<?php

namespace App\Http\Middleware;

use App\Models\Advertorial;
use Closure;
use Illuminate\Http\Request;

class SyncOverdueStatus
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('get')) {
            Advertorial::syncOverdue();
        }

        return $next($request);
    }
}