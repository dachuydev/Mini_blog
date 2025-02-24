<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        // Cache danh sách bài viết trong 5 phút
        $page = $request->get('page', 1);
        $cacheKey = 'posts_page_' . $page;
        
        $posts = Cache::remember($cacheKey, 300, function () {
            return Post::with(['user:id,name', 'comments' => function($query) {
                $query->withCount('replies');
            }])
            ->withCount('comments')
            ->latest()
            ->paginate(10);
        });

        return response()->json($posts);
    }

    public function store(PostRequest $request)
    {
        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => auth()->id()
        ]);

        Cache::flush();

        return response()->json([
            'message' => 'Tạo bài viết thành công',
            'post' => $post->load('user:id,name')
        ], 201);
    }

    public function show(Post $post)
    {
        $cacheKey = 'post_' . $post->id;
        
        $post = Cache::remember($cacheKey, 300, function () use ($post) {
            return $post->load(['user:id,name', 'comments' => function($query) {
                $query->with('user:id,name')
                    ->withCount('replies');
            }]);
        });

        return response()->json($post);
    }

    public function update(PostRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        $post->update([
            'title' => $request->title,
            'content' => $request->content
        ]);

        // Clear cache của bài viết này
        Cache::forget('post_' . $post->id);
        Cache::flush();

        return response()->json([
            'message' => 'Cập nhật bài viết thành công',
            'post' => $post->load('user:id,name')
        ]);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        // Clear cache
        Cache::forget('post_' . $post->id);
        Cache::flush();

        return response()->json([
            'message' => 'Xóa bài viết thành công'
        ]);
    }
} 