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

            <div class="flex gap-3">
                <a href="{{ route('posts.trash') }}"
                    class="bg-red-600 hover:bg-red-700 px-5 py-3 rounded-xl font-semibold">
                    🗑 Trash
                </a>

                <a href="{{ route('posts.create') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 px-6 py-3 rounded-xl font-semibold">
                    + Create Post
                </a>
            </div>
        </div>

        <!-- ✅ SUCCESS MESSAGE -->
        @if(session('success'))
            <div id="alertBox"
                class="mb-6 px-6 py-4 rounded-xl border border-green-400 bg-green-500/20 text-green-300 flex justify-between items-center">

                <span>✅ {{ session('success') }}</span>

                <button onclick="document.getElementById('alertBox').remove()">
                    ✖
                </button>
            </div>

            <script>
                setTimeout(() => {
                    let box = document.getElementById('alertBox');
                    if (box) box.remove();
                }, 3000);
            </script>
        @endif

        <!-- Search -->
        <form action="{{ route('posts.search') }}" method="GET" class="flex gap-3 mb-8 bg-white/10 p-4 rounded-xl">
            <input type="text" name="q" placeholder="Search..." class="flex-1 bg-transparent outline-none text-white">
            <button class="bg-indigo-600 px-5 py-2 rounded-xl">Search</button>
        </form>

        <!-- Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

            @forelse($posts as $post)
                <div class="bg-white/10 p-6 rounded-2xl shadow-lg">

                    <!-- Title -->
                    <h3 class="text-xl font-bold text-indigo-400 mb-2">
                        {{ $post->title }}
                    </h3>

                    <!-- Content -->
                    <p class="text-gray-300 mb-4">
                        {{ $post->content }}
                    </p>

                    <!-- Status -->
                    <p class="mb-3">
                        Status:
                        <span class="{{ $post->status == 'published' ? 'text-green-400' : 'text-yellow-400' }}">
                            {{ $post->status }}
                        </span>
                    </p>

                    <!-- ❤️ FAVORITE -->
                    <form action="{{ route('posts.favorite', $post->id) }}" method="POST">
                        @csrf
                        <button class="mb-3">
                            @if($post->is_favorite)
                                💖 Remove Favorite
                            @else
                                🤍 Add Favorite
                            @endif
                        </button>
                    </form>

                    <!-- ❤️ LIKE -->
                    <button onclick="likePost('{{ $post->id }}')" class="mb-3">
                        ❤️ <span id="like-{{ $post->id }}">{{ $post->likes }}</span>
                    </button>

                    <!-- Actions -->
                    <div class="flex justify-between">
                        <a href="{{ route('posts.edit', $post->id) }}" class="text-indigo-400">
                            Edit
                        </a>

                        <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-400">Delete</button>
                        </form>
                    </div>

                </div>
            @empty
                <p>No posts found</p>
            @endforelse

        </div>

        <!-- Pagination + Showing -->
        <div class="mt-10 flex justify-between items-center">

            <p class="text-gray-400 text-sm">
                Showing {{ $posts->firstItem() }}
                to {{ $posts->lastItem() }}
                of {{ $posts->total() }} results
            </p>

            <div>
                {{ $posts->links('pagination::tailwind') }}
            </div>

        </div>

    </div>

    <!-- LIKE JS -->
    <script>
        function likePost(id) {
            fetch(`/posts/${id}/like`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(res => res.json())
                .then(data => {
                    document.getElementById('like-' + id).innerText = data.likes;
                });
        }
    </script>

</body>

</html>