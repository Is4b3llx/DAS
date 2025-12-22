<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeoController extends Controller
{
    public function reverse(Request $request)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        if (!is_numeric($lat) || !is_numeric($lng)) {
            return response()->json(['error' => 'Invalid coordinates'], 422);
        }

        $resp = Http::withHeaders([
                'User-Agent' => 'DAS-AlasChiquitanas/1.0 (contact: admin@dasalas.shop)',
                'Accept-Language' => 'es',
            ])
            ->timeout(8)
            ->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lng,
                'zoom' => 18,
                'addressdetails' => 1,
            ]);

        if (!$resp->successful()) {
            return response()->json([
                'error' => 'Reverse geocoding failed',
                'status' => $resp->status(),
            ], 502);
        }

        return response()->json($resp->json());
    }
}
