<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index()
    {
        return response()->json(Zone::all());
    }

    public function show(Zone $zone)
    {
        return response()->json($zone);
    }

    public function store(Request $request)
    {
        $zone = Zone::create([
            'address' => $request->address,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'radius' => $request->radius,
        ]);

        return response()->json($zone);
    }
}
