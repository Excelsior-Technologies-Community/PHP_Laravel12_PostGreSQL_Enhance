<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Trash Posts</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
        }
    </style>
</head>

<body class="min-h-screen text-gray-100">

    <div class="max-w-6xl mx-auto px-6 py-10">

        <!-- Header -->
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-bold text-red-400 tracking-wide">
                🗑 Trash Manager
            </h1>

            <a href="{{ route('posts.index') }}"
                class="bg-indigo-600 hover:bg-indigo-700 px-5 py-2 rounded-xl shadow-lg">
                ⬅ Back to Posts
            </a>
        </div>

        <!-- ✅ Success Message -->
        @if(session('success'))
            <div class="mb-6 px-6 py-4 rounded-xl bg-green-500/20 border border-green-400 text-green-300 shadow">
                ✅ {{ session('success') }}
            </div>
        @endif

        <!-- Grid -->
        <div class="grid md:grid-cols-2 gap-6">

            @forelse($posts as $post)
                <div class="bg-white/10 backdrop-blur-lg border border-white/10 
                            p-6 rounded-2xl shadow-lg hover:scale-[1.02] transition duration-300">

                    <!-- Title -->
                    <h3 class="text-xl font-semibold text-indigo-400 mb-2">
                        {{ $post->title }}
                    </h3>

                    <!-- Deleted Date -->
                    <p class="text-sm text-gray-400 mb-4">
                        Deleted: {{ $post->deleted_at->format('d M Y, h:i A') }}
                    </p>

                    <!-- Badge -->
                    <span class="inline-block mb-4 px-3 py-1 text-xs rounded-full bg-red-500/20 text-red-300">
                        In Trash
                    </span>

                    <!-- Actions -->
                    <div class="flex justify-between items-center">

                        <!-- Restore -->
                        <a href="{{ route('posts.restore', $post->id) }}"
                            class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-xl text-sm shadow">
                            ♻ Restore
                        </a>

                        <!-- Permanent Delete -->
                        <form action="{{ route('posts.forceDelete', $post->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Delete permanently?')"
                                class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-xl text-sm shadow">
                                ❌ Delete
                            </button>
                        </form>

                    </div>

                </div>

            @empty
                <!-- Empty State -->
                <div class="col-span-2 text-center mt-20">
                    <h2 class="text-2xl text-gray-400 mb-2">No Trash Found 🧹</h2>
                    <p class="text-gray-500">Deleted posts will appear here</p>
                </div>
            @endforelse

        </div>

    </div>

</body>

</html>