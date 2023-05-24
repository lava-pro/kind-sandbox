<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TagController;

Route::prefix('{lang}')->group(function () {
    // Posts
    Route::get('posts/search', [PostController::class, 'search'])->name('posts.search');
    Route::get('posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('posts/{id}', [PostController::class, 'show'])->name('posts.show');
    Route::post('posts', [PostController::class, 'store'])->name('posts.store');
    Route::put('posts/{id}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
    // Tags
    Route::get('tags', [TagController::class, 'index'])->name('tags.index');
    Route::get('tags/{id}', [TagController::class, 'show'])->name('tags.show');
    Route::post('tags', [TagController::class, 'store'])->name('tags.store');
    Route::put('tags/{id}', [TagController::class, 'update'])->name('tags.update');
    Route::delete('tags/{id}', [TagController::class, 'destroy'])->name('tags.destroy');
})->whereIn('lang', ['ua', 'ru', 'en']);
