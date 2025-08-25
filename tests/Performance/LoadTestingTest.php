<?php

namespace Tests\Performance;

use Tests\TestCase;
use App\Models\EnhancedChatSession;
use App\Models\ProductInteraction;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class LoadTestingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable database logging for performance tests
        DB::disableQueryLog();
    }

    /** @test */
    public function it_can_handle_multiple_concurrent_sessions()
    {
        $startTime = microtime(true);
        
        // Create 100 concurrent sessions
        $sessions = collect();
        for ($i = 0; $i < 100; $i++) {
            $sessions->push(EnhancedChatSession::create([
                'session_id' => 'concurrent-session-' . $i,
                'status' => 'active',
                'daily_view_count' => 0,
                'daily_view_limit' => 100
            ]));
        }

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->assertCount(100, $sessions);
        $this->assertLessThan(5000, $executionTime); // Should complete in less than 5 seconds
        
        echo "Created 100 concurrent sessions in {$executionTime}ms\n";
    }

    /** @test */
    public function it_can_handle_rapid_session_updates()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'rapid-update-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        $startTime = microtime(true);
        
        // Perform 1000 rapid updates
        for ($i = 0; $i < 1000; $i++) {
            $session->incrementViewCount();
            $session->addIntent('intent_' . $i);
            $session->updateLastActivity();
        }

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertEquals(1000, $session->fresh()->daily_view_count);
        $this->assertLessThan(10000, $executionTime); // Should complete in less than 10 seconds
        
        echo "Performed 1000 rapid updates in {$executionTime}ms\n";
    }

    /** @test */
    public function it_can_handle_bulk_product_interactions()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'bulk-interaction-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        $products = Product::factory()->count(100)->create();

        $startTime = microtime(true);
        
        // Create 1000 product interactions
        $interactions = [];
        for ($i = 0; $i < 1000; $i++) {
            $interactions[] = [
                'session_id' => $session->session_id,
                'product_id' => $products->random()->id,
                'action' => ['view', 'add_to_cart', 'buy'][array_rand(['view', 'add_to_cart', 'buy'])],
                'timestamp' => Carbon::now()->addMinutes($i),
                'source' => 'chat_widget'
            ];
        }

        // Bulk insert
        ProductInteraction::insert($interactions);

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertCount(1000, ProductInteraction::where('session_id', $session->session_id)->get());
        $this->assertLessThan(5000, $executionTime); // Should complete in less than 5 seconds
        
        echo "Created 1000 product interactions in {$executionTime}ms\n";
    }

    /** @test */
    public function it_can_handle_database_query_optimization()
    {
        // Create test data
        $sessions = EnhancedChatSession::factory()->count(500)->create();
        
        foreach ($sessions as $session) {
            ProductInteraction::factory()->count(rand(1, 10))->create([
                'session_id' => $session->session_id
            ]);
        }

        // Test query performance with eager loading
        $startTime = microtime(true);
        
        $sessionsWithInteractions = EnhancedChatSession::with(['productInteractions'])
            ->where('status', 'active')
            ->get();

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(1000, $executionTime); // Should complete in less than 1 second
        
        echo "Eager loaded 500 sessions with interactions in {$executionTime}ms\n";

        // Test query performance without eager loading (N+1 problem)
        $startTime = microtime(true);
        
        $sessionsWithoutEager = EnhancedChatSession::where('status', 'active')->get();
        foreach ($sessionsWithoutEager as $session) {
            $session->productInteractions; // This will cause N+1 queries
        }

        $endTime = microtime(true);
        $executionTimeWithoutEager = ($endTime - $startTime) * 1000;

        echo "Without eager loading (N+1): {$executionTimeWithoutEager}ms\n";
        
        // Eager loading should be significantly faster
        $this->assertLessThan($executionTimeWithoutEager, $executionTime);
    }

    /** @test */
    public function it_can_handle_memory_usage_monitoring()
    {
        $initialMemory = memory_get_usage(true);
        
        // Create large dataset
        $sessions = collect();
        for ($i = 0; $i < 1000; $i++) {
            $sessions->push(EnhancedChatSession::create([
                'session_id' => 'memory-test-session-' . $i,
                'status' => 'active',
                'daily_view_count' => 0,
                'daily_view_limit' => 100,
                'intent_history' => array_fill(0, 100, 'test_intent'),
                'chat_history' => array_fill(0, 100, 'test_message'),
                'user_preferences' => array_fill(0, 50, 'test_preference')
            ]));
        }

        $peakMemory = memory_get_peak_usage(true);
        $finalMemory = memory_get_usage(true);
        
        $memoryIncrease = $finalMemory - $initialMemory;
        $peakMemoryUsage = $peakMemory - $initialMemory;

        echo "Initial memory: " . $this->formatBytes($initialMemory) . "\n";
        echo "Final memory: " . $this->formatBytes($finalMemory) . "\n";
        echo "Memory increase: " . $this->formatBytes($memoryIncrease) . "\n";
        echo "Peak memory usage: " . $this->formatBytes($peakMemoryUsage) . "\n";

        // Memory usage should be reasonable (less than 100MB for 1000 sessions)
        $this->assertLessThan(100 * 1024 * 1024, $memoryIncrease);
        
        // Clean up to free memory
        $sessions->each->delete();
        unset($sessions);
        
        // Force garbage collection
        gc_collect_cycles();
        
        $cleanupMemory = memory_get_usage(true);
        echo "After cleanup: " . $this->formatBytes($cleanupMemory) . "\n";
    }

    /** @test */
    public function it_can_handle_response_time_benchmarking()
    {
        // Test API response times
        $startTime = microtime(true);
        
        $response = $this->get('/api/chat-session/test-session/analytics');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        echo "API response time: {$responseTime}ms\n";
        
        // Response should be fast (less than 500ms for simple requests)
        $this->assertLessThan(500, $responseTime);
    }

    /** @test */
    public function it_can_handle_concurrent_api_requests()
    {
        $session = EnhancedChatSession::create([
            'session_id' => 'concurrent-api-session',
            'status' => 'active',
            'daily_view_count' => 0,
            'daily_view_limit' => 100
        ]);

        $startTime = microtime(true);
        
        // Simulate 50 concurrent API requests
        $responses = [];
        for ($i = 0; $i < 50; $i++) {
            $responses[] = $this->post('/api/product-interaction', [
                'session_id' => $session->session_id,
                'product_id' => 1,
                'action' => 'view',
                'timestamp' => Carbon::now()->toISOString(),
                'source' => 'chat_widget'
            ]);
        }

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        echo "Handled 50 concurrent API requests in {$executionTime}ms\n";
        
        // Should handle concurrent requests efficiently
        $this->assertLessThan(10000, $executionTime);
        
        // All responses should be successful
        foreach ($responses as $response) {
            $this->assertTrue($response->isSuccessful() || $response->isRedirection());
        }
    }

    /** @test */
    public function it_can_handle_database_connection_pooling()
    {
        $startTime = microtime(true);
        
        // Test multiple database connections
        $connections = [];
        for ($i = 0; $i < 10; $i++) {
            $connections[] = DB::connection();
        }

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        echo "Created 10 database connections in {$executionTime}ms\n";
        
        // Connection creation should be fast
        $this->assertLessThan(100, $executionTime);
        
        // Test connection reuse
        $startTime = microtime(true);
        
        foreach ($connections as $connection) {
            $connection->select('SELECT 1');
        }

        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000;

        echo "Executed 10 simple queries in {$queryTime}ms\n";
        
        // Queries should be fast
        $this->assertLessThan(500, $queryTime);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    protected function tearDown(): void
    {
        // Clean up any remaining data
        EnhancedChatSession::truncate();
        ProductInteraction::truncate();
        Product::truncate();
        
        parent::tearDown();
    }
}
