<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        $allowedSorts = ['title', 'created_at', 'likes'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder);
        }

        $posts = $query->paginate(5)->withQueryString();

        return view('posts.index', compact('posts'));
    }

    public function getSuggestions(Request $request)
    {
        $term = $request->get('term');
        
        $suggestions = Post::where('title', 'like', '%' . $term . '%')
                           ->limit(5)
                           ->pluck('title');

        return response()->json($suggestions);
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|string',
        ]);

        Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'status' => $request->status,
            'metadata' => [
                'author' => $request->author,
                'tags' => explode(',', $request->tags),
            ]
        ]);

        return redirect()->route('posts.index')
            ->with('success', 'Post created successfully');
    }

    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|string',
        ]);

        $post->update([
            'title' => $request->title,
            'content' => $request->content,
            'status' => $request->status,
            'metadata' => [
                'author' => $request->author,
                'tags' => explode(',', $request->tags),
            ]
        ]);

        return redirect()->route('posts.index')
            ->with('success', 'Post updated successfully');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully');
    }

    public function search(Request $request)
    {
        $query = $request->q;

        $posts = Post::whereRaw(
            "searchable @@ websearch_to_tsquery('english', ?)",
            [$query]
        )
            ->orderByRaw(
                "ts_rank(searchable, websearch_to_tsquery('english', ?)) DESC",
                [$query]
            )
            ->paginate(10);

        return view('posts.index', compact('posts'));
    }

    public function like(Post $post)
    {
        $post->increment('likes');

        return response()->json([
            'likes' => $post->likes
        ]);
    }

    public function trash()
    {
        $posts = Post::onlyTrashed()->latest()->get();
        return view('posts.trash', compact('posts'));
    }

    public function restore($id)
    {
        Post::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('posts.index')
            ->with('success', 'Post restored successfully');
    }

    public function favorite(Post $post)
    {
        $post->update(['is_favorite' => !$post->is_favorite]);

        return redirect()->back()
            ->with('success', $post->is_favorite
                ? 'Added to favorites ❤️'
                : 'Removed from favorites 💔');
    }

    public function forceDelete($id)
    {
        Post::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->back()
            ->with('success', 'Post permanently deleted ❌');
    }
}