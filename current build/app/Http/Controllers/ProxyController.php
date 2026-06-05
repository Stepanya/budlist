<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProxyController extends Controller
{
    public function fetchDataRetail($endpoint, Request $request)
    {
        $apiUrl = "https://ratecalcapi.lbcapps.com/api/PH/$endpoint";
        $apiKey = env('LBC_RATES_RETAIL_API_KEY');

        $response = Http::withHeaders([
            'token' => $apiKey,
        ])->send($request->method(), $apiUrl, [
            'query' => $request->query(),       // Query parameters
            'json' => $request->all(),          // JSON payload for POST/PUT requests
        ]);
        $jsonResponse = json_decode($response->body(), true);
        return response()->json($jsonResponse, $response->status());
    }

    public function fetchDataNam($endpoint, Request $request)
    {
        $apiUrl = "https://star.lbcusa.net/FreightCalculator/api/Freight/$endpoint";
        $apiKey = env('LBC_RATES_NAM_API_KEY');

        $response = Http::withHeaders([
            'lbcnamkey' => $apiKey,
        ])->send($request->method(), $apiUrl, [
            'query' => $request->query(),       // Query parameters
            'json' => $request->all(),          // JSON payload for POST/PUT requests
        ]);
        $jsonResponse = json_decode($response->body(), true);
        return response()->json($jsonResponse, $response->status());
    }

    public function fetchDataInt($endpoint, Request $request)
    {
        $apiUrl = "https://ratecalcapi.lbcapps.com/api/INT/$endpoint";
        $apiKey = env('LBC_RATES_INTERNATIONAL_API_KEY');

        $response = Http::withHeaders([
            'token' => $apiKey,
        ])->send($request->method(), $apiUrl, [
            'query' => $request->query(),       // Query parameters
            'json' => $request->all(),          // JSON payload for POST/PUT requests
        ]);
        $jsonResponse = json_decode($response->body(), true);
        return response()->json($jsonResponse, $response->status());
    }
}
