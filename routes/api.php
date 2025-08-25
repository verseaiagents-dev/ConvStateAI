<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIController;
use App\Http\Controllers\TestAPI;
use App\Http\Controllers\KnowledgeBaseController;

// AI Helper API Routes
Route::prefix('ai')->group(function () {
    Route::post('/response', [AIController::class, 'response']);
    Route::post('/personalized', [AIController::class, 'personalizedResponse']);
    Route::get('/stats', [AIController::class, 'getStats']);
    Route::post('/test-quality', [AIController::class, 'testQuality']);
    Route::get('/test-connection', [AIController::class, 'testConnection']);
});

// Chat Routes
Route::post('/chat', [App\Http\Controllers\TestAPI::class, 'chat'])->name('api.chat');
Route::get('/chat/{session_id}',  [TestAPI::class, 'getChatSession']);
Route::post('/chat/{session_id}/clear',  [TestAPI::class, 'clearChatSession']);

// Event handling routes
Route::post('/feedback', [TestAPI::class, 'handleFeedback']);
Route::post('/product-click', [TestAPI::class, 'handleProductClick']);
Route::post('/cargo-tracking', [TestAPI::class, 'handleCargoTracking']);
Route::post('/order-tracking', [TestAPI::class, 'handleOrderTracking']);

// Enhanced Chat Session & Product Interaction Routes
Route::post('/product-interaction', [TestAPI::class, 'handleProductInteraction']);
Route::get('/chat-session/{session_id}/analytics', [TestAPI::class, 'getSessionAnalytics']);

Route::get('/intents/ai-generated',  [TestAPI::class, 'getAIGeneratedIntents']);
Route::get('/intents/stats',  [TestAPI::class, 'getIntentStats']);
Route::get('/categories',  [TestAPI::class, 'getAllCategories']);
Route::get('/categories/{category}',  [TestAPI::class, 'getCategoryDetails']);
Route::get('/categories/analysis/recommendations',  [TestAPI::class, 'getCategoryRecommendations']);

// Knowledge Base API Routes
Route::prefix('knowledge-base')->middleware(['web'])->group(function () {
    Route::get('/', [KnowledgeBaseController::class, 'index']);
    Route::post('/upload', [KnowledgeBaseController::class, 'uploadFile']);
    Route::post('/fetch-url', [KnowledgeBaseController::class, 'fetchFromUrl']);
    Route::post('/search', [KnowledgeBaseController::class, 'search']);
    Route::get('/{id}', [KnowledgeBaseController::class, 'show']);
    Route::get('/{id}/detail', [KnowledgeBaseController::class, 'getDetail']);
    Route::get('/{id}/chunks', [KnowledgeBaseController::class, 'getChunks']);
    Route::delete('/{id}', [KnowledgeBaseController::class, 'destroy']);
    Route::post('/{id}/optimize-faq', [KnowledgeBaseController::class, 'optimizeFAQ']);
    
    // Field Mapping Routes
    Route::get('/{id}/detect-fields', [KnowledgeBaseController::class, 'detectFields']);
    Route::post('/{id}/save-mappings', [KnowledgeBaseController::class, 'saveFieldMappings']);
    Route::post('/{id}/field-mappings', [KnowledgeBaseController::class, 'getFieldMappings']);
    Route::post('/{id}/preview-data', [KnowledgeBaseController::class, 'previewTransformedData']);
    
    // Advanced Field Mapping Routes
    Route::post('/{id}/validate-data', [KnowledgeBaseController::class, 'validateData']);
    Route::post('/{id}/process-batch', [KnowledgeBaseController::class, 'processBatchData']);
    Route::get('/{id}/mapping-stats', [KnowledgeBaseController::class, 'getMappingStats']);
    Route::post('/{id}/export-data', [KnowledgeBaseController::class, 'exportTransformedData']);
});

// Campaign API Routes
Route::prefix('campaigns')->group(function () {
    Route::get('/', [App\Http\Controllers\CampaignController::class, 'index']);
    Route::post('/', [App\Http\Controllers\CampaignController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\CampaignController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\CampaignController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\CampaignController::class, 'destroy']);
    Route::get('/category/{category}', [App\Http\Controllers\CampaignController::class, 'getByCategory']);
    Route::get('/count/active', [App\Http\Controllers\CampaignController::class, 'getActiveCount']);
});

// FAQ API Routes
Route::prefix('faqs')->group(function () {
    Route::get('/', [App\Http\Controllers\FAQController::class, 'index']);
    Route::post('/', [App\Http\Controllers\FAQController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\FAQController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\FAQController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\FAQController::class, 'destroy']);
    Route::get('/category/{category}', [App\Http\Controllers\FAQController::class, 'getByCategory']);
    Route::post('/{id}/helpful', [App\Http\Controllers\FAQController::class, 'markAsHelpful']);
    Route::post('/{id}/not-helpful', [App\Http\Controllers\FAQController::class, 'markAsNotHelpful']);
    Route::get('/search', [App\Http\Controllers\FAQController::class, 'search']);
    Route::get('/popular', [App\Http\Controllers\FAQController::class, 'getPopular']);
});

// Dashboard API Routes (for CRUD operations)
Route::prefix('dashboard')->group(function () {
    // Campaign Management
    Route::post('/campaigns', [App\Http\Controllers\CampaignController::class, 'store']);
    Route::put('/campaigns/{id}', [App\Http\Controllers\CampaignController::class, 'update']);
    Route::delete('/campaigns/{id}', [App\Http\Controllers\CampaignController::class, 'destroy']);
    
    // FAQ Management
    Route::post('/faqs', [App\Http\Controllers\FAQController::class, 'store']);
    Route::put('/faqs/{id}', [App\Http\Controllers\FAQController::class, 'update']);
    Route::delete('/faqs/{id}', [App\Http\Controllers\FAQController::class, 'destroy']);
});

// Product Database Routes
Route::get('/products', [TestAPI::class, 'getProductsFromDB']);
Route::get('/products/stats/categories', [TestAPI::class, 'getCategoryStats']);
Route::get('/products/top-rated', [TestAPI::class, 'getTopRatedProducts']);

// Analytics & Reporting
Route::prefix('analytics')->group(function () {
    Route::get('/real-time', [App\Http\Controllers\AnalyticsController::class, 'getRealTimeAnalytics']);
    Route::get('/export', [App\Http\Controllers\AnalyticsController::class, 'exportAnalytics']);
    Route::post('/custom-range', [App\Http\Controllers\AnalyticsController::class, 'getCustomRangeAnalytics']);
});

// Product Interaction Tracking
Route::post('/product-interaction', [TestAPI::class, 'handleProductInteraction']);

// GDPR Compliance Routes
Route::prefix('gdpr')->group(function () {
    Route::get('/export/{sessionId}', [App\Http\Controllers\TestAPI::class, 'exportUserData'])->name('gdpr.export');
    Route::post('/delete/{sessionId}', [App\Http\Controllers\TestAPI::class, 'deleteUserData'])->name('gdpr.delete');
    Route::post('/anonymize/{sessionId}', [App\Http\Controllers\TestAPI::class, 'anonymizeUserData'])->name('gdpr.anonymize');
    Route::get('/retention-summary', [App\Http\Controllers\TestAPI::class, 'getDataRetentionSummary'])->name('gdpr.retention');
});
