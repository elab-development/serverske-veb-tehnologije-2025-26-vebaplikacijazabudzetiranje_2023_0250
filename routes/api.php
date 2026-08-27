<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ExpenseController;

//Rute za registraciju, prijavu i odjavu korisnika
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//Zasticene rute koje zahtevaju autentifikaciju
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('groups', GroupController::class);
    Route::apiResource('expenses', ExpenseController::class);

    Route::get('/groups/{id}/expenses', [GroupController::class, 'expenses']);
    Route::post('/groups/{id}/members', [GroupController::class, 'addMember']);
    Route::get('/groups/{id}/balance-summary', [GroupController::class, 'balanceSummary']);
    Route::get('/groups/{id}/export-csv', [GroupController::class, 'exportExpensesCsv']);
    Route::get('/exchange-rate/{currency}', [ExpenseController::class, 'exchangeRate']);
    Route::get('/public-holidays/{countryCode}', [ExpenseController::class, 'publicHolidays']);
});