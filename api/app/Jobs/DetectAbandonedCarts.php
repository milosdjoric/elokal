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

        // Markuj korpe starije od 1h kao expired ako su i dalje abandoned
        AbandonedCart::where('status', 'abandoned')
            ->where('updated_at', '<', now()->subDays(30))
            ->update(['status' => 'expired']);

        // Korpe starije od 1h a novije od 30 dana su target za email (buduća implementacija)
        // Za sad samo logujemo koliko ih ima
        $count = AbandonedCart::where('status', 'abandoned')
            ->where('updated_at', '<', now()->subHour())
            ->where('emails_sent', 0)
            ->count();

        if ($count > 0) {
            \Illuminate\Support\Facades\Log::info("DetectAbandonedCarts: {$count} napuštenih korpi čeka email.");
        }
    }
}
