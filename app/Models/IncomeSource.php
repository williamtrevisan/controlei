<?php

namespace App\Models;

use App\Casts\AsMoney;
use App\Enums\IncomeFrequency;
use App\Enums\IncomeSourceType;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    use HasUuids;

    protected $fillable = [
        'user_id',
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
            'active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'income_source_id');
    }
}
