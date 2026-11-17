<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EconomicController;
use App\Http\Controllers\Api\EssayController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FactionContractController;
use App\Http\Controllers\Api\GovernmentInsightController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RecallController;
use App\Http\Controllers\Api\ReputationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SocialAssistanceController;
use App\Http\Controllers\Api\SortitionController;
use App\Http\Controllers\Api\VoteController;
use App\Http\Controllers\Api\WelfareController;
use App\Models\WelfareData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    $user = $request->user();
    $user->refresh();

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'cluster_id' => $user->cluster_id,
        'cluster_name' => $user->cluster_id === null
            ? null
            : WelfareData::query()
                ->where('region_code', $user->cluster_id)
                ->value('region_name'),
        'role' => $user->role,
        'is_verified' => $user->is_verified,
        'reputation_points' => (int) $user->reputation_points,
        'reputation_level' => $user->reputation_level,
    ]);
})->middleware('auth:sanctum');

Route::post('/auth/guest', [AuthController::class, 'guest'])->middleware('throttle:300,1');
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:10,1');

Route::get('/welfare/summary', [WelfareController::class, 'summary']);
Route::get('/welfare/regions', [WelfareController::class, 'regions']);
Route::get('/welfare/region/{code}', [WelfareController::class, 'region']);
Route::get('/economic/summary', [EconomicController::class, 'summary']);
Route::get('/economic/history/{type}', [EconomicController::class, 'history']);
Route::get('/insights/news', [GovernmentInsightController::class, 'news']);
Route::get('/insights/cabinet', [GovernmentInsightController::class, 'cabinet']);
Route::get('/rights/check/{nik}', [SocialAssistanceController::class, 'check'])
    ->middleware('throttle:10,1');
Route::get('/contracts', [FactionContractController::class, 'index']);

Route::middleware(['auth:sanctum', 'role:super_admin|pengurus'])->group(function () {
    Route::post('/contracts', [FactionContractController::class, 'store']);
    Route::patch('/contracts/{contract}', [FactionContractController::class, 'update']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::get('/reputation', ReputationController::class);
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
    Route::get('/votes/active', [VoteController::class, 'active']);
    Route::post('/votes/submit', [VoteController::class, 'submit']);
    Route::post('/recall/request', [RecallController::class, 'store']);
    Route::get('/recall/active', [RecallController::class, 'active']);
    Route::post('/recall/vote', [RecallController::class, 'vote']);
    Route::get('/sortition/panels', [SortitionController::class, 'panels']);
});
