<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;


Route::get('/', function () {
    return view('index');
});

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard Routes (Protected)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Campaign Management
    Route::get('/dashboard/campaigns', [App\Http\Controllers\CampaignController::class, 'index'])->name('dashboard.campaigns.index');
    
    // FAQ Management
    Route::get('/dashboard/faqs', [App\Http\Controllers\FAQController::class, 'index'])->name('dashboard.faqs.index');
    Route::get('/dashboard/profile', [DashboardController::class, 'profile'])->name('dashboard.profile');
    Route::post('/dashboard/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
    Route::get('/dashboard/settings', [DashboardController::class, 'settings'])->name('dashboard.settings');
    Route::post('/dashboard/password', [DashboardController::class, 'updatePassword'])->name('dashboard.password.update');
    
    // Knowledge Base Routes
    Route::get('/dashboard/knowledge-base', [KnowledgeBaseController::class, 'index'])->name('dashboard.knowledge-base');
    Route::post('/dashboard/knowledge-base/upload', [KnowledgeBaseController::class, 'uploadFile'])->name('dashboard.knowledge-base.upload');
    Route::post('/dashboard/knowledge-base/fetch-url', [KnowledgeBaseController::class, 'fetchFromUrl'])->name('dashboard.knowledge-base.fetch-url');
    
    // Chat Session Dashboard Routes
    Route::get('/dashboard/chat-sessions', [App\Http\Controllers\ChatSessionDashboardController::class, 'index'])->name('dashboard.chat-sessions');
    Route::get('/dashboard/chat-sessions/{session_id}', [App\Http\Controllers\ChatSessionDashboardController::class, 'show'])->name('dashboard.chat-sessions.show');
    Route::get('/dashboard/chat-sessions/export', [App\Http\Controllers\ChatSessionDashboardController::class, 'export'])->name('dashboard.chat-sessions.export');
    
    Route::get('/dashboard/analytics', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('dashboard.analytics');
    
    // Widget Design
    Route::get('/dashboard/widget-design', [DashboardController::class, 'widgetDesign'])->name('dashboard.widget-design');
    
    // Widget Customization Routes
    Route::get('/dashboard/widget-customization', [App\Http\Controllers\WidgetCustomizationController::class, 'getCustomization'])->name('dashboard.widget-customization.get');
    Route::post('/dashboard/widget-customization', [App\Http\Controllers\WidgetCustomizationController::class, 'updateCustomization'])->name('dashboard.widget-customization.update');
    
    // Personal Token Routes
    Route::get('/dashboard/personal-token', [App\Http\Controllers\PersonalTokenController::class, 'getTokenInfo'])->name('dashboard.personal-token.info');
    Route::post('/dashboard/personal-token/generate', [App\Http\Controllers\PersonalTokenController::class, 'generateToken'])->name('dashboard.personal-token.generate');
    Route::delete('/dashboard/personal-token/revoke', [App\Http\Controllers\PersonalTokenController::class, 'revokeToken'])->name('dashboard.personal-token.revoke');
    
    // API Settings Routes
    Route::get('/dashboard/api-settings', [App\Http\Controllers\ApiSettingsController::class, 'index'])->name('dashboard.api-settings');
    Route::get('/dashboard/api-settings/stats', [App\Http\Controllers\ApiSettingsController::class, 'getStats'])->name('dashboard.api-settings.stats');
    Route::post('/dashboard/api-settings/test-order-status', [App\Http\Controllers\ApiSettingsController::class, 'testOrderStatusApi'])->name('dashboard.api-settings.test-order-status');
    Route::post('/dashboard/api-settings/test-cargo-tracking', [App\Http\Controllers\ApiSettingsController::class, 'testCargoTrackingApi'])->name('dashboard.api-settings.test-cargo-tracking');
    Route::get('/dashboard/api-settings/config', [App\Http\Controllers\ApiSettingsController::class, 'getApiConfig'])->name('dashboard.api-settings.config');
    Route::post('/dashboard/api-settings/config', [App\Http\Controllers\ApiSettingsController::class, 'saveApiConfig'])->name('dashboard.api-settings.save-config');
    Route::post('/dashboard/api-settings/field-mapping', [App\Http\Controllers\ApiSettingsController::class, 'performFieldMapping'])->name('dashboard.api-settings.field-mapping');
    Route::get('/dashboard/api-settings/field-mapping', [App\Http\Controllers\ApiSettingsController::class, 'getFieldMapping'])->name('dashboard.api-settings.get-field-mapping');

});

// Admin Routes (Protected)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');
});







// Test OpenAI API
Route::get('/test-openai', function () {
    try {
        $aiService = new \App\Services\KnowledgeBase\AIService();
        $result = $aiService->testConnection();
        return response()->json($result);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Hata: ' . $e->getMessage()
        ]);
    }
});

// Test Knowledge Base UI
Route::get('/test-kb-ui', function () {
    return view('test-kb-ui');
});

// Public Widget Customization API (for React app)
Route::get('/api/widget-customization', [App\Http\Controllers\WidgetCustomizationController::class, 'getPublicCustomization'])->name('api.widget-customization');


