<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        header('Access-Control-Allow-Origin:'.$request->header('Origin'));
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers:Content-Type, Accept, Authorization, X-Requested-With,token,language');
        header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE, PATCH');
        return $next($request);
    }
}
