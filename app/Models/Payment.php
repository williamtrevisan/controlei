<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $subscription_id
 * @property int $user_id
 * @property int $amount
 * @property PaymentStatus $status
 * @property PaymentMethod $payment_method
 * @property string $payable_type
 * @property string $payable_id
 * @property ?Carbon $paid_at
 * @property ?Carbon $expires_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Subscription $subscription
 * @property-read User $user
 * @property-read Model $payable
 */
class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'subscription_id',
        'user_id',
        'amount',
        'status',
        'payment_method',
        'payable_type',
        'payable_id',
        'paid_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}

