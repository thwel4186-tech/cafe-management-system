<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CafeController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureActiveUser;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware(['auth:sanctum', EnsureActiveUser::class])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [CafeController::class, 'dashboard']);
    Route::get('/reports', [CafeController::class, 'reports']);
    Route::get('/categories', [CafeController::class, 'categories']);
    Route::post('/categories', [CafeController::class, 'storeCategory']);
    Route::put('/categories/{category}', [CafeController::class, 'updateCategory']);
    Route::delete('/categories/{category}', [CafeController::class, 'deleteCategory']);

    Route::get('/products', [CafeController::class, 'products']);
    Route::post('/products', [CafeController::class, 'storeProduct']);
    Route::put('/products/{product}', [CafeController::class, 'updateProduct']);
    Route::delete('/products/{product}', [CafeController::class, 'deleteProduct']);

    Route::get('/tables', [CafeController::class, 'tables']);
    Route::post('/tables', [CafeController::class, 'storeTable']);
    Route::put('/tables/{table}', [CafeController::class, 'updateTable']);
    Route::delete('/tables/{table}', [CafeController::class, 'deleteTable']);

    Route::get('/orders', [CafeController::class, 'orders']);
    Route::post('/orders', [CafeController::class, 'storeOrder']);
    Route::get('/orders/{order}', [CafeController::class, 'order']);
    Route::patch('/orders/{order}/status', [CafeController::class, 'changeOrderStatus']);
    Route::post('/orders/{order}/pay', [CafeController::class, 'payOrder']);

    Route::get('/expenses', [CafeController::class, 'expenses']);
    Route::post('/expenses', [CafeController::class, 'storeExpense']);
    Route::delete('/expenses/{expense}', [CafeController::class, 'deleteExpense']);

    Route::get('/staff', [CafeController::class, 'staff']);
    Route::post('/staff', [CafeController::class, 'storeStaff']);
    Route::put('/staff/{staff}', [CafeController::class, 'updateStaff']);
});
