<?php

namespace App\Models;

use App\Enums\ResourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property ResourceType $resource_type
 * @property int $year
 * @property int $month
 * @property int $count
 * @property Carbon $created_at
 *
 * @property-read User $user
 */
class UsageTracking extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'resource_type',
        'year',
        'month',
        'count',
    ];

    protected function casts(): array
    {
        return [
            'resource_type' => ResourceType::class,
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

