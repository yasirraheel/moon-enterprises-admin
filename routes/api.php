<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PushNotificationsController;
use App\Http\Controllers\Api\TtsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public API Routes
Route::get('settings', [AuthController::class, 'settings']);
Route::get('game-categories', [AuthController::class, 'getGameCategories']);
Route::get('subcategories', [AuthController::class, 'getSubcategories']);
Route::get('payment-methods', [AuthController::class, 'getPaymentMethods']);
Route::get('withdrawal-methods', [AuthController::class, 'getWithdrawalMethods']);
Route::get('apk-version', [AuthController::class, 'getApkVersion']);
Route::post('apk-download', [AuthController::class, 'incrementApkDownload']);
Route::get('is_trial', [AuthController::class, 'getIsTrial']);

// Help Videos API Routes
Route::get('help-videos', [\App\Http\Controllers\AdminController::class, 'getHelpVideosApi']);
Route::post('help-videos/{id}/increment-view', [\App\Http\Controllers\AdminController::class, 'incrementHelpVideoView']);

// Temporary public route for testing notifications (remove after debugging)
Route::get('notifications-test', [AuthController::class, 'getNotificationsTest']);

// Authentication Routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('profile', [AuthController::class, 'updateProfile']);
    Route::get('user/balance', [UserController::class, 'getUserBalance']);
    Route::post('user/reset-commission', [UserController::class, 'resetCommission']);
    Route::get('notifications', [AuthController::class, 'getNotifications']);
    Route::get('notifications/count', [AuthController::class, 'getNotificationCount']);
    Route::post('notifications/{id}/mark-read', [AuthController::class, 'markNotificationAsRead']);
    Route::post('notifications/mark-all-read', [AuthController::class, 'markAllNotificationsAsRead']);
    Route::post('orders/create', [AuthController::class, 'createOrder']);
    Route::post('orders/create-bulk', [AuthController::class, 'createBulkOrder']);
    Route::get('orders', [AuthController::class, 'getUserOrders']);
    Route::post('deposits/create', [AuthController::class, 'createDeposit']);
    Route::get('deposits', [AuthController::class, 'getUserDeposits']);
    Route::post('withdrawals/create', [AuthController::class, 'createWithdrawal']);
    Route::get('withdrawals', [AuthController::class, 'getUserWithdrawals']);
    Route::get('paid-services', [AuthController::class, 'getPaidServices']); // Moved to protected routes
    Route::post('paid-service/purchase', [AuthController::class, 'purchasePaidService']);
    Route::get('paid-services/test-auth', [AuthController::class, 'testPaidServicesAuth']); // Test authentication
    Route::get('transactions', [AuthController::class, 'getUserTransactions']);

    // Dealership Routes
    Route::post('dealership/apply', [AuthController::class, 'applyForDealership']);

    // FCM Routes
    Route::post('fcm/register', [PushNotificationsController::class, 'registerFcmToken']);
    Route::post('fcm/unregister', [PushNotificationsController::class, 'unregisterFcmToken']);
});

Route::post('device/register', [PushNotificationsController::class, 'registerDevice']);
Route::get('device/delete', [PushNotificationsController::class, 'deleteDevice']);

use App\Http\Controllers\Api\ResultsApiController;
use App\Http\Controllers\Api\AppSettingsApiController;

// Results API Routes (Protected by API Key)
Route::middleware(\App\Http\Middleware\ResultsApiAuth::class)->prefix('results')->group(function () {
    Route::get('/', [ResultsApiController::class, 'index']); // Get all results
    Route::get('/latest', [ResultsApiController::class, 'latest']); // Get latest result
    Route::post('/by-date', [ResultsApiController::class, 'byDate']); // Get results by date

    // App Settings Routes
    Route::get('/app-settings', [AppSettingsApiController::class, 'getAppSettings']);
    Route::get('/news-ticker', [AppSettingsApiController::class, 'getNewsTicker']);
    Route::get('/iptv-links', [AppSettingsApiController::class, 'getIpTvLinks']);
});

// TTS API Routes (moved to web.php for session auth)
// Generate TTS route moved to web.php for session-based authentication

// User Credits Route moved to web.php for session-based auth
