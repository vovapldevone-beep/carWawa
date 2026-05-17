<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::query()
            ->orderByDesc('id')
            ->get([
                'id',
                'car_name',
                'car_number',
                'car_weight',
                'car_location',
                'delivery_address',
                'loading_date',
                'distance',
                'status',
            ]);

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_name' => 'required|string|max:255',
            'car_number' => 'required|string|max:255',
            'car_weight' => 'nullable|numeric|min:0',
            'car_location' => 'required|string|max:255',
            'delivery_address' => 'required|string|max:255',
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
            'delivery_address' => $validated['delivery_address'],
            'loading_date' => $validated['loading_date'] ?? null,
        ]);

        return response()->json($order, 201);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'car_name' => 'required|string|max:255',
            'car_number' => 'required|string|max:255',
            'car_weight' => 'nullable|numeric|min:0',
            'car_location' => 'required|string|max:255',
            'delivery_address' => 'required|string|max:255',
            'loading_date' => 'nullable|date',
            'distance' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|max:255',
        ]);

        $distance = null;
        if (array_key_exists('distance', $validated) && $validated['distance'] !== null) {
            $distance = (int) round((float) $validated['distance']);
        }

        $status = $validated['status'] ?? '';
        $status = is_string($status) && $status !== '' ? $status : 'pending';

        $order->update([
            'car_name' => $validated['car_name'],
            'car_number' => $validated['car_number'],
            'car_weight' => $validated['car_weight'] ?? null,
            'car_location' => $validated['car_location'],
            'delivery_address' => $validated['delivery_address'],
            'loading_date' => $validated['loading_date'] ?? null,
            'distance' => $distance,
            'status' => $status,
        ]);

        $order->refresh();

        return response()->json(
            $order->only([
                'id',
                'car_name',
                'car_number',
                'car_weight',
                'car_location',
                'delivery_address',
                'loading_date',
                'distance',
                'status',
            ]),
        );
    }
}
