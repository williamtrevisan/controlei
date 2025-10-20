<?php

namespace App\Models;

use App\Enums\SubscriptionEventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property ?string $subscription_id
 * @property SubscriptionEventType $event_type
 * @property ?int $from_id
 * @property ?int $to_id
 * @property int $monthly_recurring_revenue_change
 * @property Carbon $created_at
 *
 * @property-read User $user
 * @property-read Subscription $subscription
 * @property-read Plan $from
 * @property-read Plan $to
 */
class SubscriptionEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'event_type',
        'from_id',
        'to_id',
        'monthly_recurring_revenue_change',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => SubscriptionEventType::class,
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function from(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'from_id');
    }

    public function to(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'to_id');
    }
}

