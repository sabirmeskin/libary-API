<?php

use Illuminate\Support\Facades\Route;

Route::get('/status', fn () => response()->json([
    'service' => config('app.name'),
    'status' => 'ok',
    'timestamp' => now()->toISOString(),
]));

Route::prefix('v1')->group(base_path('routes/api_v1.php'));
Route::prefix('v2')->group(base_path('routes/api_v2.php'));
