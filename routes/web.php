<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::resource('posts', PostController::class);
Route::get('posts-search', [PostController::class, 'search'])
    ->name('posts.search');
    
Route::get('/', function () {
    return view('welcome');
});
