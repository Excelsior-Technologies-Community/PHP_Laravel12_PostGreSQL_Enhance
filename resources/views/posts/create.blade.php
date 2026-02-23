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