<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('posts/filter', [PostController::class, 'filter'])->name('posts.filter');
Route::apiResource('posts', PostController::class);
Route::put('posts/restore/{tagId}', [PostController::class, 'restore'])->name('posts.restore');

Route::post('tags/filter', [TagController::class, 'filter'])->name('tags.filter');
Route::apiResource('tags', TagController::class);
Route::put('tags/restore/{tagId}', [TagController::class, 'restore'])->name('tags.restore');
