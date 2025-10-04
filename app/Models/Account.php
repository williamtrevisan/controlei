<?php

namespace App\Models;

use App\Enums\AccountBank;
use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property AccountBank $bank
 * @property ?string $agency
 * @property ?string $account
 * @property ?string $account_digit
 * @property AccountType $type
 * @property-read string $account_number
 */
class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'bank',
        'agency',
        'account',
        'account_digit',
    ];

    protected function accountNumber(): Attribute
    {
        return Attribute::make(
            get: fn () => "$this->account-$this->account_digit",
        );
    }

    protected function casts()
    {
        return [
            'type' => AccountType::class,
            'bank' => AccountBank::class,
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
