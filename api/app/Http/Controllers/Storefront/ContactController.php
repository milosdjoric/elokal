<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $message = ContactMessage::create($data);

        // TODO: Email notifikacija adminu kad bude SMTP konfigurisan
        // $adminEmail = Setting::getValue('notifications_admin_email', 'admin@elokal.rs');
        // Mail::to($adminEmail)->send(new ContactFormMail($message));

        Log::info('ContactForm: nova poruka', ['id' => $message->id, 'email' => $message->email]);

        return response()->json([
            'message' => 'Hvala na poruci! Odgovorićemo vam u najkraćem roku.',
        ], 201);
    }
}
