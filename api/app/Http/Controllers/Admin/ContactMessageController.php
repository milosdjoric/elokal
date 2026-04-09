<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ContactMessage::orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(20));
    }

    public function show(ContactMessage $contactMessage): JsonResponse
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->update(['status' => 'read']);
        }

        return response()->json(['data' => $contactMessage]);
    }

    public function updateStatus(Request $request, ContactMessage $contactMessage): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', ContactMessage::STATUSES),
        ]);

        $contactMessage->update(['status' => $request->status]);

        return response()->json(['data' => $contactMessage]);
    }

    public function destroy(ContactMessage $contactMessage): JsonResponse
    {
        $contactMessage->delete();

        return response()->json(['message' => 'Poruka obrisana.']);
    }
}
