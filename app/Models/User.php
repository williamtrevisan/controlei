<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[ObservedBy(UserObserver::class)]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'invite_code',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function inviteCode(): string
    {
        $nextId = User::query()->max('id') + 1;

        $hash = hash('sha256', implode('|', [
            $nextId,
            config('app.key'),
            now()->timestamp
        ]));

        $code = strtoupper(substr($hash, 0, 13));
        $formatted = substr($code, 0, 3) . '-' . substr($code, 3, 4) . '-' . substr($code, 7, 6);

        while (User::query()->where('invite_code', $formatted)->exists()) {
            $hash = hash('sha256', implode('|', [
                $nextId,
                config('app.key'),
                now()->timestamp,
                rand(1000, 9999)
            ]));

            $code = strtoupper(substr($hash, 0, 13));
            $formatted = substr($code, 0, 3) . '-' . substr($code, 3, 4) . '-' . substr($code, 7, 6);
        }

        return $formatted;
    }
}
