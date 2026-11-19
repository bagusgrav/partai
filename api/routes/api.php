<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BureaucracyWatchController;
use App\Http\Controllers\Api\CivicEducationController;
use App\Http\Controllers\Api\EconomicController;
use App\Http\Controllers\Api\EssayController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FactionContractController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\GlossaryController;
use App\Http\Controllers\Api\GovernmentInsightController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\MemberReputationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrganizationStructureController;
use App\Http\Controllers\Api\PartyFinanceController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PostReportController;
use App\Http\Controllers\Api\RecallController;
use App\Http\Controllers\Api\ReputationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\SocialAssistanceController;
use App\Http\Controllers\Api\SortitionController;
use App\Http\Controllers\Api\VoteController;
use App\Http\Controllers\Api\WelfareController;
use App\Http\Controllers\Api\WelfareIndexController;
use App\Http\Middleware\IdempotentPasswordRequest;
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
Route::post('/auth/login', [SessionController::class, 'login'])->middleware('throttle:10,1');
Route::get('/auth/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('auth.verify-email');
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware([IdempotentPasswordRequest::class, 'throttle:5,1']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware([IdempotentPasswordRequest::class, 'throttle:5,1']);

Route::get('/welfare/summary', [WelfareController::class, 'summary']);
Route::get('/welfare/regions', [WelfareController::class, 'regions']);
Route::get('/welfare/region/{code}', [WelfareController::class, 'region']);
Route::get('/welfare/ikk', [WelfareIndexController::class, 'index'])->middleware('throttle:60,1');
Route::get('/welfare/ikk/{regionCode}', [WelfareIndexController::class, 'show'])->middleware('throttle:60,1');
Route::post('/welfare/ikk/ingest', [WelfareIndexController::class, 'ingest'])
    ->middleware(['auth:sanctum', 'role:super_admin|pengurus', 'throttle:20,1']);
Route::get('/economic/summary', [EconomicController::class, 'summary']);
Route::get('/economic/history/{type}', [EconomicController::class, 'history']);
Route::get('/economy/indicators', [EconomicController::class, 'indicators']);
Route::get('/economy/regions', [EconomicController::class, 'regions']);
Route::get('/insights/news', [GovernmentInsightController::class, 'news']);
Route::get('/insights/cabinet', [GovernmentInsightController::class, 'cabinet']);
Route::get('/rights/check', [SocialAssistanceController::class, 'check'])
    ->middleware(['auth:sanctum', 'throttle:5,1']);
Route::get('/contracts', [FactionContractController::class, 'index']);
Route::get('/faqs', [FaqController::class, 'index']);
Route::get('/organization/tree', [OrganizationStructureController::class, 'tree'])->middleware('throttle:60,1');
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [SessionController::class, 'logout']);
    Route::get('/organization/eligibility', [OrganizationStructureController::class, 'eligibility']);
    Route::post('/organization/seats/{seat}/apply', [OrganizationStructureController::class, 'apply'])->middleware('throttle:10,1');
});
Route::post('/inquiries', [InquiryController::class, 'store'])->middleware('throttle:5,1');
Route::get('/glossary', [GlossaryController::class, 'index']);
Route::get('/reputation/leaderboard-stats', [MemberReputationController::class, 'leaderboardStats']);
Route::get('/education/overview', [CivicEducationController::class, 'overview'])->middleware('throttle:60,1');
Route::get('/posts', [PostController::class, 'publicIndex'])->middleware('throttle:60,1');
Route::get('/posts/members', [PostController::class, 'memberIndex'])
    ->middleware(['auth:sanctum', 'throttle:60,1']);

Route::get('/finance/summary', [PartyFinanceController::class, 'summary'])->middleware('throttle:60,1');
Route::get('/finance/ledger', [PartyFinanceController::class, 'ledger'])->middleware('throttle:60,1');
Route::post('/finance/donations', [PartyFinanceController::class, 'submitDonation'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/finance/my-dues', [PartyFinanceController::class, 'getMyDues']);
    Route::post('/finance/my-dues', [PartyFinanceController::class, 'submitMyDues'])->middleware('throttle:10,1');
});

Route::get('/bureaucracy/scorecards', [BureaucracyWatchController::class, 'scorecards'])->middleware('throttle:60,1');
Route::get('/bureaucracy/reports', [BureaucracyWatchController::class, 'publicReports'])->middleware('throttle:60,1');
Route::post('/bureaucracy/reports', [BureaucracyWatchController::class, 'store'])->middleware('throttle:5,1');
Route::get('/bureaucracy/reports/track/{ticketCode}', [BureaucracyWatchController::class, 'track'])->middleware('throttle:15,1');

Route::middleware(['auth:sanctum', 'role:super_admin|pengurus'])->group(function () {
    Route::post('/contracts', [FactionContractController::class, 'store'])->middleware('throttle:20,1');
    Route::patch('/contracts/{contract}', [FactionContractController::class, 'update'])->middleware('throttle:20,1');
});

Route::middleware(['auth:sanctum', 'role:super_admin|pengurus|panelis'])->group(function () {
    Route::post('/reputation/contributions', [MemberReputationController::class, 'storeContribution'])
        ->middleware('throttle:10,1');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts', [PostController::class, 'store'])->middleware('throttle:10,1');
    Route::patch('/posts/{post}', [PostController::class, 'update'])->middleware('throttle:10,1');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->middleware('throttle:10,1');
    Route::post('/posts/{post}/report', [PostReportController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/education/session', [CivicEducationController::class, 'session']);
    Route::post('/education/answers', [CivicEducationController::class, 'answer'])->middleware('throttle:30,1');
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->middleware('throttle:30,1');
    Route::get('/reputation', ReputationController::class);
    Route::get('/reputation/me', [MemberReputationController::class, 'me']);
    Route::post('/events', [EventController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/events/active', [EventController::class, 'active']);
    Route::get('/events/{id}/qr', [EventController::class, 'qr']);
    Route::post('/attendance/claim', [AttendanceController::class, 'claim'])->middleware('throttle:20,1');
    Route::get('/attendance/pending', [AttendanceController::class, 'pending']);
    Route::post('/attendance/attest', [AttendanceController::class, 'attest'])->middleware('throttle:20,1');
    Route::get('/review', [ReviewController::class, 'index']);
    Route::post('/answer', [ReviewController::class, 'answer'])->middleware('throttle:30,1');
    Route::get('/essay/question', [EssayController::class, 'question']);
    Route::post('/essay/submit', [EssayController::class, 'submit'])->middleware('throttle:10,1');
    Route::get('/essay/review', [EssayController::class, 'pending']);
    Route::post('/essay/review', [EssayController::class, 'review'])->middleware('throttle:20,1');
    Route::get('/votes/active', [VoteController::class, 'active']);
    Route::post('/votes/submit', [VoteController::class, 'submit'])->middleware('throttle:10,1');
    Route::post('/recall/request', [RecallController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/recall/active', [RecallController::class, 'active']);
    Route::post('/recall/vote', [RecallController::class, 'vote'])->middleware('throttle:10,1');
    Route::get('/sortition/panels', [SortitionController::class, 'panels']);
});
