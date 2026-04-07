<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        event(new Registered($user));

        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && $user->isLocked()) {
            $minutes = $user->lockoutRemainingMinutes();
            throw ValidationException::withMessages([
                'email' => ["Nalog je zaključan. Pokušajte ponovo za {$minutes} min."],
            ]);
        }

        if (! $user || ! Hash::check($request->password, $user->password)) {
            $user?->incrementFailedAttempts();

            throw ValidationException::withMessages([
                'email' => ['Pogrešni kredencijali.'],
            ]);
        }

        $user->resetFailedAttempts();
        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Uspešno odjavljen.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return response()->json($user);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Trenutna lozinka nije ispravna.'],
            ]);
        }

        $user->update(['password' => $request->password]);

        return response()->json(['message' => 'Lozinka promenjena.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Lozinka nije ispravna.'],
            ]);
        }

        // Anonimizacija
        $user->update([
            'name' => 'Obrisani korisnik',
            'email' => "deleted_{$user->id}@anonimized.local",
            'phone' => null,
        ]);

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Nalog obrisan.']);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email već verifikovan.']);
        }

        $request->user()->markEmailAsVerified();

        return response()->json(['message' => 'Email verifikovan.']);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email već verifikovan.']);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verifikacioni email poslat.']);
    }

    public function orders(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()
            ->with('items')
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json(
            \App\Http\Resources\OrderResource::collection($orders)
                ->response()
                ->getData(true)
        );
    }

    public function updateNewsletter(Request $request): JsonResponse
    {
        $request->validate([
            'newsletter_subscribed' => 'required|boolean',
        ]);

        $request->user()->update([
            'newsletter_subscribed' => $request->boolean('newsletter_subscribed'),
        ]);

        return response()->json([
            'message' => $request->boolean('newsletter_subscribed')
                ? 'Uspešno ste se prijavili na newsletter.'
                : 'Uspešno ste se odjavili sa newsletter-a.',
            'newsletter_subscribed' => $request->user()->newsletter_subscribed,
        ]);
    }

    public function showOrder(Request $request, string $orderNumber): JsonResponse
    {
        $order = $request->user()->orders()
            ->with('items.product.images')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return response()->json([
            'data' => new \App\Http\Resources\OrderResource($order),
        ]);
    }
}
