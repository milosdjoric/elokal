<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\BlogCategory;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    // --- Posts ---

    public function index(Request $request): JsonResponse
    {
        $query = Post::with(['author', 'categories', 'tags'])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('title', 'ilike', "%{$request->search}%");
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', fn ($q) => $q->where('blog_categories.id', $request->category_id));
        }

        $perPage = min($request->input('per_page', 15), 100);

        return response()->json(
            PostResource::collection($query->paginate($perPage))
                ->response()
                ->getData(true)
        );
    }

    public function show(Post $post): JsonResponse
    {
        $post->load(['author', 'categories', 'tags']);

        return response()->json(['data' => new PostResource($post)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts',
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'required|string',
            'featured_image' => 'nullable|string|max:500',
            'status' => 'required|in:' . implode(',', Post::STATUSES),
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:blog_categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = Post::create(array_merge($data, [
            'admin_id' => $request->user()->id,
        ]));

        if (! empty($data['category_ids'])) {
            $post->categories()->sync($data['category_ids']);
        }
        if (! empty($data['tag_ids'])) {
            $post->tags()->sync($data['tag_ids']);
        }

        $post->load(['author', 'categories', 'tags']);

        return (new PostResource($post))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Post $post): PostResource
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => "sometimes|string|max:255|unique:posts,slug,{$post->id}",
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'sometimes|string',
            'featured_image' => 'nullable|string|max:500',
            'status' => 'sometimes|in:' . implode(',', Post::STATUSES),
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:blog_categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        if (isset($data['status']) && $data['status'] === 'published' && ! $post->published_at && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post->update($data);

        if (array_key_exists('category_ids', $data)) {
            $post->categories()->sync($data['category_ids'] ?? []);
        }
        if (array_key_exists('tag_ids', $data)) {
            $post->tags()->sync($data['tag_ids'] ?? []);
        }

        $post->load(['author', 'categories', 'tags']);

        return new PostResource($post);
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json(['message' => 'Post obrisan.']);
    }

    // --- Blog Categories ---

    public function categories(): JsonResponse
    {
        $categories = BlogCategory::withCount('posts')->orderBy('sort_order')->get();

        return response()->json(['data' => $categories]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_categories',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'integer|min:0',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category = BlogCategory::create($data);

        return response()->json(['data' => $category], 201);
    }

    public function updateCategory(Request $request, BlogCategory $category): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => "sometimes|string|max:255|unique:blog_categories,slug,{$category->id}",
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'integer|min:0',
        ]);

        $category->update($data);

        return response()->json(['data' => $category]);
    }

    public function destroyCategory(BlogCategory $category): JsonResponse
    {
        $category->delete();

        return response()->json(['message' => 'Kategorija obrisana.']);
    }

    // --- Tags ---

    public function tags(): JsonResponse
    {
        $tags = Tag::withCount('posts')->orderBy('name')->get();

        return response()->json(['data' => $tags]);
    }

    public function storeTag(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tags',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $tag = Tag::create($data);

        return response()->json(['data' => $tag], 201);
    }

    public function destroyTag(Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json(['message' => 'Tag obrisan.']);
    }
}
