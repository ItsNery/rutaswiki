<?php

use App\Http\Controllers\CityController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TransitRouteController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Api\RouteApiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CityController::class, 'home'])->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
Route::get('/map', [CityController::class, 'map'])->name('map');
Route::get('/cities/create', [CityController::class, 'create'])->middleware(['auth', 'verified'])->name('cities.create');
Route::post('/cities', [CityController::class, 'store'])->middleware(['auth', 'verified'])->name('cities.store');
Route::get('/cities/{city}', [CityController::class, 'show'])->name('cities.show');
// API Routes (public)
Route::get('/api/routes/search', [RouteApiController::class, 'search'])->name('api.routes.search');
Route::get('/api/cities/{city}/routes', [RouteApiController::class, 'index'])->name('api.routes.index');
Route::get('/api/routes/{route}', [RouteApiController::class, 'show'])->name('api.routes.show');
Route::get('/api/nearby-routes', [RouteApiController::class, 'nearby'])->name('api.routes.nearby');
Route::post('/api/suggest-places', [RouteApiController::class, 'suggestPlaces'])->name('api.routes.suggest-places');
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

// Wildcards / history / diff (placed AFTER creation and editing routes to prevent conflicts)
Route::get('/cities/{city}/routes/{route}', [TransitRouteController::class, 'show'])->name('routes.show');
Route::get('/cities/{city}/routes/{route}/history', [TransitRouteController::class, 'history'])->name('routes.history');
Route::get('/cities/{city}/routes/{route}/history/diff/{revision}', [TransitRouteController::class, 'diff'])->name('routes.history.diff');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ─── Admin Routes (protected by Spatie 'admin' role) ─────
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Cities
    Route::get('/cities', [AdminController::class, 'cities'])->name('cities');
    Route::delete('/cities/{city}', [AdminController::class, 'deleteCity'])->name('cities.delete');
    Route::post('/cities/{id}/restore', [AdminController::class, 'restoreCity'])->name('cities.restore');
    Route::delete('/cities/{id}/force', [AdminController::class, 'forceDeleteCity'])->name('cities.force-delete');

    // Routes
    Route::get('/routes', [AdminController::class, 'routes'])->name('routes');
    Route::delete('/routes/{route}', [AdminController::class, 'deleteRoute'])->name('routes.delete');
    Route::post('/routes/{id}/restore', [AdminController::class, 'restoreRoute'])->name('routes.restore');
    Route::delete('/routes/{id}/force', [AdminController::class, 'forceDeleteRoute'])->name('routes.force-delete');
});

require __DIR__.'/auth.php';
