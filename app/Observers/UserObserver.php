<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function creating(User $user): void
    {
        if (!$user->invite_code) {
            $user->invite_code = $this->generateInviteCode();
        }
    }

    private function generateInviteCode(): string
    {
        // Get the next user ID (since the user hasn't been saved yet)
        $nextId = User::max('id') + 1;

        // Generate hash using user ID and app key
        $hash = hash('sha256', implode('|', [
            $nextId,
            config('app.key'),
            now()->timestamp
        ]));

        // Take first 13 characters and format as XXX-XXXX-XXXXXX
        $code = strtoupper(substr($hash, 0, 13));
        $formatted = substr($code, 0, 3) . '-' . substr($code, 3, 4) . '-' . substr($code, 7, 6);

        // Ensure uniqueness (very unlikely to collide with hash, but just in case)
        while (User::where('invite_code', $formatted)->exists()) {
            $hash = hash('sha256', implode('|', [
                $nextId,
                config('app.key'),
                now()->timestamp,
                rand(1000, 9999) // Add randomness if collision occurs
            ]));
            $code = strtoupper(substr($hash, 0, 13));
            $formatted = substr($code, 0, 3) . '-' . substr($code, 3, 4) . '-' . substr($code, 7, 6);
        }

        return $formatted;
    }
}
