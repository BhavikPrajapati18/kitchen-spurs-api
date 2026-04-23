<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::get('/restaurants/{id}', [RestaurantController::class, 'show']);
Route::get('/restaurants/{id}/analytics', [AnalyticsController::class, 'restaurantAnalytics']);
Route::get('/analytics/top-restaurants', [AnalyticsController::class, 'topRestaurants']);