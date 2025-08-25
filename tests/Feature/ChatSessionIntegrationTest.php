<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\EnhancedChatSession;
use App\Models\ProductInteraction;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ChatSessionIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_create_and_track_chat_session()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);

        // Create a chat session
        $sessionData = [
            'session_id' => 'test-session-' . $this->faker->uuid,
            'user_id' => $user->id,
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ];

        $session = EnhancedChatSession::create($sessionData);

        $this->assertDatabaseHas('enhanced_chat_sessions', [
            'session_id' => $session->session_id,
            'user_id' => $user->id
        ]);

        // Track product interaction
        $product = Product::factory()->create();
        
        $interactionData = [
            'session_id' => $session->session_id,
            'product_id' => $product->id,
            'action' => 'view',
            'timestamp' => Carbon::now(),
            'source' => 'chat_widget',
            'metadata' => [
                'product_name' => $product->title ?? $product->name,
                'action_source' => 'chat_widget'
            ]
        ];

        $interaction = ProductInteraction::create($interactionData);

        $this->assertDatabaseHas('product_interactions', [
            'session_id' => $session->session_id,
            'product_id' => $product->id,
            'action' => 'view'
        ]);

        // Verify relationships
        $this->assertInstanceOf(Product::class, $interaction->product);
        $this->assertInstanceOf(EnhancedChatSession::class, $interaction->chatSession);
        $this->assertInstanceOf(User::class, $session->user);
    }

    /** @test */
    public function it_can_track_multiple_product_interactions()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'multi-interaction-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        $products = Product::factory()->count(3)->create();

        $actions = ['view', 'add_to_cart', 'buy'];

        foreach ($products as $index => $product) {
            ProductInteraction::create([
                'session_id' => $session->session_id,
                'product_id' => $product->id,
                'action' => $actions[$index],
                'timestamp' => Carbon::now()->addMinutes($index),
                'source' => 'chat_widget'
            ]);
        }

        $this->assertCount(3, $session->productInteractions);
        $this->assertCount(3, ProductInteraction::where('session_id', $session->session_id)->get());
    }

    /** @test */
    public function it_can_handle_session_lifecycle()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'lifecycle-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        // Add intents
        $session->addIntent('product_search');
        $session->addIntent('category_browse');

        // Add some chat messages
        $session->addChatMessage('user', 'I need help with electronics');
        $session->addChatMessage('bot', 'I can help you find electronics');
        $session->addChatMessage('user', 'Show me smartphones');

        // Update user preferences
        $session->updateUserPreferences([
            'category' => 'electronics',
            'brand' => 'Samsung',
            'price_range' => 'medium',
            'color' => 'black'
        ]);

        // Increment view count
        $session->incrementViewCount();
        $session->incrementViewCount();

        // Verify all changes
        $freshSession = $session->fresh();
        
        $this->assertCount(2, $freshSession->intent_history); // product_search + category_browse
        $this->assertCount(3, $freshSession->chat_history); // 3 messages added
        $this->assertEquals('electronics', $freshSession->user_preferences['category']);
        $this->assertEquals('Samsung', $freshSession->user_preferences['brand']);
        $this->assertEquals(2, $freshSession->daily_view_count);

        // Complete session
        $session->complete();
        $this->assertEquals('completed', $session->fresh()->status);
    }

    /** @test */
    public function it_can_handle_daily_view_limits()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'limit-session',
            'status' => 'active',
            'daily_view_count' => 95,
            'daily_view_limit' => 100
        ]);

        // Set daily limit to 2 and last activity to yesterday
        $session->update([
            'daily_view_limit' => 2,
            'last_activity' => now()->subDay()
        ]);

        // First two increments should work
        $session->incrementViewCount();
        $session->incrementViewCount();

        // Should not be able to view more
        $this->assertFalse($session->fresh()->canViewMore());

        // Refresh daily limits (this should reset the count)
        $session->refreshDailyLimits();
        $this->assertEquals(0, $session->fresh()->daily_view_count);
        $this->assertTrue($session->fresh()->canViewMore());
    }

    /** @test */
    public function it_can_track_session_analytics()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'analytics-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100,
            'created_at' => Carbon::now()->subHours(2)
        ]);

        // Simulate user activity over time
        $session->updateLastActivity();
        $session->addIntent('product_search');
        $session->addIntent('product_search'); // Duplicate intent
        $session->addIntent('category_browse');

        // Create some interactions
        $product = Product::factory()->create();
        
        ProductInteraction::create([
            'session_id' => $session->session_id,
            'product_id' => $product->id,
            'action' => 'view',
            'timestamp' => Carbon::now()->subHour(),
            'source' => 'chat_widget'
        ]);

        ProductInteraction::create([
            'session_id' => $session->session_id,
            'product_id' => $product->id,
            'action' => 'add_to_cart',
            'timestamp' => Carbon::now()->subMinutes(30),
            'source' => 'chat_widget'
        ]);

        // Verify analytics data
        $freshSession = $session->fresh();
        
        $this->assertCount(3, $freshSession->intent_history); // 3 intents added
        $this->assertCount(2, $freshSession->productInteractions);
        $this->assertNotNull($freshSession->last_activity);
        
        // Verify session is active
        $this->assertEquals('active', $freshSession->status);
        
        // Verify session has expected data
        $this->assertGreaterThan(0, count($freshSession->intent_history));
        $this->assertGreaterThan(0, count($freshSession->productInteractions));
    }

    /** @test */
    public function it_can_handle_session_expiration()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'expiration-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100,
            'expires_at' => Carbon::now()->subHour()
        ]);

        // Check if expired
        $this->assertTrue($session->isExpired());

        // Expire session
        $session->expire();
        $this->assertEquals('expired', $session->fresh()->status);

        // Should not be able to view more
        $this->assertFalse($session->fresh()->canViewMore());
    }

    /** @test */
    public function it_can_handle_user_preferences_updates()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'preferences-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100,
            'user_preferences' => ['category' => 'electronics']
        ]);

        // Update preferences multiple times
        $session->updateUserPreferences(['brand' => 'Samsung']);
        $session->updateUserPreferences(['price_range' => 'medium']);
        $session->updateUserPreferences(['color' => 'black']);

        $freshSession = $session->fresh();
        
        // All preferences should be preserved
        $this->assertEquals('electronics', $freshSession->user_preferences['category']);
        $this->assertEquals('Samsung', $freshSession->user_preferences['brand']);
        $this->assertEquals('medium', $freshSession->user_preferences['price_range']);
        $this->assertEquals('black', $freshSession->user_preferences['color']);
    }

    /** @test */
    public function it_can_handle_bulk_operations()
    {
        // Create multiple sessions
        $sessions = collect();
        for ($i = 0; $i < 5; $i++) {
            $sessions->push(EnhancedChatSession::create([
                'session_id' => 'bulk-session-' . $i,
                'status' => 'active',
                'daily_view_count' => 0,
                'daily_view_limit' => 100
            ]));
        }

        // Bulk update status
        $sessions->each(function ($session) {
            $session->update(['status' => 'completed']);
        });

        // Verify all sessions are completed
        $this->assertCount(5, EnhancedChatSession::where('status', 'completed')->get());

        // Bulk refresh daily limits
        $sessions->each(function ($session) {
            $session->refreshDailyLimits();
        });

        // Verify all sessions have reset daily view count
        $this->assertCount(5, EnhancedChatSession::where('daily_view_count', 0)->get());
    }

    /** @test */
    public function it_can_handle_concurrent_updates()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'concurrent-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        // Simulate concurrent updates
        $session1 = EnhancedChatSession::find($session->id);
        $session2 = EnhancedChatSession::find($session->id);

        $session1->incrementViewCount();
        $session2->incrementViewCount();

        $session1->save();
        $session2->save();

        // Final count should be 2
        $this->assertEquals(2, $session->fresh()->daily_view_count);
    }

    /** @test */
    public function it_can_handle_large_data_sets()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'large-data-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        // Add many intents
        for ($i = 0; $i < 100; $i++) {
            $session->addIntent('intent_' . $i);
        }

        // Add many chat messages
        for ($i = 0; $i < 100; $i++) {
            $session->addChatMessage('user', 'message_' . $i);
        }

        // Add many product interactions
        $products = Product::factory()->count(50)->create();
        
        foreach ($products as $product) {
            ProductInteraction::create([
                'session_id' => $session->session_id,
                'product_id' => $product->id,
                'action' => 'view',
                'timestamp' => Carbon::now(),
                'source' => 'chat_widget'
            ]);
        }

        $freshSession = $session->fresh();
        
        $this->assertCount(100, $freshSession->intent_history);
        $this->assertCount(100, $freshSession->chat_history);
        $this->assertCount(50, $freshSession->productInteractions);
    }
}
