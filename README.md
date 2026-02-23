# PHP_Laravel12_PostGreSQL_Enhance

------------------------------------------------------------------------

## Introduction

PHP_Laravel12_PostGreSQL_Enhance is a Laravel 12 project demonstrating advanced PostgreSQL features in a modern Laravel application.

This project integrates PostgreSQL fully, showcasing features like:

- UUID primary keys

- JSONB columns with GIN indexes

- Full-text search using tsvector

- Generated and expression-based columns

- Partial and expression indexes

- A complete CRUD system with search functionality

It is designed to help developers learn how to leverage PostgreSQL’s powerful database capabilities in a Laravel environment.

------------------------------------------------------------------------

## Project Overview

The project implements:

- A Posts module with index, create, edit, delete, and search features.

- Advanced PostgreSQL schema features: GIN indexes, tsvector full-text search, JSON metadata storage, UUID primary keys, and generated slugs.

- Modern Tailwind CSS-based UI for a clean and responsive interface.

- Fully functional CRUD operations with pagination and search using PostgreSQL’s full-text search engine.

------------------------------------------------------------------------

##  Requirements

Make sure your system has:

-   PHP 8.2+
-   Composer
-   PostgreSQL installed
-   Node.js & NPM
-   Laravel 12 compatible environment

------------------------------------------------------------------------

##  Step 1 --- Create Laravel 12 Project

``` bash
composer create-project laravel/laravel PHP_Laravel12_PostGreSQL_Enhance "12.*"
```

``` bash
cd PHP_Laravel12_PostGreSQL_Enhance
```

------------------------------------------------------------------------

## Step 2 --- Configure PostgreSQL


Now open `.env` and update:

``` env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=postgres_enhance
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Create a database in PostgreSQL:

``` sql
CREATE DATABASE postgres_enhance;
```

or

run migration command:

```bash
php artisan migrate
```

------------------------------------------------------------------------

##  Step 3 --- Install PostgreSQL Enhanced Package

Install the official enhancement package:

``` bash
composer require tpetry/laravel-postgresql-enhanced
```

This package extends Laravel's Schema Builder with PostgreSQL-specific
features.

------------------------------------------------------------------------

##  Step 4 --- Create Post Model and Migration

``` bash
php artisan make:model Post -m
```

------------------------------------------------------------------------

##  Step 5 --- Update Migration

Open:

database/migrations/xxxx_xx_xx_create_posts_table.php

Replace with:

``` php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop trigger & function if they exist (safe)
        DB::statement('DROP TRIGGER IF EXISTS posts_searchable_update ON posts;');
        DB::statement('DROP FUNCTION IF EXISTS posts_searchable_trigger();');

        Schema::create('posts', function (Blueprint $table) {

            // UUID Primary Key
            $table->uuid('id')->primary();

            // Basic Fields
            $table->string('title');
            $table->text('content');

            // Status column (for partial index)
            $table->string('status')->default('draft');

            // JSONB column (supports GIN index)
            $table->jsonb('metadata')->nullable();

            // Full Text Search column
            $table->tsvector('searchable')->nullable();

            // Generated slug column
            $table->string('slug')->storedAs('lower(title)');

            $table->timestamps();

            // GIN index for JSONB
            $table->index('metadata', null, 'gin');

            // GIN index for full-text search
            $table->index('searchable', null, 'gin');
        });

        // Expression index (lower title)
        DB::statement('
            CREATE INDEX posts_title_lower_idx
            ON posts (lower(title));
        ');

        // Partial index for published posts
        DB::statement("
            CREATE INDEX posts_published_idx
            ON posts (status)
            WHERE status = 'published';
        ");

        // Full Text Search Trigger Function
        DB::statement("
            CREATE FUNCTION posts_searchable_trigger() RETURNS trigger AS $$
            BEGIN
                NEW.searchable := to_tsvector('english', coalesce(NEW.title,'') || ' ' || coalesce(NEW.content,''));
                RETURN NEW;
            END
            $$ LANGUAGE plpgsql;
        ");

        // Trigger for full-text search
        DB::statement("
            CREATE TRIGGER posts_searchable_update
            BEFORE INSERT OR UPDATE
            ON posts
            FOR EACH ROW
            EXECUTE FUNCTION posts_searchable_trigger();
        ");
    }

    public function down(): void
    {
        // Drop trigger & function first
        DB::statement('DROP TRIGGER IF EXISTS posts_searchable_update ON posts;');
        DB::statement('DROP FUNCTION IF EXISTS posts_searchable_trigger();');

        // Drop expression and partial indexes
        DB::statement('DROP INDEX IF EXISTS posts_title_lower_idx;');
        DB::statement('DROP INDEX IF EXISTS posts_published_idx;');

        // Drop the table
        Schema::dropIfExists('posts');
    }
};
```

Run Migration

``` bash
php artisan migrate
```

------------------------------------------------------------------------

## Step 6 --- Configure Model 

Open:

app/Models/Post.php

``` php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = [
        'title',
        'content',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // UUID settings
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (!$post->id) {
                $post->id = (string) Str::uuid();
            }
        });
    }
}
```

------------------------------------------------------------------------

##  Step 7 --- Create Controller

``` bash
php artisan make:controller PostController --resource
```

Example store method:

``` php
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
```

------------------------------------------------------------------------

## Step 8 --- Define Routes

Open:

routes/web.php

``` php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::resource('posts', PostController::class);
Route::get('posts-search', [PostController::class, 'search'])
    ->name('posts.search');
```

------------------------------------------------------------------------

## Step 9 --- Create Views

Create folder:

resources/views/posts/

Create files:

-   index.blade.php
-   create.blade.php
-   edit.blade.php

### 9.1  index.blade.php

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Posts</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
        }
    </style>
</head>
<body class="min-h-screen text-gray-100">

<div class="max-w-7xl mx-auto px-6 py-10">

    <!-- Header -->
    <div class="flex justify-between items-center mb-10">
        <h1 class="text-3xl font-bold text-indigo-400 tracking-wide">
            🚀 Posts Dashboard
        </h1>

        <a href="{{ route('posts.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 px-6 py-3 rounded-xl font-semibold transition shadow-lg">
            + Create Post
        </a>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-500/20 border border-green-400 text-green-300 p-4 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search -->
    <div class="mb-8">
        <form action="{{ route('posts.search') }}" method="GET"
              class="flex gap-3 bg-white/10 backdrop-blur-lg p-4 rounded-2xl border border-white/20">
            <input type="text"
                   name="q"
                   value="{{ request('q') }}"
                   placeholder="Search posts..."
                   class="flex-1 bg-transparent outline-none text-white placeholder-gray-400">

            <button class="bg-indigo-600 hover:bg-indigo-700 px-6 py-2 rounded-xl font-semibold transition">
                Search
            </button>
        </form>
    </div>

    <!-- Posts Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

        @forelse($posts as $post)
            <div class="bg-white/10 backdrop-blur-xl border border-white/20 p-6 rounded-2xl shadow-xl hover:scale-[1.02] transition">

                <h3 class="text-xl font-bold mb-2 text-indigo-400">
                    {{ $post->title }}
                </h3>

                <p class="text-gray-300 mb-4 line-clamp-3">
                    {{ $post->content }}
                </p>

                <div class="flex justify-between items-center text-sm text-gray-400 mb-4">
                    <span>Status:
                        <span class="px-2 py-1 rounded-lg
                            {{ $post->status === 'published'
                                ? 'bg-green-500/20 text-green-300'
                                : 'bg-yellow-500/20 text-yellow-300' }}">
                            {{ ucfirst($post->status) }}
                        </span>
                    </span>
                </div>

                <div class="flex justify-between items-center">
                    <a href="{{ route('posts.edit', $post->id) }}"
                       class="text-indigo-400 hover:text-indigo-300 font-medium">
                        Edit
                    </a>

                    <form action="{{ route('posts.destroy', $post->id) }}"
                          method="POST"
                          onsubmit="return confirm('Delete this post?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-400 hover:text-red-300 font-medium">
                            Delete
                        </button>
                    </form>
                </div>

            </div>
        @empty
            <p class="text-gray-400">No posts found.</p>
        @endforelse

    </div>

    <!-- Pagination -->
    <div class="mt-10">
        {{ $posts->links() }}
    </div>

</div>

</body>
</html>
```

### 9.2 create.blade.php

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Post</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
        }
    </style>
</head>
<body class="min-h-screen text-gray-100">

<div class="max-w-3xl mx-auto px-6 py-10">

    <!-- Header -->
    <div class="flex justify-between items-center mb-10">
        <h1 class="text-3xl font-bold text-indigo-400 tracking-wide">🚀 Create Post</h1>
        <a href="{{ route('posts.index') }}" 
           class="bg-indigo-600 hover:bg-indigo-700 px-6 py-3 rounded-xl font-semibold transition shadow-lg">
           Back
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 p-8 rounded-2xl shadow-xl">
        <form action="{{ route('posts.store') }}" method="POST" class="space-y-4">
            @csrf

            <input type="text" name="title" placeholder="Title" required
                   class="w-full p-3 rounded-xl bg-white/5 border border-white/20 focus:border-indigo-500 outline-none">

            <textarea name="content" rows="5" placeholder="Content" required
                      class="w-full p-3 rounded-xl bg-white/5 border border-white/20 focus:border-indigo-500 outline-none"></textarea>

            <input type="text" name="author" placeholder="Author"
                   class="w-full p-3 rounded-xl bg-white/5 border border-white/20">

            <input type="text" name="tags" placeholder="tag1, tag2"
                   class="w-full p-3 rounded-xl bg-white/5 border border-white/20">

            <select name="status" required
                    class="w-full p-3 rounded-xl bg-white/5 border border-white/20">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
            </select>

            <button class="w-full bg-indigo-600 hover:bg-indigo-700 py-3 rounded-xl font-semibold transition shadow-lg">
                Save Post
            </button>
        </form>
    </div>

</div>

</body>
</html>
```

###  9.3 edit.blade.php

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Post</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
        }
    </style>
</head>
<body class="min-h-screen text-gray-100">

<div class="max-w-3xl mx-auto px-6 py-10">

    <!-- Header -->
    <div class="flex justify-between items-center mb-10">
        <h1 class="text-3xl font-bold text-indigo-400 tracking-wide">✏️ Edit Post</h1>
        <a href="{{ route('posts.index') }}" 
           class="bg-indigo-600 hover:bg-indigo-700 px-6 py-3 rounded-xl font-semibold transition shadow-lg">
           Back
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 p-8 rounded-2xl shadow-xl">
        <form action="{{ route('posts.update', $post->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <input type="text" name="title" value="{{ $post->title }}" placeholder="Title" required
                   class="w-full p-3 rounded-xl bg-white/5 border border-white/20 focus:border-indigo-500 outline-none">

            <textarea name="content" rows="5" placeholder="Content" required
                      class="w-full p-3 rounded-xl bg-white/5 border border-white/20 focus:border-indigo-500 outline-none">{{ $post->content }}</textarea>

            <input type="text" name="author" value="{{ $post->metadata['author'] ?? '' }}" placeholder="Author"
                   class="w-full p-3 rounded-xl bg-white/5 border border-white/20">

            <input type="text" name="tags" value="{{ isset($post->metadata['tags']) ? implode(',', $post->metadata['tags']) : '' }}" placeholder="tag1, tag2"
                   class="w-full p-3 rounded-xl bg-white/5 border border-white/20">

            <select name="status" required
                    class="w-full p-3 rounded-xl bg-white/5 border border-white/20">
                <option value="draft" {{ $post->status === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ $post->status === 'published' ? 'selected' : '' }}>Published</option>
            </select>

            <button class="w-full bg-indigo-600 hover:bg-indigo-700 py-3 rounded-xl font-semibold transition shadow-lg">
                Update Post
            </button>
        </form>
    </div>

</div>

</body>
</html>
```
------------------------------------------------------------------------

## Step 10 --- Run Development Server

start server:

```bash
php artisan serve
```

Open in browser:

```
http://127.0.0.1:8000/posts
```

------------------------------------------------------------------------

## Output

### Index Page With Search

<img width="1919" height="1026" alt="Screenshot 2026-02-23 150926" src="https://github.com/user-attachments/assets/9f1ca8c1-408b-4716-a2e1-f046c2e556f3" />

------------------------------------------------------------------------

## Project Structure

```
PHP_Laravel12_PostGreSQL_Enhance
│
├── app
│   ├── Models
│   │   └── Post.php
│   │
│   └── Http
│       └── Controllers
│           └── PostController.php
│
├── database
│   └── migrations
│       └── 2026_02_23_080952_create_posts_table.php
│
├── resources
│   └── views
│       └── posts
│           ├── index.blade.php
│           ├── create.blade.php
│           └── edit.blade.php
│
├── routes
│   └── web.php
│
├── .env
└── composer.json
```

------------------------------------------------------------------------

Your PHP_Laravel12_PostGreSQL_Enhance Project is now ready!
