<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LatestPricesService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GetLatestPrices extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        Request             $request,
        LatestPricesService $latestPrices,
    )
    {
        return response()->json(
            $latestPrices->getLatestPrices()->all(),
            Response::HTTP_OK
        );
    }
}
