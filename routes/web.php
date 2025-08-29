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
})->name('index');

// Legal Pages
Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');

Route::get('/terms-of-service', function () {
    return view('terms-of-service');
})->name('terms-of-service');

Route::get('/cookies', function () {
    return view('cookies');
})->name('cookies');

// Language Routes
Route::post('/change-language', [App\Http\Controllers\LanguageController::class, 'changeLanguage'])->name('change-language');
Route::get('/current-language', [App\Http\Controllers\LanguageController::class, 'getCurrentLanguage'])->name('current-language');

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
    Route::post('/dashboard/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
    Route::post('/dashboard/profile/avatar', [DashboardController::class, 'updateAvatar'])->name('dashboard.profile.avatar.update');
    Route::post('/dashboard/profile/avatar/remove', [DashboardController::class, 'removeAvatar'])->name('dashboard.profile.avatar.remove');
    Route::get('/dashboard/settings', [DashboardController::class, 'settings'])->name('dashboard.settings');
    Route::post('/dashboard/password', [DashboardController::class, 'updatePassword'])->name('dashboard.password.update');
    
    // Subscription Routes
    Route::get('/dashboard/subscription', [App\Http\Controllers\SubscriptionController::class, 'index'])->name('dashboard.subscription.index');
    Route::post('/dashboard/subscription', [App\Http\Controllers\SubscriptionController::class, 'subscribe'])->name('dashboard.subscription.subscribe');
    Route::post('/dashboard/subscription/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('dashboard.subscription.cancel');
    Route::post('/dashboard/subscription/renew', [App\Http\Controllers\SubscriptionController::class, 'renew'])->name('dashboard.subscription.renew');
    
    // Expired Subscription Route
    Route::get('/subscription/expired', [App\Http\Controllers\SubscriptionController::class, 'expired'])->name('subscription.expired');
});

// Dashboard Routes with Subscription Check (Protected)
Route::middleware(['auth', 'subscription'])->group(function () {
    // Projects Routes
    Route::get('/dashboard/projects', [App\Http\Controllers\ProjectsController::class, 'index'])->name('dashboard.projects');
    Route::get('/dashboard/projects/load-content', [App\Http\Controllers\ProjectsController::class, 'loadContent'])->name('dashboard.projects.load-content');
    Route::post('/dashboard/projects', [App\Http\Controllers\ProjectsController::class, 'store'])->name('dashboard.projects.store');
    Route::get('/dashboard/projects/{project}', [App\Http\Controllers\ProjectsController::class, 'show'])->name('dashboard.projects.show');
    Route::put('/dashboard/projects/{project}', [App\Http\Controllers\ProjectsController::class, 'update'])->name('dashboard.projects.update');
    Route::delete('/dashboard/projects/{project}', [App\Http\Controllers\ProjectsController::class, 'destroy'])->name('dashboard.projects.destroy');

    // Campaign Management
    Route::get('/dashboard/campaigns', [App\Http\Controllers\CampaignController::class, 'index'])->name('dashboard.campaigns.index');
    Route::post('/dashboard/campaigns', [App\Http\Controllers\CampaignController::class, 'store'])->name('dashboard.campaigns.store');
    Route::get('/dashboard/campaigns/{id}', [App\Http\Controllers\CampaignController::class, 'show'])->name('dashboard.campaigns.show');
    Route::put('/dashboard/campaigns/{id}', [App\Http\Controllers\CampaignController::class, 'update'])->name('dashboard.campaigns.update');
    Route::delete('/dashboard/campaigns/{id}', [App\Http\Controllers\CampaignController::class, 'destroy'])->name('dashboard.campaigns.destroy');
    
    // AI Campaign Suggestions
    Route::get('/dashboard/campaigns/products/list', [App\Http\Controllers\CampaignController::class, 'getProductsForCampaign'])->name('dashboard.campaigns.products');
    Route::post('/dashboard/campaigns/ai-suggestions', [App\Http\Controllers\CampaignController::class, 'generateAICampaignSuggestions'])->name('dashboard.campaigns.ai-suggestions');
    Route::post('/dashboard/campaigns/create-from-ai', [App\Http\Controllers\CampaignController::class, 'createFromAISuggestion'])->name('dashboard.campaigns.create-from-ai');
    Route::post('/dashboard/campaigns/create-multiple-from-ai', [App\Http\Controllers\CampaignController::class, 'createMultipleFromAISuggestions'])->name('dashboard.campaigns.create-multiple-from-ai');
    
    // FAQ Management
    Route::get('/dashboard/faqs', [App\Http\Controllers\FAQController::class, 'index'])->name('dashboard.faqs.index');
    
    // Knowledge Base Routes
    Route::get('/dashboard/knowledge-base', [KnowledgeBaseController::class, 'index'])->name('dashboard.knowledge-base');
    Route::get('/dashboard/knowledge-base/load-content', [KnowledgeBaseController::class, 'loadContent'])->name('dashboard.knowledge-base.load-content');
    Route::post('/dashboard/knowledge-base/upload', [KnowledgeBaseController::class, 'uploadFile'])->name('dashboard.knowledge-base.upload');
    Route::post('/dashboard/knowledge-base/fetch-url', [KnowledgeBaseController::class, 'fetchFromUrl'])->name('dashboard.knowledge-base.fetch-url');
    Route::post('/dashboard/knowledge-base/refresh-images', [KnowledgeBaseController::class, 'refreshImageAnalysis'])->name('dashboard.knowledge-base.refresh-images');
    Route::get('/dashboard/knowledge-base/image-status', [KnowledgeBaseController::class, 'getImageAnalysisStatus'])->name('dashboard.knowledge-base.image-status');
    Route::delete('/dashboard/knowledge-base/{id}', [KnowledgeBaseController::class, 'destroy'])->name('dashboard.knowledge-base.destroy');
    Route::get('/dashboard/knowledge-base/{id}/detail', [KnowledgeBaseController::class, 'getDetail'])->name('dashboard.knowledge-base.detail');
    
    // Chat Session Dashboard Routes
    Route::get('/dashboard/chat-sessions', [App\Http\Controllers\ChatSessionDashboardController::class, 'index'])->name('dashboard.chat-sessions');
    Route::get('/dashboard/chat-sessions/{session_id}', [App\Http\Controllers\ChatSessionDashboardController::class, 'show'])->name('dashboard.chat-sessions.show');
    Route::get('/dashboard/chat-sessions/export', [App\Http\Controllers\ChatSessionDashboardController::class, 'export'])->name('dashboard.chat-sessions.export');
    
    Route::get('/dashboard/analytics', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('dashboard.analytics');
    
    // Widget Design
    Route::get('/dashboard/widget-design', [App\Http\Controllers\WidgetDesignController::class, 'index'])->name('dashboard.widget-design');
    Route::get('/dashboard/widget-design/load-content', [App\Http\Controllers\WidgetDesignController::class, 'loadContent'])->name('dashboard.widget-design.load-content');
    Route::post('/dashboard/widget-design/store', [App\Http\Controllers\WidgetDesignController::class, 'store'])->name('dashboard.widget-design.store');
    Route::post('/dashboard/widget-design/test-endpoint', [App\Http\Controllers\WidgetDesignController::class, 'testEndpoint'])->name('dashboard.widget-design.test-endpoint');
    
    // Widget Customization Routes
    Route::get('/dashboard/widget-customization', [App\Http\Controllers\WidgetCustomizationController::class, 'getCustomization'])->name('dashboard.widget-customization.get');
    
    // Personal Token Routes
    Route::get('/dashboard/personal-token', [App\Http\Controllers\PersonalTokenController::class, 'getTokenInfo'])->name('dashboard.personal-token.info');
    Route::post('/dashboard/personal-token/generate', [App\Http\Controllers\PersonalTokenController::class, 'generateToken'])->name('dashboard.personal-token.generate');
    Route::delete('/dashboard/personal-token/revoke', [App\Http\Controllers\PersonalTokenController::class, 'revokeToken'])->name('dashboard.personal-token.revoke');
});

// Admin Routes (Protected)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    
    // User Management Routes
    Route::get('/admin/users/{id}', [AdminController::class, 'getUser'])->name('admin.users.get');
    Route::put('/admin/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/admin/users/{id}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('admin.users.toggle-admin');
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    
    Route::get('/admin/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');
Route::get('/admin/analytics/load-content', [AdminController::class, 'loadAnalyticsContent'])->name('admin.analytics.load-content');
    
    // Plans Management
    Route::resource('admin/plans', \App\Http\Controllers\Admin\PlanController::class)->names([
        'index' => 'admin.plans.index',
        'create' => 'admin.plans.create',
        'store' => 'admin.plans.store',
        'show' => 'admin.plans.show',
        'edit' => 'admin.plans.edit',
        'update' => 'admin.plans.update',
        'destroy' => 'admin.plans.destroy',
    ]);
    
    // Subscriptions Management
    Route::resource('admin/subscriptions', \App\Http\Controllers\Admin\SubscriptionController::class)->names([
        'index' => 'admin.subscriptions.index',
        'create' => 'admin.subscriptions.create',
        'store' => 'admin.subscriptions.store',
        'show' => 'admin.subscriptions.show',
        'edit' => 'admin.subscriptions.edit',
        'update' => 'admin.subscriptions.update',
        'destroy' => 'admin.subscriptions.destroy',
    ]);
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


