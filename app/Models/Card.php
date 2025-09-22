<?php

namespace App\Models;

use App\Casts\AsMoney;
use App\Enums\AccountBank;
use App\Enums\CardBrand;
use App\Enums\CardType;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $account_id
 * @property string $last_four_digits
 * @property CardType $type
 * @property CardBrand $brand
 * @property Money $limit
 * @property int $due_day
 * @property-read int $closingDay
 * @property string $matcher_regex
 */
class Card extends Model
{
    /** @use HasFactory<\Database\Factories\CardFactory> */
    use HasFactory;

    protected $fillable = [
        'account_id',
        'last_four_digits',
        'type',
        'brand',
        'limit',
        'due_day',
        'matcher_regex',
    ];

    protected function closingDay(): Attribute
    {
        return Attribute::make(
            get: fn () => now()
                ->addMonth()
                ->setDay($this->due_day)
                ->subDays(config()->integer("banklink.banks.{$this->account->bank->value}.closing_due_interval_days"))
                ->day,
        );
    }

    protected function casts()
    {
        return [
            'brand' => CardBrand::class,
            'type' => CardType::class,
            'limit' => AsMoney::class,
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
