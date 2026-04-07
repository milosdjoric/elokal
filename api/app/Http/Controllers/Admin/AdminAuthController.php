<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if ($admin && $admin->isLocked()) {
            $minutes = $admin->lockoutRemainingMinutes();
            throw ValidationException::withMessages([
                'email' => ["Nalog je zaključan. Pokušajte ponovo za {$minutes} min."],
            ]);
        }

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            $admin?->incrementFailedAttempts();

            throw ValidationException::withMessages([
                'email' => ['Pogrešni kredencijali.'],
            ]);
        }

        if (! $admin->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Nalog je deaktiviran.'],
            ]);
        }

        $admin->resetFailedAttempts();
        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'admin' => $admin,
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
}
