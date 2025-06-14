<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TodoController;
use App\Http\Controllers\Api\ChartController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/todos', [TodoController::class, 'store']);
Route::get('/todos', [TodoController::class, 'index']);
Route::get('/todos/export', [TodoController::class, 'export']);

Route::get('/chart', [ChartController::class, 'getData']);
