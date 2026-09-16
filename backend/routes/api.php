<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public (read-only) portfolio endpoints
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'index']);
Route::get('profile', [PublicController::class, 'profile']);
Route::get('profile/{id}', [PublicController::class, 'profile'])->whereNumber('id');
Route::get('skills', [PublicController::class, 'skills']);
Route::get('projects', [PublicController::class, 'projects']);
Route::get('projects/{slug}', [PublicController::class, 'project']);
Route::get('posts', [PublicController::class, 'posts']);
Route::get('posts/{slug}', [PublicController::class, 'post']);
Route::get('publications', [PublicController::class, 'publications']);
Route::get('services', [PublicController::class, 'services']);
Route::get('testimonials', [PublicController::class, 'testimonials']);
Route::get('settings', [PublicController::class, 'settings']);
Route::get('search', [SearchController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Public write (contact form + analytics)
|--------------------------------------------------------------------------
*/
Route::post('contact', [ContactController::class, 'store']);
Route::post('feedback', [FeedbackController::class, 'store']);
Route::post('analytics', [AnalyticsController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
});
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Tefera Portfolio API is running',
    ]);
});
/*
|--------------------------------------------------------------------------
| Admin dashboard (protected + permission-gated)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::get('analytics/overview', [\App\Http\Controllers\Admin\AnalyticsController::class, 'overview']);

    Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'index']);
    Route::apiResource('profiles', \App\Http\Controllers\Admin\ProfileController::class)
        ->only(['index', 'show', 'update']);

    Route::apiResource('skills', \App\Http\Controllers\Admin\SkillController::class);
    Route::apiResource('experiences', \App\Http\Controllers\Admin\ExperienceController::class);
    Route::apiResource('educations', \App\Http\Controllers\Admin\EducationController::class);
    Route::apiResource('certifications', \App\Http\Controllers\Admin\CertificationController::class);
    Route::apiResource('projects', \App\Http\Controllers\Admin\ProjectController::class);
    Route::apiResource('publications', \App\Http\Controllers\Admin\PublicationController::class);
    Route::apiResource('services', \App\Http\Controllers\Admin\ServiceController::class);
    Route::apiResource('posts', \App\Http\Controllers\Admin\PostController::class);
    Route::apiResource('testimonials', \App\Http\Controllers\Admin\TestimonialController::class);

    Route::get('messages/stats', [\App\Http\Controllers\Admin\MessageController::class, 'stats']);
    Route::apiResource('messages', \App\Http\Controllers\Admin\MessageController::class)
        ->only(['index', 'show', 'destroy']);
    Route::post('messages/{id}/read', [\App\Http\Controllers\Admin\MessageController::class, 'markAsRead']);

    Route::get('feedback/stats', [\App\Http\Controllers\Admin\FeedbackController::class, 'stats']);
    Route::apiResource('feedback', \App\Http\Controllers\Admin\FeedbackController::class)
        ->only(['index', 'show', 'destroy']);
    Route::post('feedback/{id}/read', [\App\Http\Controllers\Admin\FeedbackController::class, 'markAsRead']);

    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index']);
    Route::put('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update']);

    Route::apiResource('media', \App\Http\Controllers\Admin\MediaController::class)
        ->only(['index', 'store', 'destroy']);
});
