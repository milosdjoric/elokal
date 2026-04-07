<?php

namespace App\Jobs;

use App\Models\Webhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DispatchWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900]; // 1min, 5min, 15min

    public function __construct(
        public string $event,
        public array $payload,
    ) {}

    public function handle(): void
    {
        $webhooks = Webhook::where('is_active', true)->get()
            ->filter(fn ($wh) => $wh->shouldReceive($this->event));

        foreach ($webhooks as $webhook) {
            $start = microtime(true);
            $signature = hash_hmac('sha256', json_encode($this->payload), $webhook->secret);

            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'X-Webhook-Event' => $this->event,
                        'X-Webhook-Signature' => $signature,
                    ])
                    ->post($webhook->url, $this->payload);

                $duration = (int) ((microtime(true) - $start) * 1000);

                $webhook->logs()->create([
                    'event' => $this->event,
                    'status_code' => $response->status(),
                    'response_body' => substr($response->body(), 0, 1000),
                    'duration_ms' => $duration,
                    'success' => $response->successful(),
                ]);

                if ($response->successful()) {
                    $webhook->update(['failures' => 0, 'last_triggered_at' => now()]);
                } else {
                    $webhook->increment('failures');
                }
            } catch (\Exception $e) {
                $duration = (int) ((microtime(true) - $start) * 1000);
                $webhook->logs()->create([
                    'event' => $this->event,
                    'response_body' => $e->getMessage(),
                    'duration_ms' => $duration,
                    'success' => false,
                ]);
                $webhook->increment('failures');
            }

            // Auto-disable after 10 consecutive failures
            if ($webhook->failures >= 10) {
                $webhook->update(['is_active' => false]);
            }
        }
    }
}
