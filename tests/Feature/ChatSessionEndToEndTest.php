<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\EnhancedChatSession;
use App\Models\ProductInteraction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;

class ChatSessionEndToEndTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Create test products
        $this->products = Product::factory()->count(5)->create();
    }

    /** @test */
    public function it_can_complete_full_chat_session_flow()
    {
        // 1. Start chat session
        $sessionId = $this->faker->uuid;
        
        $response = $this->post('/api/chat', [
            'message' => 'I need help finding electronics',
            'session_id' => $sessionId,
            'additional_data' => [
                'user_id' => $this->user->id,
                'source' => 'chat_widget'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'intent',
            'session_id',
            'data'
        ]);

        // 2. Verify session created
        $session = EnhancedChatSession::where('session_id', $sessionId)->first();
        $this->assertNotNull($session);
        $this->assertEquals('active', $session->status);

        // 3. Simulate product recommendation
        $product = $this->products->first();
        
        $response = $this->post('/api/product-interaction', [
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'action' => 'view',
            'timestamp' => now()->toISOString(),
            'source' => 'chat_widget',
            'metadata' => [
                'product_name' => $product->name,
                'product_category' => $product->category
            ]
        ]);

        $response->assertStatus(200);

        // 4. Verify product interaction tracked
        $interaction = ProductInteraction::where('session_id', $sessionId)
            ->where('product_id', $product->id)
            ->first();
        $this->assertNotNull($interaction);
        $this->assertEquals('view', $interaction->action);

        // 5. Simulate product page visit
        $productPageResponse = $this->get("/shop/product/{$product->id}?ref=chat&session={$sessionId}");
        $productPageResponse->assertStatus(200);

        // 6. Verify additional view tracking
        $viewInteractions = ProductInteraction::where('session_id', $sessionId)
            ->where('product_id', $product->id)
            ->get();
        $this->assertGreaterThan(1, $viewInteractions->count());

        // 7. Simulate add to cart
        $cartResponse = $this->post('/api/product-interaction', [
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'action' => 'add_to_cart',
            'timestamp' => now()->toISOString(),
            'source' => 'product_page',
            'metadata' => [
                'product_name' => $product->name,
                'action_source' => 'product_page'
            ]
        ]);

        $cartResponse->assertStatus(200);

        // 8. Verify cart interaction tracked
        $cartInteraction = ProductInteraction::where('session_id', $sessionId)
            ->where('action', 'add_to_cart')
            ->first();
        $this->assertNotNull($cartInteraction);

        // 9. Simulate purchase
        $purchaseResponse = $this->post('/api/product-interaction', [
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'action' => 'buy',
            'timestamp' => now()->toISOString(),
            'source' => 'checkout',
            'metadata' => [
                'product_name' => $product->name,
                'order_id' => 'TEST_ORDER_123',
                'amount' => $product->price
            ]
        ]);

        $purchaseResponse->assertStatus(200);

        // 10. Verify purchase tracked
        $purchaseInteraction = ProductInteraction::where('session_id', $sessionId)
            ->where('action', 'buy')
            ->first();
        $this->assertNotNull($purchaseInteraction);

        // 11. Check session analytics
        $analyticsResponse = $this->get("/api/chat-session/{$sessionId}/analytics");
        $analyticsResponse->assertStatus(200);

        $analytics = $analyticsResponse->json();
        $this->assertArrayHasKey('session_stats', $analytics);
        $this->assertArrayHasKey('intent_analysis', $analytics);
        $this->assertArrayHasKey('interaction_patterns', $analytics);

        // 12. Verify conversion tracking
        $this->assertGreaterThan(0, $analytics['session_stats']['conversion_rate']);
        $this->assertGreaterThan(0, $analytics['session_stats']['conversion_interactions']);
    }

    /** @test */
    public function it_can_handle_multiple_product_interactions_in_session()
    {
        $sessionId = $this->faker->uuid;
        
        // Create session
        $session = EnhancedChatSession::create([
            'session_id' => $sessionId,
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        // Simulate multiple product interactions
        $products = $this->products->take(3);
        
        foreach ($products as $product) {
            // View product
            $this->post('/api/product-interaction', [
                'session_id' => $sessionId,
                'product_id' => $product->id,
                'action' => 'view',
                'timestamp' => now()->toISOString(),
                'source' => 'chat_widget'
            ]);

            // Compare product
            $this->post('/api/product-interaction', [
                'session_id' => $sessionId,
                'product_id' => $product->id,
                'action' => 'compare',
                'timestamp' => now()->toISOString(),
                'source' => 'chat_widget'
            ]);
        }

        // Verify all interactions tracked
        $interactions = ProductInteraction::where('session_id', $sessionId)->get();
        $this->assertEquals(6, $interactions->count()); // 3 products × 2 actions

        // Verify session view count updated
        $session->refresh();
        $this->assertEquals(3, $session->daily_view_count);
    }

    /** @test */
    public function it_can_handle_session_with_daily_view_limits()
    {
        $sessionId = $this->faker->uuid;
        
        // Create session with low limit
        $session = EnhancedChatSession::create([
            'session_id' => $sessionId,
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 2
        ]);

        // First two views should work
        for ($i = 0; $i < 2; $i++) {
            $response = $this->post('/api/product-interaction', [
                'session_id' => $sessionId,
                'product_id' => $this->products[$i]->id,
                'action' => 'view',
                'timestamp' => now()->toISOString(),
                'source' => 'chat_widget'
            ]);
            
            $response->assertStatus(200);
        }

        // Third view should fail due to limit
        $response = $this->post('/api/product-interaction', [
            'session_id' => $sessionId,
            'product_id' => $this->products[2]->id,
            'action' => 'view',
            'timestamp' => now()->toISOString(),
            'source' => 'chat_widget'
        ]);

        $response->assertStatus(429); // Too Many Requests
        $response->assertJson(['error' => 'Daily view limit reached']);

        // Verify session view count
        $session->refresh();
        $this->assertEquals(2, $session->daily_view_count);
    }

    /** @test */
    public function it_can_track_user_preferences_from_interactions()
    {
        $sessionId = $this->faker->uuid;
        
        // Create session
        $session = EnhancedChatSession::create([
            'session_id' => $sessionId,
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        // Simulate product interactions in electronics category
        $electronicsProducts = $this->products->where('category', 'electronics')->take(3);
        
        foreach ($electronicsProducts as $product) {
            $this->post('/api/product-interaction', [
                'session_id' => $sessionId,
                'product_id' => $product->id,
                'action' => 'view',
                'timestamp' => now()->toISOString(),
                'source' => 'chat_widget'
            ]);
        }

        // Verify user preferences updated
        $session->refresh();
        $this->assertArrayHasKey('category', $session->user_preferences);
        $this->assertContains('electronics', $session->user_preferences['category']);
    }

    /** @test */
    public function it_can_handle_session_expiration_and_cleanup()
    {
        $sessionId = $this->faker->uuid;
        
        // Create expired session
        $session = EnhancedChatSession::create([
            'session_id' => $sessionId,
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100,
            'expires_at' => now()->subHour() // Expired 1 hour ago
        ]);

        // Try to interact with expired session
        $response = $this->post('/api/product-interaction', [
            'session_id' => $sessionId,
            'product_id' => $this->products->first()->id,
            'action' => 'view',
            'timestamp' => now()->toISOString(),
            'source' => 'chat_widget'
        ]);

        // Should create new session or handle gracefully
        $response->assertStatus(200);

        // Verify session status updated
        $session->refresh();
        $this->assertEquals('expired', $session->status);
    }

    /** @test */
    public function it_can_handle_concurrent_sessions_for_same_user()
    {
        $userId = $this->user->id;
        
        // Create multiple sessions for same user
        $sessionIds = [];
        for ($i = 0; $i < 3; $i++) {
            $sessionId = $this->faker->uuid;
            $sessionIds[] = $sessionId;
            
            EnhancedChatSession::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'status' => 'active',
                'daily_view_count' => 0,
                'daily_view_limit' => 100
            ]);
        }

        // Simulate interactions across all sessions
        foreach ($sessionIds as $index => $sessionId) {
            $product = $this->products[$index];
            
            $this->post('/api/product-interaction', [
                'session_id' => $sessionId,
                'product_id' => $product->id,
                'action' => 'view',
                'timestamp' => now()->toISOString(),
                'source' => 'chat_widget'
            ]);
        }

        // Verify all sessions have interactions
        foreach ($sessionIds as $sessionId) {
            $interactions = ProductInteraction::where('session_id', $sessionId)->get();
            $this->assertGreaterThan(0, $interactions->count());
        }

        // Verify user has multiple active sessions
        $userSessions = EnhancedChatSession::where('user_id', $userId)
            ->where('status', 'active')
            ->get();
        $this->assertEquals(3, $userSessions->count());
    }

    /** @test */
    public function it_can_export_session_data_for_analytics()
    {
        $sessionId = $this->faker->uuid;
        
        // Create session with interactions
        $session = EnhancedChatSession::create([
            'session_id' => $sessionId,
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        // Add some interactions
        $this->post('/api/product-interaction', [
            'session_id' => $sessionId,
            'product_id' => $this->products->first()->id,
            'action' => 'view',
            'timestamp' => now()->toISOString(),
            'source' => 'chat_widget'
        ]);

        // Test analytics export
        $response = $this->get("/api/chat-session/{$sessionId}/analytics");
        $response->assertStatus(200);

        $analytics = $response->json();
        
        // Verify analytics structure
        $this->assertArrayHasKey('session_stats', $analytics);
        $this->assertArrayHasKey('intent_analysis', $analytics);
        $this->assertArrayHasKey('interaction_patterns', $analytics);
        $this->assertArrayHasKey('user_preferences', $analytics);

        // Verify data accuracy
        $this->assertEquals(1, $analytics['session_stats']['total_interactions']);
        $this->assertEquals(0, $analytics['session_stats']['conversion_rate']); // No purchases yet
    }

    /** @test */
    public function it_can_handle_large_number_of_interactions_performance()
    {
        $sessionId = $this->faker->uuid;
        
        // Create session
        $session = EnhancedChatSession::create([
            'session_id' => $sessionId,
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 1000
        ]);

        $startTime = microtime(true);
        
        // Create 100 interactions
        for ($i = 0; $i < 100; $i++) {
            $product = $this->products[$i % $this->products->count()];
            
            $this->post('/api/product-interaction', [
                'session_id' => $sessionId,
                'product_id' => $product->id,
                'action' => 'view',
                'timestamp' => now()->toISOString(),
                'source' => 'chat_widget'
            ]);
        }

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Performance should be reasonable (less than 10 seconds for 100 interactions)
        $this->assertLessThan(10000, $executionTime);

        // Verify all interactions created
        $interactions = ProductInteraction::where('session_id', $sessionId)->get();
        $this->assertEquals(100, $interactions->count());

        // Verify session view count updated
        $session->refresh();
        $this->assertEquals(100, $session->daily_view_count);
    }
}
