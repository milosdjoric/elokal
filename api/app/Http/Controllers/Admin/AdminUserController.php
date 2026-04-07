<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Admin::orderBy('name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins',
            'password' => 'required|string|min:8',
            'role' => 'required|in:super_admin,admin,editor',
            'permissions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $admin = Admin::create($data);
        return response()->json(['data' => $admin], 201);
    }

    public function update(Request $request, Admin $admin): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => "sometimes|email|unique:admins,email,{$admin->id}",
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|in:super_admin,admin,editor',
            'permissions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if (empty($data['password'])) unset($data['password']);

        $admin->update($data);
        return response()->json(['data' => $admin]);
    }

    public function destroy(Admin $admin): JsonResponse
    {
        if ($admin->id === auth()->id()) {
            return response()->json(['message' => 'Ne možete obrisati sopstveni nalog.'], 422);
        }

        $admin->delete();
        return response()->json(['message' => 'Admin obrisan.']);
    }
}
