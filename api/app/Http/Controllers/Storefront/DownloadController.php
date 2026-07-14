<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\FeatureFlag;
use App\Http\Controllers\Controller;
use App\Models\DownloadLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    /**
     * Listaj sva preuzimanja za ulogovanog korisnika.
     */
    public function index(Request $request): JsonResponse
    {
        if (! feature(FeatureFlag::Downloads)) {
            abort(404);
        }

        $downloads = DownloadLog::with(['file', 'order'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (DownloadLog $log) => [
                'id' => $log->id,
                'file_name' => $log->file?->name,
                'file_size' => $log->file?->file_size,
                'order_number' => $log->order?->order_number,
                'download_count' => $log->download_count,
                'max_downloads' => $log->file?->max_downloads,
                'expires_at' => $log->expires_at,
                'can_download' => $log->canDownload() === true,
                'token' => $log->token,
                'created_at' => $log->created_at,
            ]);

        return response()->json(['data' => $downloads]);
    }

    /**
     * Preuzmi fajl po tokenu.
     */
    public function download(string $token): StreamedResponse|JsonResponse
    {
        if (! feature(FeatureFlag::Downloads)) {
            abort(404);
        }

        $log = DownloadLog::with('file')->where('token', $token)->firstOrFail();

        $canDownload = $log->canDownload();
        if ($canDownload !== true) {
            return response()->json(['message' => $canDownload], 403);
        }

        $log->increment('download_count');

        $file = $log->file;

        return Storage::disk('local')->download($file->file_path, $file->name);
    }
}
