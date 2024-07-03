<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PriceReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GetPriceReport extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        Request            $request,
        PriceReportService $priceReport,
    )
    {
        return response()->json(
            $priceReport->getPriceReport()->values(),
            Response::HTTP_OK
        );
    }
}
