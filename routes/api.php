<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TagController;

Route::prefix('{lang}')->group(function () {
    // Posts
    Route::get('posts/search', [PostController::class, 'search']);
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{id}', [PostController::class, 'show']);
    Route::post('posts', [PostController::class, 'store']);
    Route::put('posts/{id}', [PostController::class, 'update']);
    Route::delete('posts/{id}', [PostController::class, 'destroy']);
    // Tags
    Route::get('tags', [TagController::class, 'index']);
    Route::get('tags/{id}', [TagController::class, 'show']);
    Route::post('tags', [TagController::class, 'store']);
    Route::put('tags/{id}', [TagController::class, 'update']);
    Route::delete('tags/{id}', [TagController::class, 'destroy']);
})->whereIn('lang', ['ua', 'ru', 'en']);
