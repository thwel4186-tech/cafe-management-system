<?php
use Illuminate\Support\Facades\Route;
Route::get('/', fn () => response()->json(['application' => 'Cafe Management API', 'frontend' => env('FRONTEND_URL')]));
