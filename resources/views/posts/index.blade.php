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