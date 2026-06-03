<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = NewsletterSubscriber::orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('email', 'ilike', "%{$request->search}%");
        }

        $perPage = min($request->input('per_page', 15), 100);

        return response()->json($query->paginate($perPage));
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total' => NewsletterSubscriber::count(),
            'confirmed' => NewsletterSubscriber::where('status', 'confirmed')->count(),
            'pending' => NewsletterSubscriber::where('status', 'pending')->count(),
            'unsubscribed' => NewsletterSubscriber::where('status', 'unsubscribed')->count(),
        ]);
    }

    public function export(): StreamedResponse
    {
        $subscribers = NewsletterSubscriber::confirmed()->orderBy('email')->get();

        return response()->streamDownload(function () use ($subscribers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['email', 'confirmed_at', 'source']);
            foreach ($subscribers as $sub) {
                fputcsv($handle, [
                    $sub->email,
                    $sub->confirmed_at?->toDateTimeString(),
                    $sub->source,
                ]);
            }
            fclose($handle);
        }, 'newsletter-subscribers-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function destroy(NewsletterSubscriber $subscriber): JsonResponse
    {
        $subscriber->delete();

        return response()->json(['message' => 'Pretplatnik obrisan.']);
    }
}
