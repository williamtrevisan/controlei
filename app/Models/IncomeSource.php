<?php

namespace App\Models;

use App\Casts\AsMoney;
use App\Enums\IncomeFrequency;
use App\Enums\IncomeSourceType;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property IncomeSourceType $type
 * @property IncomeFrequency $frequency
 * @property string $matcher_regex
 * @property ?Money $average_amount
 * @property bool $active
 */
class IncomeSource extends Model
{
    /** @use HasFactory<\Database\Factories\IncomeSourceFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'frequency',
        'matcher_regex',
        'average_amount',
        'active',
    ];

    protected function casts()
    {
        return [
            'type' => IncomeSourceType::class,
            'frequency' => IncomeFrequency::class,
            'average_amount' => AsMoney::class,
        ];
    }
}
