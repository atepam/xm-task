<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class LatestPriceCandle extends Model
{
    use HasFactory;

    public const TABLE_NAME = 'candles';

    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    protected $fillable = [
        'symbol',
        'open',
        'high',
        'low',
        'price',
        'volume',
        'latest_trading_date',
        'prev_close',
        'change',
        'change_percent',
        'time',
    ];

    protected function casts(): array
    {
        return [
            'open' => 'float',
            'high' => 'float',
            'low' => 'float',
            'price' => 'float',
            'volume' => 'integer',
            'latest_trading_date',
            'prev_close' => 'float',
            'change' => 'float',
            'change_percent' => 'float',
        ];
    }

    protected function changePercent(): Attribute
    {
        return Attribute::make(
            set: function (string $value) {
                return Str::before($value, '%');
            },
        );
    }

    public function symbol(): string
    {
        return $this->getAttribute('symbol');
    }

    public function price(): float
    {
        return $this->getAttribute('price');
    }

    public function time(): string
    {
        return $this->getAttribute('time');
    }

    public function calcChange(LatestPriceCandle $previous): float
    {
        return (($this->price() - $previous->price()) / $previous->price()) * 100;
    }
}
