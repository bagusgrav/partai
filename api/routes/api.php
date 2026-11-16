<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EssayController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WelfareController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/guest', [AuthController::class, 'guest'])->middleware('throttle:300,1');

Route::get('/welfare/summary', [WelfareController::class, 'summary']);
Route::get('/welfare/regions', [WelfareController::class, 'regions']);
Route::get('/welfare/region/{code}', [WelfareController::class, 'region']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events/active', [EventController::class, 'active']);
    Route::get('/events/{id}/qr', [EventController::class, 'qr']);
    Route::post('/attendance/claim', [AttendanceController::class, 'claim']);
    Route::get('/attendance/pending', [AttendanceController::class, 'pending']);
    Route::post('/attendance/attest', [AttendanceController::class, 'attest']);
    Route::get('/review', [ReviewController::class, 'index']);
    Route::post('/answer', [ReviewController::class, 'answer']);
    Route::get('/essay/question', [EssayController::class, 'question']);
    Route::post('/essay/submit', [EssayController::class, 'submit']);
    Route::get('/essay/review', [EssayController::class, 'pending']);
    Route::post('/essay/review', [EssayController::class, 'review']);
});
