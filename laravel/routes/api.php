<?php

use App\Services\Comment\Http\Controllers\CommentController;
use App\Services\News\Http\Controllers\NewsController;
use App\Services\VideoPost\Http\Controllers\VideoPostController;
use Illuminate\Support\Facades\Route;

Route::prefix('news')->group(function () {
    Route::get('/', [NewsController::class, 'index']);
    Route::post('/', [NewsController::class, 'store']);
    Route::get('/{id}', [NewsController::class, 'show']);
    Route::put('/{id}', [NewsController::class, 'update']);
    Route::delete('/{id}', [NewsController::class, 'destroy']);
});

Route::prefix('video-posts')->group(function () {
    Route::get('/', [VideoPostController::class, 'index']);
    Route::post('/', [VideoPostController::class, 'store']);
    Route::get('/{id}', [VideoPostController::class, 'show']);
    Route::put('/{id}', [VideoPostController::class, 'update']);
    Route::delete('/{id}', [VideoPostController::class, 'destroy']);
});

Route::prefix('comments')->group(function () {
    Route::post('/', [CommentController::class, 'store']);
    Route::put('/{id}', [CommentController::class, 'update']);
    Route::delete('/{id}', [CommentController::class, 'destroy']);
});
