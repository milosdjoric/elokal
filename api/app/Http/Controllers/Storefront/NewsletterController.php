<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'source' => 'sometimes|string|in:footer,popup,checkout,account',
        ]);

        $existing = NewsletterSubscriber::where('email', $request->email)->first();

        if ($existing) {
            if ($existing->status === 'confirmed') {
                return response()->json(['message' => 'Već ste prijavljeni na newsletter.']);
            }
            if ($existing->status === 'unsubscribed') {
                $existing->update([
                    'status' => 'pending',
                    'token' => NewsletterSubscriber::generateToken(),
                    'unsubscribed_at' => null,
                ]);
                // TODO: Poslati confirmation email
                return response()->json(['message' => 'Ponovo ste prijavljeni. Proverite email za potvrdu.']);
            }
            // Pending — resend
            return response()->json(['message' => 'Proverite email za potvrdu prijave.']);
        }

        NewsletterSubscriber::create([
            'email' => $request->email,
            'token' => NewsletterSubscriber::generateToken(),
            'source' => $request->input('source', 'footer'),
        ]);

        // TODO: Poslati confirmation email

        return response()->json(['message' => 'Hvala! Proverite email za potvrdu prijave.'], 201);
    }

    public function confirm(string $token): JsonResponse
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        if (! $subscriber) {
            return response()->json(['message' => 'Nevažeći token.'], 404);
        }

        if ($subscriber->status === 'confirmed') {
            return response()->json(['message' => 'Već ste potvrdili prijavu.']);
        }

        $subscriber->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return response()->json(['message' => 'Uspešno ste se prijavili na newsletter!']);
    }

    public function unsubscribe(string $token): JsonResponse
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        if (! $subscriber) {
            return response()->json(['message' => 'Nevažeći token.'], 404);
        }

        $subscriber->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        return response()->json(['message' => 'Uspešno ste se odjavili sa newsletter-a.']);
    }
}
