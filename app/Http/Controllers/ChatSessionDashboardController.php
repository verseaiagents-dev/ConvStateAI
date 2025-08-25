<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EnhancedChatSession;
use App\Models\ProductInteraction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChatSessionDashboardController extends Controller
{
    /**
     * Chat sessions listesi ve stats
     */
    public function index()
    {
        try {
            // Stats hesapla
            $stats = $this->calculateDashboardStats();
            
            // Sessions listesi (paginated)
            $sessions = EnhancedChatSession::with(['user', 'productInteractions'])
                ->orderBy('last_activity', 'desc')
                ->paginate(20);

            return view('dashboard.chat-sessions', compact('sessions', 'stats'));
            
        } catch (\Exception $e) {
            \Log::error('Chat session dashboard error: ' . $e->getMessage());
            return back()->with('error', 'Dashboard yüklenirken hata oluştu');
        }
    }

    /**
     * Session detayı
     */
    public function show(string $sessionId)
    {
        try {
            $session = EnhancedChatSession::where('session_id', $sessionId)
                ->with(['user', 'productInteractions.product'])
                ->first();

            if (!$session) {
                return back()->with('error', 'Session bulunamadı');
            }

            // Session analytics
            $analytics = $this->getSessionAnalytics($session);
            
            // Product interactions timeline
            $interactions = $session->productInteractions()
                ->with(['product.category'])
                ->orderBy('timestamp', 'desc')
                ->get()
                ->map(function($interaction) {
                    return [
                        'id' => $interaction->id,
                        'action' => $interaction->action,
                        'timestamp' => $interaction->timestamp,
                        'duration_seconds' => $interaction->duration_seconds ?? 0,
                        'product' => $interaction->product ? [
                            'id' => $interaction->product->id,
                            'name' => $interaction->product->name,
                            'image' => $interaction->product->image,
                            'category' => $interaction->product->category ? [
                                'id' => $interaction->product->category->id,
                                'name' => $interaction->product->category->name
                            ] : null
                        ] : null
                    ];
                })
                ->toArray();

            return view('dashboard.chat-session-detail', compact('session', 'analytics', 'interactions'));
            
        } catch (\Exception $e) {
            \Log::error('Chat session detail error: ' . $e->getMessage());
            return back()->with('error', 'Session detayı yüklenirken hata oluştu');
        }
    }

    /**
     * Dashboard stats hesapla
     */
    private function calculateDashboardStats(): array
    {
        $now = Carbon::now();
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        // Total sessions
        $totalSessions = EnhancedChatSession::count();
        
        // Active sessions today
        $activeToday = EnhancedChatSession::whereDate('last_activity', $today)
            ->where('status', 'active')
            ->count();
        
        // Total interactions today
        $totalInteractionsToday = ProductInteraction::whereDate('timestamp', $today)->count();
        
        // Conversion rate today
        $conversionsToday = ProductInteraction::whereDate('timestamp', $today)
            ->whereIn('action', ['buy', 'add_to_cart'])
            ->count();
        
        $conversionRateToday = $totalInteractionsToday > 0 
            ? round(($conversionsToday / $totalInteractionsToday) * 100, 2) 
            : 0;

        // Weekly stats
        $weeklySessions = EnhancedChatSession::where('created_at', '>=', $thisWeek)->count();
        $weeklyInteractions = ProductInteraction::where('timestamp', '>=', $thisWeek)->count();
        
        // Monthly stats
        $monthlySessions = EnhancedChatSession::where('created_at', '>=', $thisMonth)->count();
        $monthlyInteractions = ProductInteraction::where('timestamp', '>=', $thisMonth)->count();

        // Intent distribution
        $intentStats = $this->getIntentDistribution();
        
        // Action distribution
        $actionStats = $this->getActionDistribution();

        return [
            'overview' => [
                'total_sessions' => $totalSessions,
                'active_today' => $activeToday,
                'total_interactions_today' => $totalInteractionsToday,
                'conversion_rate_today' => $conversionRateToday
            ],
            'trends' => [
                'weekly_sessions' => $weeklySessions,
                'weekly_interactions' => $weeklyInteractions,
                'monthly_sessions' => $monthlySessions,
                'monthly_interactions' => $monthlyInteractions
            ],
            'intent_distribution' => $intentStats,
            'action_distribution' => $actionStats
        ];
    }

    /**
     * Intent distribution hesapla
     */
    private function getIntentDistribution(): array
    {
        $sessions = EnhancedChatSession::whereNotNull('intent_history')->get();
        $intentCounts = [];
        
        foreach ($sessions as $session) {
            if (is_array($session->intent_history)) {
                foreach ($session->intent_history as $intent) {
                    $intentName = $intent['intent'] ?? 'unknown';
                    if (!isset($intentCounts[$intentName])) {
                        $intentCounts[$intentName] = 0;
                    }
                    $intentCounts[$intentName]++;
                }
            }
        }
        
        arsort($intentCounts);
        return array_slice($intentCounts, 0, 10); // Top 10 intents
    }

    /**
     * Action distribution hesapla
     */
    private function getActionDistribution(): array
    {
        return ProductInteraction::select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->pluck('count', 'action')
            ->toArray();
    }

    /**
     * Session analytics getir
     */
    private function getSessionAnalytics(EnhancedChatSession $session): array
    {
        $interactions = $session->productInteractions;
        
        // Intent analysis
        $intentAnalysis = [
            'total_intents' => count($session->intent_history ?? []),
            'intent_distribution' => $this->getIntentCounts($session->intent_history ?? []),
            'most_common_intent' => $this->getMostCommonIntent($session->intent_history ?? [])
        ];
        
        // Interaction patterns
        $interactionPatterns = [
            'total_interactions' => $interactions->count(),
            'action_distribution' => $interactions->groupBy('action')->map->count(),
            'conversion_rate' => $this->calculateConversionRate($interactions),
            'hourly_activity' => $this->getHourlyActivity($interactions)
        ];
        
        // Session duration
        $sessionDuration = $session->created_at->diffInMinutes($session->last_activity ?? $session->created_at);
        
        return [
            'intent_analysis' => $intentAnalysis,
            'interaction_patterns' => $interactionPatterns,
            'session_duration_minutes' => $sessionDuration,
            'daily_view_usage' => round(($session->daily_view_count / $session->daily_view_limit) * 100, 2),
            'session_stats' => [
                'intent_count' => $intentAnalysis['total_intents'],
                'product_views' => $interactionPatterns['action_distribution']['view'] ?? 0,
                'cart_additions' => $interactionPatterns['action_distribution']['add_to_cart'] ?? 0,
                'conversion_rate' => $interactionPatterns['conversion_rate'],
                'total_interactions' => $interactionPatterns['total_interactions'],
                'session_duration_minutes' => $sessionDuration
            ]
        ];
    }

    /**
     * Intent counts hesapla
     */
    private function getIntentCounts(array $intentHistory): array
    {
        $counts = [];
        foreach ($intentHistory as $intent) {
            $intentName = $intent['intent'] ?? 'unknown';
            if (!isset($counts[$intentName])) {
                $counts[$intentName] = 0;
            }
            $counts[$intentName]++;
        }
        return $counts;
    }

    /**
     * Most common intent bul
     */
    private function getMostCommonIntent(array $intentHistory): ?string
    {
        if (empty($intentHistory)) {
            return null;
        }
        
        $counts = $this->getIntentCounts($intentHistory);
        arsort($counts);
        return array_key_first($counts);
    }

    /**
     * Conversion rate hesapla
     */
    private function calculateConversionRate($interactions): float
    {
        if ($interactions->isEmpty()) {
            return 0;
        }
        
        $conversionActions = $interactions->whereIn('action', ['buy', 'add_to_cart'])->count();
        return round(($conversionActions / $interactions->count()) * 100, 2);
    }

    /**
     * Hourly activity hesapla
     */
    private function getHourlyActivity($interactions): array
    {
        $hourly = array_fill(0, 24, 0);
        
        foreach ($interactions as $interaction) {
            $hour = $interaction->timestamp->hour;
            $hourly[$hour]++;
        }
        
        return $hourly;
    }

    /**
     * Sessions export (CSV)
     */
    public function export(Request $request)
    {
        try {
            $sessions = EnhancedChatSession::with(['user', 'productInteractions'])
                ->orderBy('created_at', 'desc')
                ->get();

            $filename = 'chat_sessions_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($sessions) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, [
                    'Session ID', 'User ID', 'Status', 'Created At', 'Last Activity',
                    'Daily View Count', 'Daily View Limit', 'Total Interactions',
                    'Most Common Intent', 'Conversion Rate'
                ]);

                foreach ($sessions as $session) {
                    $interactions = $session->productInteractions;
                    $conversionRate = $this->calculateConversionRate($interactions);
                    $mostCommonIntent = $this->getMostCommonIntent($session->intent_history ?? []);
                    
                    fputcsv($file, [
                        $session->session_id,
                        $session->user_id ?? 'Guest',
                        $session->status,
                        $session->created_at->format('Y-m-d H:i:s'),
                        $session->last_activity ? $session->last_activity->format('Y-m-d H:i:s') : 'N/A',
                        $session->daily_view_count,
                        $session->daily_view_limit,
                        $interactions->count(),
                        $mostCommonIntent ?? 'N/A',
                        $conversionRate . '%'
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            \Log::error('Chat sessions export error: ' . $e->getMessage());
            return back()->with('error', 'Export işlemi başarısız');
        }
    }
}
