<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LangController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminGamePromptsController;
use App\Http\Controllers\AdminResultsController;
use App\Http\Controllers\AdminAppSettingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDealerController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\UpgradeController;
use App\Http\Controllers\AddFundsController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\TaxRatesController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ManualNotificationController;
use App\Http\Controllers\Admin\FcmSettingsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\InstallScriptController;
use App\Http\Controllers\StripeConnectController;
use App\Http\Controllers\StripeWebHookController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\TwoFactorAuthController;
use App\Http\Controllers\CountriesStatesController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\AdminLiveAlertsController;
use App\Http\Controllers\Api\LiveAlertsApiController;
use App\Http\Controllers\RolesAndPermissionsController;
use App\Http\Controllers\AjaxController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('home', function() {
    return redirect('/');
});

// Authentication
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::get('logout', [LoginController::class, 'logout']);

Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset']);

// Social Login
Route::group(['middleware' => 'guest'], function() {
    Route::get('oauth/{provider}', [SocialAuthController::class, 'redirect'])->where('provider', '(facebook|google|twitter)$');
    Route::get('oauth/{provider}/callback', [SocialAuthController::class, 'callback'])->where('provider', '(facebook|google|twitter)$');
});

// Public Routes
Route::get('members',[HomeController::class, 'members']);
Route::get('categories',[HomeController::class, 'categories']);
Route::get('pricing',[HomeController::class, 'pricing']);
Route::get('category/{slug}',[HomeController::class, 'category']);
Route::get('category/{slug}/{subcategory}', [HomeController::class, 'subcategory']);
Route::get('tags',[HomeController::class, 'tags']);
Route::get('tags/{tags}',[HomeController::class, 'tagsShow']);
Route::get('search', [HomeController::class, 'getSearch']);
Route::get('contact',[HomeController::class, 'contact']);
Route::post('contact',[HomeController::class, 'contactStore']);

// TTS Routes
// TTS tasks route removed - app converted

// TTS Callback (no auth required - called by external API)
// TTS routes removed - app converted

// Account Verification
Route::get('verify/account/{confirmation_code}', [HomeController::class, 'getVerifyAccount'])->where('confirmation_code','[A-Za-z0-9]+');

// Static Pages
Route::get('page/{page}',[PagesController::class, 'show'])->where('page','[^/]*' );

// Sitemaps
Route::get('sitemaps.xml', function() {
    return response()->view('default.sitemaps')->header('Content-Type', 'application/xml');
});

// Public API Routes (No Auth Required)
Route::get('api/apk/version-info', [AdminController::class, 'getApkVersionInfo']);
Route::get('api/help-videos', [AdminController::class, 'getHelpVideosApi']);
Route::post('api/help-videos/{id}/increment-view', [AdminController::class, 'incrementHelpVideoView']);
Route::get('api/live-alerts', [LiveAlertsApiController::class, 'getLiveAlerts']);

// Authenticated User Routes
Route::group(['middleware' => 'auth'], function() {
    // Account Settings
    Route::get('account',[UserController::class, 'account']);
    Route::post('account',[UserController::class, 'update_account']);


    // Delete Account
    Route::get('account/delete',[UserController::class, 'delete']);
    Route::post('account/delete',[UserController::class, 'delete_account']);

    // Upload Avatar & Cover
    Route::post('upload/avatar',[UserController::class, 'upload_avatar']);
    Route::post('upload/cover',[UserController::class, 'upload_cover']);

    // User Features
    Route::get('likes',[UserController::class, 'userLikes']);
    Route::get('feed',[UserController::class, 'followingFeed']);
    Route::get('notifications',[UserController::class, 'notifications']);
    Route::get('notifications/delete',[UserController::class, 'notificationsDelete']);

    // Manual Notifications
    Route::get('manual-notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('manual-notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');

    // Report User
    Route::post('report/user',[UserController::class, 'report']);

    // Comments
    Route::post('comment/store',[CommentsController::class, 'store']);
    Route::post('comment/delete',[CommentsController::class, 'destroy']);
    Route::post('comment/like',[CommentsController::class, 'like']);

    // User Balance API
    Route::get('api/user/balance', [App\Http\Controllers\Api\UserController::class, 'getUserBalance']);

    // TTS API Routes removed - app converted

    // AJAX Routes
    Route::get('ajax/notifications', [AjaxController::class, 'notifications']);

    // Dashboard
    Route::get('user/dashboard',[DashboardController::class, 'dashboard']);
    Route::get('user/dashboard/deposits',[DashboardController::class, 'deposits']);
    Route::get('user/dashboard/add/funds',[DashboardController::class, 'addFunds']);
    Route::post('user/dashboard/add/funds',[AddFundsController::class, 'send']);

    // Withdrawals

    // Stripe Connect
    Route::get('stripe/connect', [StripeConnectController::class, 'redirectToStripe'])->name('redirect.stripe');
    Route::get('connect/{token}', [StripeConnectController::class, 'saveStripeAccount'])->name('save.stripe');

    // Subscriptions
    Route::post('buy/subscription',[SubscriptionsController::class, 'buy']);
    Route::get('account/subscription',[UserController::class, 'subscription']);
    Route::get('buy/subscription/success',[SubscriptionsController::class, 'success'])->name('success.subscription');
    Route::post('account/subscription/cancel',[SubscriptionsController::class, 'cancel']);

    // Recharge/Deposit
    Route::get('account/recharge',[UserController::class, 'recharge']);
    Route::post('account/recharge',[UserController::class, 'storeRecharge']);
});

// Comments Likes (Public)
Route::post('comments/likes',[CommentsController::class, 'getLikes']);

// User Profiles
Route::get('{slug}', [UserController::class, 'profile'])->where('slug','[A-Za-z0-9\_-]+')->name('profile');
Route::get('{slug}/followers', [UserController::class, 'followers'])->where('slug','[A-Za-z0-9\_-]+');
Route::get('{slug}/following', [UserController::class, 'following'])->where('slug','[A-Za-z0-9\_-]+');

// Admin Routes
Route::group(['middleware' => 'role'], function() {
    // Upgrades
    Route::get('update/{version}',[UpgradeController::class, 'update']);

    // Dashboard
    Route::get('panel/admin',[AdminController::class, 'dashboard'])->name('dashboard');

    // Categories Management
    Route::get('panel/admin/categories',[AdminController::class, 'categories'])->name('categories');
    Route::get('panel/admin/categories/add',[AdminController::class, 'addCategories']);
    Route::post('panel/admin/categories/add',[AdminController::class, 'storeCategories']);
    Route::get('panel/admin/categories/edit/{id}',[AdminController::class, 'editCategories']);
    Route::post('panel/admin/categories/update',[AdminController::class, 'updateCategories']);
    Route::post('panel/admin/categories/delete/{id}',[AdminController::class, 'deleteCategories']);

    // Subcategories
    Route::get('panel/admin/subcategories',[AdminController::class, 'subcategories']);
    Route::get('panel/admin/subcategories/add',[AdminController::class, 'addSubcategories']);
    Route::post('panel/admin/subcategories/add',[AdminController::class, 'storeSubcategories']);
    Route::get('panel/admin/subcategories/edit/{id}',[AdminController::class, 'editSubcategories']);
    Route::post('panel/admin/subcategories/update',[AdminController::class, 'updateSubcategories']);
    Route::post('panel/admin/subcategories/delete/{id}',[AdminController::class, 'deleteSubcategories']);

    // Settings
    Route::get('panel/admin/settings',[AdminController::class, 'settings'])->name('general_settings');
    Route::post('panel/admin/settings',[AdminController::class, 'saveSettings']);
    Route::get('panel/admin/settings/limits',[AdminController::class, 'settingsLimits']);
    Route::post('panel/admin/settings/limits',[AdminController::class, 'saveSettingsLimits']);

    Route::view('panel/admin/announcements','admin.announcements')->name('announcements');
    Route::post('panel/admin/announcements', [AdminController::class, 'storeAnnouncements']);

    // Members Management
    Route::get('panel/admin/members',[AdminUserController::class, 'index'])->name('members');
    Route::get('panel/admin/members/export',[AdminUserController::class, 'exportUsers'])->name('admin.export_users');
    Route::get('panel/admin/members/edit/{id}',[AdminUserController::class, 'edit']);
    Route::post('panel/admin/members/edit/{id}', [AdminUserController::class, 'update']);

    // Specific routes must come before parameterized routes
    Route::post('panel/admin/members/credit', function() {
        \Log::info('CREDIT ROUTE HIT');
        return app(AdminUserController::class)->creditUser(request());
    })->name('members.credit');
    Route::post('panel/admin/members/debit', function() {
        \Log::info('DEBIT ROUTE HIT');
        return app(AdminUserController::class)->debitUser(request());
    })->name('members.debit');
    Route::get('panel/admin/members/test',[AdminUserController::class, 'testMethod'])->name('members.test');

    // Parameterized route must come last
    Route::post('panel/admin/members/{id}', [AdminUserController::class, 'destroy'])->name('user.destroy');

    // Dealership Routes
    Route::get('panel/admin/dealership-requests', [AdminDealerController::class, 'dealershipRequests'])->name('dealership_requests');
    Route::post('panel/admin/dealership-requests/approve', [AdminDealerController::class, 'approveDealershipRequest'])->name('dealership_requests.approve');
    Route::post('panel/admin/dealership-requests/reject', [AdminDealerController::class, 'rejectDealershipRequest'])->name('dealership_requests.reject');
    Route::get('panel/admin/dealers', [AdminDealerController::class, 'dealers'])->name('dealers');
    Route::post('panel/admin/dealers/remove-status', [AdminDealerController::class, 'removeDealerStatus'])->name('dealers.remove_status');
    Route::get('panel/admin/dealer-orders', [AdminDealerController::class, 'dealerOrders'])->name('dealer_orders');
    Route::get('panel/admin/dealer-deposits', [AdminDealerController::class, 'dealerDeposits'])->name('dealer_deposits');
    Route::get('panel/admin/dealer-withdrawals', [AdminDealerController::class, 'dealerWithdrawals'])->name('dealer_withdrawals');
    Route::get('panel/admin/dealer-transactions', [AdminDealerController::class, 'dealerTransactions'])->name('dealer_transactions');

    // Orders Management
    Route::get('panel/admin/orders',[AdminController::class, 'orders'])->name('orders');
    Route::get('panel/admin/orders/export-pdf',[AdminController::class, 'exportOrdersPdf'])->name('orders.export.pdf');
    Route::get('panel/admin/orders/export-winner',[AdminController::class, 'exportWinnerPdf'])->name('orders.export.winner');
    Route::post('panel/admin/orders/store',[AdminController::class, 'store_orders']);
    Route::post('panel/admin/orders/update',[AdminController::class, 'update_orders']);
    Route::post('panel/admin/orders/update-status',[AdminController::class, 'updateOrderStatus']);
    Route::post('panel/admin/orders/update-np',[AdminController::class, 'updateOrderNp']);
    Route::post('panel/admin/orders/delete',[AdminController::class, 'delete_orders']);
    Route::post('panel/admin/orders/delete-all',[AdminController::class, 'deleteAllOrders'])->name('orders.delete.all');
    Route::get('panel/admin/orders/soft-deleted',[AdminController::class, 'softDeletedOrders'])->name('orders.soft.deleted');
    Route::post('panel/admin/orders/restore',[AdminController::class, 'restoreOrder'])->name('orders.restore');
    Route::post('panel/admin/orders/restore-bulk',[AdminController::class, 'restoreBulkOrders'])->name('orders.restore.bulk');
    Route::post('panel/admin/orders/restore-all',[AdminController::class, 'restoreAllOrders'])->name('orders.restore.all');
    Route::post('panel/admin/orders/delete-all-soft-deleted',[AdminController::class, 'deleteAllSoftDeletedOrders'])->name('orders.delete.all.soft.deleted');
    Route::post('panel/admin/orders/permanently-delete',[AdminController::class, 'permanentlyDeleteOrder'])->name('orders.permanently.delete');

    // Transactions Management
    Route::get('panel/admin/transactions',[AdminController::class, 'transactions'])->name('admin.transactions');
    Route::post('panel/admin/transactions/delete-all',[AdminController::class, 'deleteAllTransactions'])->name('admin.transactions.delete-all');

    // Deposits Management
    Route::get('panel/admin/deposits',[AdminController::class, 'deposits'])->name('admin.deposits');
    Route::post('panel/admin/deposits/delete-all',[AdminController::class, 'deleteAllDeposits'])->name('admin.deposits.delete-all');

    // Withdrawals Management
    Route::get('panel/admin/withdrawals',[AdminController::class, 'withdrawals'])->name('admin.withdrawals');
    Route::post('panel/admin/withdrawals/delete-all',[AdminController::class, 'deleteAllWithdrawals'])->name('admin.withdrawals.delete-all');
    Route::post('panel/admin/withdrawals/approve',[AdminController::class, 'approveWithdrawal'])->name('admin.withdrawals.approve');
    Route::post('panel/admin/withdrawals/reject',[AdminController::class, 'rejectWithdrawal'])->name('admin.withdrawals.reject');

    // Reported Members
    Route::get('panel/admin/members-reported',[AdminController::class, 'members_reported'])->name('members_reported');
    Route::post('panel/admin/members-reported',[AdminController::class, 'delete_members_reported']);

    // Pages Management
    Route::get('panel/admin/pages',[PagesController::class, 'index'])->name('pages');
    Route::get('panel/admin/pages/create',[PagesController::class, 'create']);
    Route::post('panel/admin/pages/create',[PagesController::class, 'store']);
    Route::get('panel/admin/pages/edit/{id}',[PagesController::class, 'edit']);
    Route::post('panel/admin/pages/edit/{id}', [PagesController::class, 'update']);
    Route::post('panel/admin/pages/{id}', [PagesController::class, 'destroy'])->name('pages.destroy');

    // Social Profiles
    Route::get('panel/admin/profiles-social',[AdminController::class, 'profiles_social'])->name('profiles_social');
    Route::post('panel/admin/profiles-social',[AdminController::class, 'update_profiles_social']);

    // Google Settings
    Route::get('panel/admin/google',[AdminController::class, 'google'])->name('google');
    Route::post('panel/admin/google',[AdminController::class, 'update_google']);

    // Languages
    Route::get('panel/admin/languages',[LangController::class, 'index'])->name('languages');
    Route::get('panel/admin/languages/create',[LangController::class, 'create']);
    Route::post('panel/admin/languages/create',[LangController::class, 'store']);
    Route::get('panel/admin/languages/edit/{id}',[LangController::class, 'edit']);
    Route::post('panel/admin/languages/edit/{id}', [LangController::class, 'update']);
    Route::post('panel/admin/languages/{id}', [LangController::class, 'destroy'])->name('languages.destroy');

    // Theme
    Route::get('panel/admin/theme',[AdminController::class, 'theme'])->name('theme');
    Route::post('panel/admin/theme',[AdminController::class, 'themeStore']);

    // Custom CSS/JS
    Route::view('panel/admin/custom-css-js','admin.css-js')->name('custom_css_js');
    Route::post('panel/admin/custom-css-js',[AdminController::class, 'customCssJs']);

    // Payment Settings
    Route::get('panel/admin/payments',[AdminController::class, 'payments'])->name('payment_settings');
    Route::post('panel/admin/payments',[AdminController::class, 'savePayments']);
    Route::get('panel/admin/payments/{id}',[AdminController::class, 'paymentsGateways']);
    Route::post('panel/admin/payments/{id}',[AdminController::class, 'savePaymentsGateways']);

    // Game Prompts Management
    Route::get('panel/admin/game-prompts', [AdminGamePromptsController::class, 'index'])->name('admin.game_prompts');
    Route::get('panel/admin/game-prompts/view/{id}', [AdminGamePromptsController::class, 'view'])->name('admin.game_prompts.view');
    Route::get('panel/admin/game-prompts/export-pdf/{id}', [AdminGamePromptsController::class, 'exportPdf'])->name('admin.game_prompts.export_pdf');
    Route::get('panel/admin/game-prompts/export-exported-pdf/{id}', [AdminGamePromptsController::class, 'exportExportedPdf'])->name('admin.game_prompts.export_exported_pdf');
    Route::post('panel/admin/game-prompts/store', [AdminGamePromptsController::class, 'store'])->name('admin.game_prompts.store');
    Route::post('panel/admin/game-prompts/update', [AdminGamePromptsController::class, 'update'])->name('admin.game_prompts.update');
    Route::post('panel/admin/game-prompts/delete/{id}', [AdminGamePromptsController::class, 'delete'])->name('admin.game_prompts.delete');
    Route::post('panel/admin/game-prompts/mark-as-done', [AdminGamePromptsController::class, 'markAsDone'])->name('admin.game_prompts.mark_as_done');
    Route::post('panel/admin/game-prompts/mark-as-undone', [AdminGamePromptsController::class, 'markAsUndone'])->name('admin.game_prompts.mark_as_undone');
    Route::post('panel/admin/game-prompts/mark-all-as-undone', [AdminGamePromptsController::class, 'markAllAsUndone'])->name('admin.game_prompts.mark_all_as_undone');

    // Results Management
    Route::get('panel/admin/results', [AdminResultsController::class, 'index'])->name('admin.results');
    Route::get('panel/admin/results/api', [AdminResultsController::class, 'apiSettings'])->name('admin.results.api');
    Route::post('panel/admin/results/store', [AdminResultsController::class, 'store'])->name('admin.results.store');
    Route::post('panel/admin/results/update/{id}', [AdminResultsController::class, 'update'])->name('admin.results.update');
    Route::post('panel/admin/results/delete/{id}', [AdminResultsController::class, 'destroy'])->name('admin.results.delete');
    Route::post('panel/admin/results/generate-key', [AdminResultsController::class, 'generateApiKey'])->name('admin.results.generate_key');

    // App Settings (Result App)
    Route::get('panel/admin/app-settings', [AdminAppSettingsController::class, 'index'])->name('admin.app_settings');
    Route::post('panel/admin/app-settings/update', [AdminAppSettingsController::class, 'updateSettings'])->name('admin.app_settings.update');

    // News Ticker
    Route::post('panel/admin/news-ticker/store', [AdminAppSettingsController::class, 'storeNews'])->name('admin.news_ticker.store');
    Route::post('panel/admin/news-ticker/update', [AdminAppSettingsController::class, 'updateNews'])->name('admin.news_ticker.update');
    Route::post('panel/admin/news-ticker/delete/{id}', [AdminAppSettingsController::class, 'deleteNews'])->name('admin.news_ticker.delete');

    // IP TV Links
    Route::post('panel/admin/iptv/store', [AdminAppSettingsController::class, 'storeIpTv'])->name('admin.iptv.store');
    Route::post('panel/admin/iptv/update', [AdminAppSettingsController::class, 'updateIpTv'])->name('admin.iptv.update');
    Route::post('panel/admin/iptv/delete/{id}', [AdminAppSettingsController::class, 'deleteIpTv'])->name('admin.iptv.delete');
    Route::post('panel/admin/iptv/parse-m3u', [AdminAppSettingsController::class, 'parseM3u'])->name('admin.iptv.parse_m3u');
    Route::post('panel/admin/iptv/store-bulk', [AdminAppSettingsController::class, 'storeBulkIpTv'])->name('admin.iptv.store_bulk');

    // Deposits Management - Approve/Reject
    Route::post('panel/admin/deposits/approve',[AdminController::class, 'approveDeposit'])->name('deposits.approve');
    Route::post('panel/admin/deposits/reject',[AdminController::class, 'rejectDeposit'])->name('deposits.reject');

    // Withdrawals Management

    // Maintenance
    Route::view('panel/admin/maintenance', 'admin.maintenance')->name('maintenance_mode');
    Route::post('panel/admin/maintenance',[AdminController::class, 'maintenance']);
    Route::get('panel/admin/clear-cache', [AdminController::class, 'clearCache']);

    // System Logs
    Route::get('panel/admin/system-logs', [AdminController::class, 'systemLogs'])->name('system_logs');
    Route::post('panel/admin/system-logs/delete', [AdminController::class, 'deleteSystemLogs'])->name('system_logs.delete');

    // Billing - Payment Methods
    Route::get('panel/admin/billing',[AdminController::class, 'billing'])->name('billing');
    Route::post('panel/admin/payment-methods/store',[AdminController::class, 'storePaymentMethod'])->name('payment-methods.store');
    Route::get('panel/admin/payment-methods/{id}',[AdminController::class, 'getPaymentMethod'])->name('payment-methods.get');
    Route::post('panel/admin/payment-methods/update',[AdminController::class, 'updatePaymentMethod'])->name('payment-methods.update');
    Route::delete('panel/admin/payment-methods/{id}',[AdminController::class, 'deletePaymentMethod'])->name('payment-methods.delete');

    // Billing - Withdrawal Methods
    Route::get('panel/admin/withdrawal-methods',[AdminController::class, 'withdrawalMethods'])->name('withdrawal-methods');
    Route::post('panel/admin/withdrawal-methods/store',[AdminController::class, 'storeWithdrawalMethod'])->name('withdrawal-methods.store');
    Route::get('panel/admin/withdrawal-methods/{id}',[AdminController::class, 'getWithdrawalMethod'])->name('withdrawal-methods.get');
    Route::post('panel/admin/withdrawal-methods/update',[AdminController::class, 'updateWithdrawalMethod'])->name('withdrawal-methods.update');
    Route::delete('panel/admin/withdrawal-methods/{id}',[AdminController::class, 'deleteWithdrawalMethod'])->name('withdrawal-methods.delete');

    // Paid Services
    Route::get('panel/admin/paid-services',[AdminController::class, 'paidServices'])->name('paid-services');
    Route::get('panel/admin/paid-services/sales',[AdminController::class, 'paidServiceSales'])->name('paid-services.sales');
    Route::post('panel/admin/paid-services/store',[AdminController::class, 'storePaidService'])->name('paid-services.store');
    Route::get('panel/admin/paid-services/{id}',[AdminController::class, 'getPaidService'])->name('paid-services.get');
    Route::post('panel/admin/paid-services/update',[AdminController::class, 'updatePaidService'])->name('paid-services.update');
    Route::delete('panel/admin/paid-services/{id}',[AdminController::class, 'deletePaidService'])->name('paid-services.delete');

    // Paid Service Sales Management
    Route::post('panel/admin/paid-services/sales/change-status',[AdminController::class, 'changeSaleStatus'])->name('paid-services.sales.change-status');
    Route::post('panel/admin/paid-services/sales/refund',[AdminController::class, 'refundSale'])->name('paid-services.sales.refund');
    Route::delete('panel/admin/paid-services/sales/delete',[AdminController::class, 'deleteSale'])->name('paid-services.sales.delete');
    Route::delete('panel/admin/paid-services/sales/delete-all',[AdminController::class, 'deleteAllSales'])->name('paid-services.sales.delete-all');

    // Tax Rates
    Route::get('panel/admin/tax-rates',[TaxRatesController::class, 'show'])->name('tax_rates');
    Route::view('panel/admin/tax-rates/add', 'admin.add-tax');
    Route::post('panel/admin/tax-rates/add', [TaxRatesController::class, 'store']);
    Route::get('panel/admin/tax-rates/edit/{id}', [TaxRatesController::class, 'edit']);
    Route::post('panel/admin/tax-rates/update', [TaxRatesController::class, 'update']);
    Route::post('panel/admin/ajax/states', [TaxRatesController::class, 'getStates']);

    // Plans
    Route::get('panel/admin/plans',[PlansController::class, 'show'])->name('plans');
    Route::view('panel/admin/plans/add', 'admin.add-plan');
    Route::post('panel/admin/plans/add', [PlansController::class, 'store']);
    Route::get('panel/admin/plans/edit/{id}', [PlansController::class, 'edit']);
    Route::post('panel/admin/plans/update', [PlansController::class, 'update']);

    // Subscriptions
    Route::get('panel/admin/subscriptions',[AdminController::class, 'subscriptions'])->name('subscriptions');

    // Countries & States
    Route::get('panel/admin/countries', [CountriesStatesController::class, 'countries'])->name('countries');
    Route::view('panel/admin/countries/add', 'admin.add-country');
    Route::post('panel/admin/countries/add', [CountriesStatesController::class, 'addCountry']);
    Route::get('panel/admin/countries/edit/{id}', [CountriesStatesController::class, 'editCountry']);
    Route::post('panel/admin/countries/update', [CountriesStatesController::class, 'updateCountry']);
    Route::post('panel/admin/countries/delete/{id}', [CountriesStatesController::class, 'deleteCountry']);

    Route::get('panel/admin/states', [CountriesStatesController::class, 'states'])->name('states');
    Route::view('panel/admin/states/add', 'admin.add-state');
    Route::post('panel/admin/states/add', [CountriesStatesController::class, 'addState']);
    Route::get('panel/admin/states/edit/{id}', [CountriesStatesController::class, 'editState']);
    Route::post('panel/admin/states/update', [CountriesStatesController::class, 'updateState']);
    Route::post('panel/admin/states/delete/{id}', [CountriesStatesController::class, 'deleteState']);

    // Email Settings
    Route::view('panel/admin/settings/email','admin.email-settings')->name('email_settings');
    Route::post('panel/admin/settings/email',[AdminController::class, 'emailSettings']);

    // Storage
    Route::view('panel/admin/storage','admin.storage')->name('storage');
    Route::post('panel/admin/storage',[AdminController::class, 'storage']);

    // Social Login
    Route::view('panel/admin/social-login','admin.social-login')->name('social_login');
    Route::post('panel/admin/social-login',[AdminController::class, 'updateSocialLogin']);

    // PWA
    Route::view('panel/admin/pwa','admin.pwa')->name('pwa');
    Route::post('panel/admin/pwa',[AdminController::class, 'pwa']);

    // Roles & Permissions
    Route::get('panel/admin/roles-and-permissions', [RolesAndPermissionsController::class, 'index'])->name('role_and_permissions');
    Route::view('panel/admin/roles-and-permissions/create', 'admin.add-role');
    Route::post('panel/admin/roles-and-permissions/create', [RolesAndPermissionsController::class, 'store']);
    Route::get('panel/admin/roles-and-permissions/edit/{id}', [RolesAndPermissionsController::class, 'edit']);
    Route::post('panel/admin/roles-and-permissions/update', [RolesAndPermissionsController::class, 'update']);
    Route::post('panel/admin/roles-and-permissions/delete/{id}', [RolesAndPermissionsController::class, 'destroy']);

    // Manual Notifications
    Route::resource('panel/admin/manual-notifications', ManualNotificationController::class)->names([
        'index' => 'admin.manual_notifications.index',
        'create' => 'admin.manual_notifications.create',
        'store' => 'admin.manual_notifications.store',
        'show' => 'admin.manual_notifications.show',
        'edit' => 'admin.manual_notifications.edit',
        'update' => 'admin.manual_notifications.update',
        'destroy' => 'admin.manual_notifications.destroy',
    ]);
    Route::patch('panel/admin/manual-notifications/{manualNotification}/toggle-status', [ManualNotificationController::class, 'toggleStatus'])->name('admin.manual_notifications.toggle_status');
    Route::post('panel/admin/manual-notifications/delete-all', [ManualNotificationController::class, 'deleteAll'])->name('admin.manual_notifications.delete_all');

    // FCM Settings
    Route::get('panel/admin/fcm-settings', [FcmSettingsController::class, 'index'])->name('admin.fcm_settings.index');
    Route::post('panel/admin/fcm-settings', [FcmSettingsController::class, 'update'])->name('admin.fcm_settings.update');
    Route::post('panel/admin/fcm-settings/store', [FcmSettingsController::class, 'store'])->name('admin.fcm_settings.store');
    Route::delete('panel/admin/fcm-settings/delete/{id}', [FcmSettingsController::class, 'destroy'])->name('admin.fcm_settings.destroy');

    // APK Version Management
    Route::get('panel/admin/apk-versions', [AdminController::class, 'apkVersions'])->name('apk.versions');
    Route::post('panel/admin/apk-versions/upload-file', [AdminController::class, 'uploadApkFile'])->name('apk.upload.file');
    Route::post('panel/admin/apk-versions/upload-chunk', [AdminController::class, 'uploadApkChunk'])->name('apk.upload.chunk');
    Route::post('panel/admin/apk-versions/update-link', [AdminController::class, 'updateApkLink'])->name('apk.update.link');
    Route::post('panel/admin/apk-versions/update-platform-url', [AdminController::class, 'updateApkPlatformUrl'])->name('apk.update.platform.url');
    Route::post('panel/admin/apk-versions/toggle-active/{id}', [AdminController::class, 'toggleApkActive'])->name('apk.toggle.active');
    Route::delete('panel/admin/apk-versions/delete/{id}', [AdminController::class, 'deleteApkVersion'])->name('apk.delete');

    // Help Videos Management
    Route::get('panel/admin/help-videos', [AdminController::class, 'helpVideos'])->name('admin.help.videos');
    Route::post('panel/admin/help-videos/store', [AdminController::class, 'storeHelpVideo'])->name('admin.help.videos.store');
    Route::post('panel/admin/help-videos/toggle-active/{id}', [AdminController::class, 'toggleHelpVideoActive'])->name('admin.help.videos.toggle');
    Route::delete('panel/admin/help-videos/delete/{id}', [AdminController::class, 'deleteHelpVideo'])->name('admin.help.videos.delete');

    // Live Activity Alerts & Online Users Management
    Route::get('panel/admin/live-alerts', [AdminLiveAlertsController::class, 'index'])->name('admin.live_alerts');
    Route::post('panel/admin/live-alerts/settings', [AdminLiveAlertsController::class, 'updateSettings'])->name('admin.live_alerts.settings');
    Route::post('panel/admin/live-alerts/alert/store', [AdminLiveAlertsController::class, 'storeAlert'])->name('admin.live_alerts.store');
    Route::post('panel/admin/live-alerts/alert/toggle/{id}', [AdminLiveAlertsController::class, 'toggleAlert'])->name('admin.live_alerts.toggle');
    Route::delete('panel/admin/live-alerts/alert/delete/{id}', [AdminLiveAlertsController::class, 'deleteAlert'])->name('admin.live_alerts.delete');
    Route::post('panel/admin/live-alerts/reset-defaults', [AdminLiveAlertsController::class, 'resetDefaults'])->name('admin.live_alerts.reset_defaults');
});

// Public APK Download Routes
Route::get('download/apk', [AdminController::class, 'showDownloadPage'])->name('public.apk.page');
Route::get('download/apk/file/{id}', [AdminController::class, 'downloadApkFile'])->name('public.apk.file');

// Language Switching
Route::get('change/lang/{id}',[LangController::class, 'language'])->where(['id' => '[a-z]+']);

// Installation & Addons
Route::get('install/{addon}',[InstallController::class, 'install']);

// PayPal Routes
Route::get('payment/paypal',[PayPalController::class, 'show'])->name('paypal');
Route::get('paypal/success',[PayPalController::class, 'success'])->name('paypal.success');
Route::get('paypal/buy',[PayPalController::class, 'buy'])->name('paypal.buy');
Route::get('paypal/buy/success', [PayPalController::class, 'successBuy'])->name('buy.success');
Route::get('paypal/verify', [PayPalController::class, 'verifyTransaction'])->name('paypal.verify');
Route::get('payment/paypal/subscription', [PayPalController::class, 'subscription'])->name('paypal.subscription');
Route::post('webhook/paypal', [PayPalController::class, 'webhook'])->name('paypal.webhook');
Route::get('paypal/cancel', [PayPalController::class, 'cancel'])->name('paypal.cancel');

// Stripe Routes
Route::get('payment/stripe', [StripeController::class, 'show'])->name('stripe');
Route::post('payment/stripe/charge', [StripeController::class, 'charge']);
Route::get('payment/stripe/buy', [StripeController::class, 'buy'])->name('stripe.buy');
Route::get('payment/stripe/subscription', [StripeController::class, 'subscription'])->name('stripe.subscription');
Route::post('stripe/webhook', [StripeWebHookController::class, 'handleWebhook']);

// Miscellaneous Routes
Route::get('invoice/{id}',[UserController::class, 'invoice']);
Route::post('verify/2fa', [TwoFactorAuthController::class, 'verify']);
Route::post('2fa/resend', [TwoFactorAuthController::class, 'resend']);

// Installation Script
Route::get('installer/script',[InstallScriptController::class, 'wizard']);
Route::post('installer/script/database',[InstallScriptController::class, 'database']);
Route::post('installer/script/user',[InstallScriptController::class, 'user']);
