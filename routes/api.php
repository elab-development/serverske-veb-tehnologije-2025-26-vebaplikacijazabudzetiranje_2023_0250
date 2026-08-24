<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ExpenseController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('groups', GroupController::class);
    Route::apiResource('expenses', ExpenseController::class);

    Route::get('/groups/{id}/expenses', [GroupController::class, 'expenses']);
    Route::post('/groups/{id}/members', [GroupController::class, 'addMember']);
    Route::get('/exchange-rate/{currency}', [ExpenseController::class, 'exchangeRate']);
});