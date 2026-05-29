<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::resource('posts', PostController::class);

Route::get('/posts-search', [PostController::class, 'search'])
    ->name('posts.search');

Route::get('/search/suggestions', [PostController::class, 'getSuggestions'])
    ->name('posts.suggestions');

Route::post('/posts/{post}/like', [PostController::class, 'like'])
    ->name('posts.like');

Route::post('/posts/{post}/favorite', [PostController::class, 'favorite'])
    ->name('posts.favorite');

Route::get('/posts-trash', [PostController::class, 'trash'])
    ->name('posts.trash');

Route::get('/posts-restore/{id}', [PostController::class, 'restore'])
    ->name('posts.restore');

Route::delete('/posts/{id}/force-delete', [PostController::class, 'forceDelete'])
    ->name('posts.forceDelete');

Route::get('/', function () {
    return view('welcome');
});