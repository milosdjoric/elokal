<?php

namespace App\Jobs;

use App\Models\StockNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBackInStockNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $productId,
    ) {}

    public function handle(): void
    {
        $notifications = StockNotification::where('product_id', $this->productId)
            ->pending()
            ->get();

        foreach ($notifications as $notification) {
            // TODO: Slanje emaila — Mail::to($notification->email)->send(new BackInStockMail($notification->product));

            $notification->update([
                'notified' => true,
                'notified_at' => now(),
            ]);
        }
    }
}
