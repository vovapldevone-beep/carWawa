<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'tow_id',
        'car_name',
        'car_number',
        'car_weight',
        'car_location',
        'delivery_address',
        'status',
        'loading_date',
        'distance',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'car_weight' => 'decimal:2',
            'loading_date' => 'datetime',
        ];
    }
}
