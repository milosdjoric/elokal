<?php

namespace App\Jobs;

use App\Models\AbandonedCart;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DetectAbandonedCarts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        if (! feature('feature_abandoned_cart')) {
            return;
        }

        // Korpe starije od 30 dana → expired
        AbandonedCart::where('status', 'abandoned')
            ->where('updated_at', '<', now()->subDays(30))
            ->update(['status' => 'expired']);

        // Step 1: 1h posle napuštanja, još nema emailova
        AbandonedCart::where('status', 'abandoned')
            ->where('emails_sent', 0)
            ->where('created_at', '<', now()->subHour())
            ->where('created_at', '>', now()->subDays(30))
            ->each(fn (AbandonedCart $cart) => SendAbandonedCartReminder::dispatch($cart, 1));

        // Step 2: 24h posle napuštanja, poslat 1 email
        AbandonedCart::where('status', 'abandoned')
            ->where('emails_sent', 1)
            ->where('created_at', '<', now()->subHours(24))
            ->where('created_at', '>', now()->subDays(30))
            ->each(fn (AbandonedCart $cart) => SendAbandonedCartReminder::dispatch($cart, 2));

        // Step 3: 72h posle napuštanja, poslata 2 emaila
        AbandonedCart::where('status', 'abandoned')
            ->where('emails_sent', 2)
            ->where('created_at', '<', now()->subHours(72))
            ->where('created_at', '>', now()->subDays(30))
            ->each(fn (AbandonedCart $cart) => SendAbandonedCartReminder::dispatch($cart, 3));
    }
}
