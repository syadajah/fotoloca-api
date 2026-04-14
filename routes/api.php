<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AddOnController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Rute yang GAK butuh token (Login)
Route::post('/login', [AuthController::class, 'login']);

// Rute yang BUTUH token (Harus login dulu)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- MANAJEMEN USER (Termasuk update profil) ---
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    // INGAT: Pakai PUT atau PATCH buat update data!
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::patch('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);

    Route::get('/dashboard', [DashboardController::class, 'getDashboard']);

    // --- CRUD LAINNYA ---
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('addons', AddOnController::class);

    // --- TRANSAKSI ---
    Route::get('/transaction/export', [TransactionController::class, 'export']);
    Route::post('/transaction/{id}/send-invoice', [TransactionController::class, 'sendInvoiceEmail']);
    Route::get('transactions/print/{id}', [TransactionController::class, 'printInvoice']);
    Route::get('/transaction', [TransactionController::class, 'index']);
    Route::post('/transaction', [TransactionController::class, 'store']);
    Route::get('/transaction-setup/{id_produk}', [TransactionController::class, 'getTransactionSetup']);

    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
});
