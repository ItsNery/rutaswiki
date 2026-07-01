<?php

use App\Http\Controllers\CityController;
use App\Http\Controllers\TransitRouteController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Api\RouteApiController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CityController::class, 'home'])->name('home');
Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
Route::get('/cities/create', [CityController::class, 'create'])->middleware(['auth', 'verified'])->name('cities.create');
Route::post('/cities', [CityController::class, 'store'])->middleware(['auth', 'verified'])->name('cities.store');
Route::get('/cities/{city}', [CityController::class, 'show'])->name('cities.show');
// API Routes (public)
Route::get('/api/cities/{city}/routes', [RouteApiController::class, 'index'])->name('api.routes.index');
Route::get('/api/routes/{route}', [RouteApiController::class, 'show'])->name('api.routes.show');
Route::get('/api/nearby-routes', [RouteApiController::class, 'nearby'])->name('api.routes.nearby');
Route::get('/api/calculate-schedule', [TransitRouteController::class, 'calculateSchedule'])->name('api.routes.calculate-schedule');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/cities/{city}/routes/create', [TransitRouteController::class, 'create'])->name('routes.create');
    Route::post('/cities/{city}/routes', [TransitRouteController::class, 'store'])->name('routes.store');
    Route::get('/cities/{city}/routes/{route}/edit', [TransitRouteController::class, 'edit'])->name('routes.edit');
    Route::put('/cities/{city}/routes/{route}', [TransitRouteController::class, 'update'])->name('routes.update');
    
    Route::post('/routes/{route}/vote', [VoteController::class, 'store'])->name('routes.vote');
    Route::post('/routes/{route}/comment', [CommentController::class, 'store'])->name('routes.comment');
});

// Wildcards / history (placed AFTER creation and editing routes to prevent conflicts)
Route::get('/cities/{city}/routes/{route}', [TransitRouteController::class, 'show'])->name('routes.show');
Route::get('/cities/{city}/routes/{route}/history', [TransitRouteController::class, 'history'])->name('routes.history');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
