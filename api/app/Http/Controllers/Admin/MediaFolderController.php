<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaFolder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaFolderController extends Controller
{
    public function index(): JsonResponse
    {
        $folders = MediaFolder::withCount('images')
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->withCount('images')])
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $folders]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:media_folders,id',
        ]);

        $folder = MediaFolder::create($data);

        return response()->json(['data' => $folder], 201);
    }

    public function update(Request $request, MediaFolder $folder): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
        ]);

        $folder->update($data);

        return response()->json(['data' => $folder]);
    }

    public function destroy(MediaFolder $folder): JsonResponse
    {
        // Oslobodi slike — ne briši ih, samo ukloni folder_id
        $folder->images()->update(['folder_id' => null]);
        $folder->children()->update(['parent_id' => null]);
        $folder->delete();

        return response()->json(['message' => 'Folder obrisan.']);
    }

    public function moveImage(Request $request): JsonResponse
    {
        $request->validate([
            'image_id' => 'required|exists:product_images,id',
            'folder_id' => 'nullable|exists:media_folders,id',
        ]);

        \App\Models\ProductImage::where('id', $request->image_id)
            ->update(['folder_id' => $request->folder_id]);

        return response()->json(['message' => 'Slika premeštena.']);
    }
}
