<?php

namespace App\Models;

use App\Enums\AnalyticsEventType;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $user_id
 * @property string $analyticsable_type
 * @property string $analyticsable_id
 * @property AnalyticsEventType $event_type
 * @property ?int $amount
 * @property ?Collection $data
 * @property Carbon $event_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read User $user
 * @property-read Model $analyticsable
 */
class Analytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'analyticsable_type',
        'analyticsable_id',
        'event_type',
        'amount',
        'data',
        'event_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => AnalyticsEventType::class,
            'data' => AsCollection::class,
            'event_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function analyticsable(): MorphTo
    {
        return $this->morphTo();
    }
}
