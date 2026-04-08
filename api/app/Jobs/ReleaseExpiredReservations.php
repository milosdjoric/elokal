<?php

namespace App\Jobs;

use App\Models\StockReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReleaseExpiredReservations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $released = StockReservation::releaseExpired();

        if ($released > 0) {
            \Illuminate\Support\Facades\Log::info("ReleaseExpiredReservations: {$released} isteklih rezervacija oslobođeno.");
        }
    }
}
