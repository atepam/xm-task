<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureResponseOnException
{

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$response->isSuccessful()) {
            $response->setData([]);
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }
}
