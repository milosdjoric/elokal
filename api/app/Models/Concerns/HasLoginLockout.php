<?php

namespace App\Models\Concerns;

use Carbon\Carbon;

trait HasLoginLockout
{
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOCKOUT_MINUTES = 15;

    public function isLocked(): bool
    {
        return $this->locked_until && Carbon::parse($this->locked_until)->isFuture();
    }

    public function lockoutRemainingMinutes(): int
    {
        if (! $this->isLocked()) {
            return 0;
        }

        return (int) now()->diffInMinutes(Carbon::parse($this->locked_until), false);
    }

    public function incrementFailedAttempts(): void
    {
        $this->increment('failed_login_attempts');

        if ($this->failed_login_attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $this->update([
                'locked_until' => now()->addMinutes(self::LOCKOUT_MINUTES),
                'failed_login_attempts' => 0,
            ]);
        }
    }

    public function resetFailedAttempts(): void
    {
        if ($this->failed_login_attempts > 0 || $this->locked_until) {
            $this->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ]);
        }
    }
}
