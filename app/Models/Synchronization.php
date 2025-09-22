<?php

namespace App\Models;

use App\Filament\Imports\TransactionSynchronizer;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ?CarbonInterface $completed_at
 * @property-read Authenticatable $user
 */
class Synchronization extends Model
{
    use Prunable;

    protected $fillable = [
        'user_id',
        'completed_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
