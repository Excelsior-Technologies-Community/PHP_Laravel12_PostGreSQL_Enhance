<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Posts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { background: linear-gradient(135deg, #0f172a, #1e293b); }
        .suggestion-item { padding: 10px; cursor: pointer; color: #cbd5e1; background: #1e293b; border-bottom: 1px solid #334155; }
        .suggestion-item:hover { background: #4f46e5; color: white; }
    </style>
</head>
<body class="min-h-screen text-gray-100">

    <div class="max-w-7xl mx-auto px-6 py-10">
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-bold text-indigo-400 tracking-wide">🚀 Posts Dashboard</h1>
            <div class="flex gap-3">
                <a href="{{ route('posts.trash') }}" class="bg-red-600 hover:bg-red-700 px-5 py-3 rounded-xl font-semibold">🗑 Trash</a>
                <a href="{{ route('posts.create') }}" class="bg-indigo-600 hover:bg-indigo-700 px-6 py-3 rounded-xl font-semibold">+ Create Post</a>
            </div>
        </div>

        @if(session('success'))
            <div id="alertBox" class="mb-6 px-6 py-4 rounded-xl border border-green-400 bg-green-500/20 text-green-300 flex justify-between items-center">
                <span>✅ {{ session('success') }}</span>
                <button onclick="document.getElementById('alertBox').remove()">✖</button>
            </div>
        @endif

        <form action="{{ route('posts.index') }}" method="GET" class="mb-8 relative">
            <div class="bg-white/10 p-4 rounded-xl flex gap-3">
                <input type="text" id="search-input" name="search" value="{{ request('search') }}" placeholder="Search..." class="flex-1 bg-transparent outline-none text-white" autocomplete="off">
                <select name="sort" class="bg-indigo-900 rounded-lg p-2 text-white">
                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Newest</option>
                    <option value="likes" {{ request('sort') == 'likes' ? 'selected' : '' }}>Most Liked</option>
                </select>
                <button class="bg-indigo-600 px-5 py-2 rounded-xl">Filter</button>
            </div>
            <div id="suggestions-box" class="absolute w-full bg-slate-800 rounded-xl mt-2 hidden shadow-2xl z-50 border border-slate-700"></div>
        </form>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($posts as $post)
                <div class="bg-white/10 p-6 rounded-2xl shadow-lg">
                    <h3 class="text-xl font-bold text-indigo-400 mb-2">{{ $post->title }}</h3>
                    <p class="text-gray-300 mb-4">{{ $post->content }}</p>
                    <p class="mb-3">Status: <span class="{{ $post->status == 'published' ? 'text-green-400' : 'text-yellow-400' }}">{{ $post->status }}</span></p>
                    
                    <form action="{{ route('posts.favorite', $post->id) }}" method="POST">
                        @csrf
                        <button class="mb-3">{{ $post->is_favorite ? '💖 Remove Favorite' : '🤍 Add Favorite' }}</button>
                    </form>

                    <button onclick="likePost('{{ $post->id }}')" class="mb-3">❤️ <span id="like-{{ $post->id }}">{{ $post->likes }}</span></button>

                    <div class="flex justify-between">
                        <a href="{{ route('posts.edit', $post->id) }}" class="text-indigo-400">Edit</a>
                        <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="text-red-400">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p>No posts found</p>
            @endforelse
        </div>

        <div class="mt-10">{{ $posts->links() }}</div>
    </div>

    <script>
        $('#search-input').on('keyup', function() {
            let term = $(this).val();
            if(term.length >= 2) {
                $.get("{{ route('posts.suggestions') }}", { term: term }, function(data) {
                    let html = '<div class="p-2 text-xs text-indigo-400 font-bold uppercase">Suggestions</div>';
                    data.forEach(item => { html += `<div class="suggestion-item">${item}</div>`; });
                    $('#suggestions-box').html(html).show();
                });
            } else { $('#suggestions-box').hide(); }
        });

        $('#search-input').on('blur', function() {
            let val = $(this).val();
            if(val) {
                let history = JSON.parse(localStorage.getItem('searchHistory') || '[]');
                if(!history.includes(val)) {
                    history.unshift(val);
                    localStorage.setItem('searchHistory', JSON.stringify(history.slice(0, 5)));
                }
            }
        });

        $(document).on('click', '.suggestion-item', function() {
            $('#search-input').val($(this).text());
            $('#suggestions-box').hide();
        });

        function likePost(id) {
            fetch(`/posts/${id}/like`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => res.json())
            .then(data => { document.getElementById('like-' + id).innerText = data.likes; });
        }
    </script>
</body>
</html>