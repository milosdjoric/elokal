<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DownloadableFile;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DownloadableFileController extends Controller
{
    public function index(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $product->downloadableFiles()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:102400', // max 100MB
            'name' => 'nullable|string|max:255',
            'max_downloads' => 'nullable|integer|min:1',
            'expires_days' => 'nullable|integer|min:1',
        ]);

        $file = $request->file('file');
        $path = $file->store('downloads', 'local');

        $downloadable = $product->downloadableFiles()->create([
            'name' => $request->input('name', $file->getClientOriginalName()),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'max_downloads' => $request->max_downloads,
            'expires_days' => $request->expires_days,
        ]);

        return response()->json(['data' => $downloadable], 201);
    }

    public function update(Request $request, DownloadableFile $downloadableFile): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'max_downloads' => 'nullable|integer|min:1',
            'expires_days' => 'nullable|integer|min:1',
            'sort_order' => 'integer',
        ]);

        $downloadableFile->update($request->only(['name', 'max_downloads', 'expires_days', 'sort_order']));

        return response()->json(['data' => $downloadableFile]);
    }

    public function destroy(DownloadableFile $downloadableFile): JsonResponse
    {
        \Illuminate\Support\Facades\Storage::disk('local')->delete($downloadableFile->file_path);
        $downloadableFile->delete();

        return response()->json(['message' => 'Fajl obrisan.']);
    }
}
