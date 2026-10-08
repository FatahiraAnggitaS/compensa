<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockEmployeeWritesInPublicDemo
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(
            config('demo.public'),
            Response::HTTP_FORBIDDEN,
            'Perubahan data employee dinonaktifkan pada public demo.',
        );

        return $next($request);
    }
}
