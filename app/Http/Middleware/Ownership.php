<?php

namespace App\Http\Middleware;

use Closure;
use Request;

class Ownership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // todo: check ownership!

        return $next($request);
    }
}
