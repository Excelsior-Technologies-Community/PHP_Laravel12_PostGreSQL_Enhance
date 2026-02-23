<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    //  List Posts
    public function index()
    {
        $posts = Post::latest()->paginate(10);
        return view('posts.index', compact('posts'));
    }

    //  Show Create Form
    public function create()
    {
        return view('posts.create');
    }

    //  Store New Post
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

    //  Show Edit Form
    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    //  Update Post
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

    //  Delete Post
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully');
    }

    //  Full Text Search
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
}