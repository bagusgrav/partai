<?php

use App\Http\Controllers\Api\EssayController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/review', [ReviewController::class, 'index']);
    Route::post('/answer', [ReviewController::class, 'answer']);
    Route::get('/essay/question', [EssayController::class, 'question']);
    Route::post('/essay/submit', [EssayController::class, 'submit']);
    Route::get('/essay/review', [EssayController::class, 'pending']);
    Route::post('/essay/review', [EssayController::class, 'review']);
});
