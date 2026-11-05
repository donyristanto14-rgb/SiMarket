<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductModerationController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:register');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('seller')
    ->name('seller.')
    ->middleware(['auth', 'role:student'])
    ->group(function () {
        Route::get('/products', [ProductController::class, 'sellerIndex'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product:slug}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product:slug}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{product:slug}/archive', [ProductController::class, 'archive'])->name('products.archive');
    });

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/products', [ProductModerationController::class, 'index'])->name('products.index');
        Route::patch('/products/{product:slug}/approve', [ProductModerationController::class, 'approve'])->name('products.approve');
        Route::patch('/products/{product:slug}/reject', [ProductModerationController::class, 'reject'])->name('products.reject');
        Route::patch('/products/{product:slug}/suspend', [ProductModerationController::class, 'suspend'])->name('products.suspend');
        Route::patch('/products/{product:slug}/feature', [ProductModerationController::class, 'toggleFeatured'])->name('products.feature');
    });
