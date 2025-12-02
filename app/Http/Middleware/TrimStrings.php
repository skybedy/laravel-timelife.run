<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Skip trimming for webhook routes
     */
    public function handle($request, $next)
    {
        if ($request->is('webhook/*') || $request->is('webhook')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
