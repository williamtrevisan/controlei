<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $transaction_id
 * @property int $owner_id
 * @property int $member_id
 * @property Carbon $shared_at
 * @property-read Transaction $transaction
 * @property-read User $owner
 * @property-read User $member
 */
class TransactionMember extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'transaction_id',
        'owner_id',
        'member_id',
        'shared_at',
    ];

    protected $casts = [
        'shared_at' => 'datetime',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}
