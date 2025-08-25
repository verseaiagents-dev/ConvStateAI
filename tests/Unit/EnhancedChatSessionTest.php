<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\EnhancedChatSession;
use App\Models\ProductInteraction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EnhancedChatSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_create_a_chat_session()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'test-session-123',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100,
            'intent_history' => ['product_search', 'category_browse'],
            'chat_history' => ['Hello', 'I need help'],
            'user_preferences' => ['category' => 'electronics'],
            'product_interactions' => []
        ]);

        $this->assertDatabaseHas('enhanced_chat_sessions', [
            'session_id' => 'test-session-123',
            'status' => 'active'
        ]);

        $this->assertEquals(['product_search', 'category_browse'], $session->intent_history);
        $this->assertEquals(['Hello', 'I need help'], $session->chat_history);
        $this->assertEquals(['category' => 'electronics'], $session->user_preferences);
    }

    /** @test */
    public function it_can_check_if_session_is_active()
    {
        $activeSession = EnhancedChatSession::create([
            'session_id' => 'active-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        $expiredSession = EnhancedChatSession::create([
            'session_id' => 'expired-session',
            'status' => 'expired',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        $this->assertTrue($activeSession->isActive());
        $this->assertFalse($expiredSession->isActive());
    }

    /** @test */
    public function it_can_check_if_session_is_expired()
    {
        $expiredSession = EnhancedChatSession::create([
            'session_id' => 'expired-session',
            'status' => 'expired',
            'daily_view_count' => 0,
            'daily_view_limit' => 100,
            'expires_at' => Carbon::now()->subHour()
        ]);

        $this->assertTrue($expiredSession->isExpired());
    }

    /** @test */
    public function it_can_check_daily_view_limits()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'limit-test-session',
            'status' => 'active',
            'daily_view_count' => 50,
            'daily_view_limit' => 100
        ]);

        $this->assertTrue($session->canViewMore());

        $session->daily_view_count = 100;
        $this->assertFalse($session->canViewMore());
    }

    /** @test */
    public function it_can_increment_view_count()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'view-count-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        $session->incrementViewCount();
        $this->assertEquals(1, $session->fresh()->daily_view_count);

        $session->incrementViewCount();
        $this->assertEquals(2, $session->fresh()->daily_view_count);
    }

    /** @test */
    public function it_can_update_last_activity()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'activity-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        $oldActivity = $session->last_activity;
        
        $session->updateLastActivity();
        
        $this->assertNotEquals($oldActivity, $session->fresh()->last_activity);
        $this->assertNotNull($session->fresh()->last_activity);
    }

    /** @test */
    public function it_can_add_intents()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'intent-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100,
            'intent_history' => []
        ]);

        $session->addIntent('product_search');
        $session->addIntent('category_browse');

        $this->assertContains('product_search', array_column($session->fresh()->intent_history, 'intent'));
        $this->assertContains('category_browse', array_column($session->fresh()->intent_history, 'intent'));
        $this->assertCount(2, $session->fresh()->intent_history);
    }

    /** @test */
    public function it_can_add_chat_messages()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'chat-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100,
            'chat_history' => []
        ]);

        $session->addChatMessage('user', 'Hello');
        $session->addChatMessage('bot', 'How can I help you?');

        $this->assertContains('Hello', array_column($session->fresh()->chat_history, 'content'));
        $this->assertContains('How can I help you?', array_column($session->fresh()->chat_history, 'content'));
        $this->assertCount(2, $session->fresh()->chat_history);
    }

    /** @test */
    public function it_can_update_user_preferences()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'preferences-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100,
            'user_preferences' => ['category' => 'electronics']
        ]);

        $session->updateUserPreferences(['brand' => 'Apple', 'price_range' => 'high']);

        $this->assertEquals('electronics', $session->fresh()->user_preferences['category']);
        $this->assertEquals('Apple', $session->fresh()->user_preferences['brand']);
        $this->assertEquals('high', $session->fresh()->user_preferences['price_range']);
    }

    /** @test */
    public function it_can_add_product_interactions()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'interaction-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100,
            'product_interactions' => []
        ]);

        $session->addProductInteraction(1, 'view', ['source' => 'chat_widget']);

        $this->assertCount(1, $session->fresh()->product_interactions);
        $this->assertEquals(1, $session->fresh()->product_interactions[0]['product_id']);
        $this->assertEquals('view', $session->fresh()->product_interactions[0]['action']);
    }

    /** @test */
    public function it_can_refresh_daily_limits()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'limit-refresh-session',
            'status' => 'active',
            'daily_view_count' => 50,
            'daily_view_limit' => 100,
            'created_at' => Carbon::now()->subDay()
        ]);

        $session->refreshDailyLimits();

        $this->assertEquals(0, $session->fresh()->daily_view_count);
    }

    /** @test */
    public function it_can_expire_session()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'expire-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        $session->expire();

        $this->assertEquals('expired', $session->fresh()->status);
    }

    /** @test */
    public function it_can_complete_session()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'complete-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        $session->complete();

        $this->assertEquals('completed', $session->fresh()->status);
    }

    /** @test */
    public function it_has_relationships_with_user_and_interactions()
    {
        $user = User::factory()->create();
        
        $session = EnhancedChatSession::create([
            'session_id' => 'relationship-session',
            'user_id' => $user->id,
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        $interaction = ProductInteraction::create([
            'session_id' => $session->session_id,
            'action' => 'view',
            'timestamp' => Carbon::now(),
            'source' => 'chat_widget'
        ]);

        $this->assertInstanceOf(User::class, $session->user);
        $this->assertEquals($user->id, $session->user->id);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $session->productInteractions);
        $this->assertCount(1, $session->productInteractions);
        $this->assertEquals($interaction->id, $session->productInteractions->first()->id);
    }

    /** @test */
    public function it_can_scope_active_sessions()
    {
        EnhancedChatSession::create([
            'session_id' => 'active-1',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        EnhancedChatSession::create([
            'session_id' => 'active-2',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        EnhancedChatSession::create([
            'session_id' => 'expired-1',
            'status' => 'expired',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        $activeSessions = EnhancedChatSession::active()->get();

        $this->assertCount(2, $activeSessions);
        $this->assertTrue($activeSessions->every(fn($session) => $session->status === 'active'));
    }

    /** @test */
    public function it_can_scope_active_today_sessions()
    {
        EnhancedChatSession::create([
            'session_id' => 'today-1',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100,
            'last_activity' => Carbon::now()
        ]);

        EnhancedChatSession::create([
            'session_id' => 'yesterday-1',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100,
            'last_activity' => Carbon::now()->subDay()
        ]);

        $todaySessions = EnhancedChatSession::activeToday()->get();

        $this->assertCount(1, $todaySessions);
        $this->assertEquals('today-1', $todaySessions->first()->session_id);
    }
}
