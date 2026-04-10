<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AuthorController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\LoanController;
use App\Http\Controllers\Api\V1\MemberController;
use App\Http\Controllers\Api\V1\SearchController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:80,1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum', 'throttle:200,1'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::apiResource('authors', AuthorController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('books', BookController::class);
    Route::apiResource('members', MemberController::class);
    Route::apiResource('loans', LoanController::class);
    Route::patch('/loans/{loan}/return', [LoanController::class, 'markReturned']);

    Route::get('/search/global', [SearchController::class, 'global']);
    Route::get('/search/books', [SearchController::class, 'books']);
    Route::get('/search/members', [SearchController::class, 'members']);
});
