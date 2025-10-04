<?php

namespace App\Models;

use App\Enums\InvitationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invite extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'invites';

    protected $fillable = [
        'inviter_id',
        'invitee_id',
        'status',
        'message',
        'accepted_at'
    ];

    protected $casts = [
        'status' => InvitationStatus::class,
        'accepted_at' => 'datetime'
    ];

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invitee_id');
    }
}
