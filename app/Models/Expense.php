<?php

namespace App\Models;

use App\Casts\AsMoney;
use App\Enums\ExpenseFrequency;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $description
 * @property ExpenseFrequency $frequency
 * @property string $matcher_regex
 * @property Money $average_amount
 * @property bool $active
 */
class Expense extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseFactory> */
    use HasFactory;

    protected $fillable = [
        'description',
        'frequency',
        'matcher_regex',
        'average_amount',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'frequency' => ExpenseFrequency::class,
            'average_amount' => AsMoney::class,
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'expense_id');
    }

    public function getMonthlyProjection(): ?Money
    {
        if (!$this->active || $this->average_amount->isZero()) {
            return null;
        }

        return match ($this->frequency) {
            ExpenseFrequency::Monthly => $this->average_amount,
            ExpenseFrequency::Quarterly => $this->average_amount->dividedBy(3, roundingMode: \Brick\Math\RoundingMode::HALF_UP),
            ExpenseFrequency::Annually => $this->average_amount->dividedBy(12, roundingMode: \Brick\Math\RoundingMode::HALF_UP),
            ExpenseFrequency::Occasionally => null,
        };
    }
}
