<?php

use Illuminate\Support\Facades\Route;

Route::get('/status', fn () => response()->json([
    'version' => 'v2',
    'status' => 'planned',
    'message' => 'v2 endpoints are reserved for future backward-compatible improvements.',
]));
