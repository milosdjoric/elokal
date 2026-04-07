<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\BlogCategory;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Post::published()->with(['author', 'categories', 'tags'])
            ->orderByDesc('published_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->input('per_page', 9), 24);

        return response()->json(
            PostResource::collection($query->paginate($perPage))
                ->response()
                ->getData(true)
        );
    }

    public function show(string $slug): JsonResponse
    {
        $post = Post::published()->with(['author', 'categories', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Related posts iz istih kategorija
        $categoryIds = $post->categories->pluck('id');
        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->whereHas('categories', fn ($q) => $q->whereIn('blog_categories.id', $categoryIds))
            ->with(['author', 'categories'])
            ->limit(3)
            ->get();

        return response()->json([
            'data' => new PostResource($post),
            'related' => PostResource::collection($related),
        ]);
    }

    public function byCategory(string $slug): JsonResponse
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();

        $posts = $category->posts()
            ->published()
            ->with(['author', 'categories', 'tags'])
            ->orderByDesc('published_at')
            ->paginate(9);

        return response()->json(array_merge(
            PostResource::collection($posts)->response()->getData(true),
            ['category' => ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug, 'description' => $category->description]],
        ));
    }

    public function byTag(string $slug): JsonResponse
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $posts = $tag->posts()
            ->published()
            ->with(['author', 'categories', 'tags'])
            ->orderByDesc('published_at')
            ->paginate(9);

        return response()->json(array_merge(
            PostResource::collection($posts)->response()->getData(true),
            ['tag' => ['id' => $tag->id, 'name' => $tag->name, 'slug' => $tag->slug]],
        ));
    }

    public function sidebar(): JsonResponse
    {
        $recentPosts = Post::published()->with('categories')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get(['id', 'title', 'slug', 'featured_image', 'published_at']);

        $categories = BlogCategory::withCount(['posts' => fn ($q) => $q->published()])
            ->orderBy('sort_order')
            ->get();

        $tags = Tag::whereHas('posts', fn ($q) => $q->published())
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->orderByDesc('posts_count')
            ->limit(20)
            ->get();

        return response()->json([
            'recent_posts' => $recentPosts,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }
}
