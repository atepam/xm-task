<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceReportItem extends Model
{
    protected $table = null;

    public $timestamps = false;

    protected $fillable = [
        'symbol',
        'price',
        'change_percent',
        'time',
    ];

    protected function casts(): array
    {
        return [
            'open' => 'float',
            'price' => 'float',
            'change_percent' => 'float',
        ];
    }
}
