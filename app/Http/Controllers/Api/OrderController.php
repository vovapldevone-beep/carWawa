<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_name' => 'required|string|max:255',
            'car_number' => 'required|string|max:255',
            'car_weight' => 'nullable|numeric|min:0',
            'car_location' => 'required|string|max:255',
            'loading_date' => 'nullable|date',
            'client_id' => 'nullable|integer',
            'tow_id' => 'nullable|integer',
        ]);

        $order = Order::create([
            'client_id' => $validated['client_id'] ?? 0,
            'tow_id' => $validated['tow_id'] ?? 0,
            'car_name' => $validated['car_name'],
            'car_number' => $validated['car_number'],
            'car_weight' => $validated['car_weight'] ?? null,
            'car_location' => $validated['car_location'],
            'loading_date' => $validated['loading_date'] ?? null,
        ]);

        return response()->json($order, 201);
    }
}
