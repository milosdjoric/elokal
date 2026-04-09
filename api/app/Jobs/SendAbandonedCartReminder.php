<?php

namespace App\Jobs;

use App\Models\AbandonedCart;
use App\Models\Coupon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendAbandonedCartReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public AbandonedCart $abandonedCart,
        public int $step, // 1, 2, ili 3
    ) {}

    public function handle(): void
    {
        $cart = $this->abandonedCart;

        // Preskači ako je korpa u međuvremenu oporavljena ili istekla
        if ($cart->status !== 'abandoned') {
            return;
        }

        $couponCode = null;

        // Step 2 (24h): auto-generisanje 10% kupona
        if ($this->step === 2) {
            $coupon = Coupon::create([
                'code' => 'AC-' . Str::upper(Str::random(8)),
                'type' => 'percentage',
                'value' => 10.00,
                'max_uses' => 1,
                'max_uses_per_user' => 1,
                'starts_at' => now(),
                'expires_at' => now()->addHours(48),
                'is_active' => true,
                'description' => "Napuštena korpa — auto kupon za {$cart->email}",
            ]);
            $couponCode = $coupon->code;
        }

        $recoveryUrl = config('app.frontend_url', 'http://localhost:3000') . '/korpa/obnovi/' . $cart->token;

        // TODO: Slanje emaila kad bude SMTP konfigurisan
        // Mail::to($cart->email)->send(new AbandonedCartMail($cart, $this->step, $recoveryUrl, $couponCode));

        Log::info("AbandonedCartReminder: step {$this->step} za {$cart->email}", [
            'cart_id' => $cart->id,
            'coupon' => $couponCode,
            'recovery_url' => $recoveryUrl,
        ]);

        $cart->update([
            'emails_sent' => $this->step,
            'last_email_at' => now(),
        ]);
    }
}
