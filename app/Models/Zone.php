<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = [
        'name',
        'number',
        'site',
        'address',
        'lat',
        'lng',
        'radius',
        'load_capacity',
        'type_tow',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'load_capacity' => 'decimal:2',
        ];
    }
}
