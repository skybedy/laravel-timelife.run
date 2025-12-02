<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull as Middleware;

class ConvertEmptyStringsToNull extends Middleware
{
    /**
     * Skip conversion for webhook routes
     */
    public function handle($request, $next)
    {
        if ($request->is('webhook/*') || $request->is('webhook')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
