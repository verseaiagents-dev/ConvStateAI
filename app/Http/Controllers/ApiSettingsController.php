<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\AIFieldMappingService;

class ApiSettingsController extends Controller
{
    /**
     * Show API settings page
     */
    public function index()
    {
        $user = Auth::user();
        return view('dashboard.api-settings', compact('user'));
    }

    /**
     * Get API statistics
     */
    public function getStats()
    {
        try {
            // Cart statistics
            $cartStats = [
                'total_carts' => \App\Models\Commerce\Cart::count(),
                'active_carts' => \App\Models\Commerce\Cart::where('updated_at', '>', now()->subDays(7))->count(),
                'abandoned_carts' => \App\Models\Commerce\Cart::where('updated_at', '<', now()->subDays(7))->count(),
                'total_items' => \App\Models\Commerce\CartItem::sum('quantity'),
                'average_cart_value' => \App\Models\Commerce\Cart::with('items')->get()->avg(function($cart) {
                    return $cart->items->sum(function($item) {
                        return $item->price * $item->quantity;
                    });
                }) ?? 0
            ];

            // Order statistics
            $orderStats = [
                'total_orders' => \App\Models\Commerce\Order::count(),
                'pending_orders' => \App\Models\Commerce\Order::where('status', 'pending')->count(),
                'completed_orders' => \App\Models\Commerce\Order::where('status', 'completed')->count(),
                'cancelled_orders' => \App\Models\Commerce\Order::where('status', 'cancelled')->count(),
                'total_revenue' => \App\Models\Commerce\Order::where('status', 'completed')->sum('total_amount'),
                'average_order_value' => \App\Models\Commerce\Order::where('status', 'completed')->avg('total_amount') ?? 0
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'cart' => $cartStats,
                    'orders' => $orderStats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'İstatistikler yüklenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test order status API endpoint
     */
    public function testOrderStatusApi(Request $request)
    {
        $request->validate([
            'api_url' => 'required|url'
        ]);

        try {
            $response = Http::timeout(10)->get($request->api_url);
            
            $result = [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response' => $response->json() ?: $response->body(),
                'headers' => $response->headers(),
                'response_time' => $response->handlerStats()['total_time'] ?? null
            ];

            // Cache successful API configuration
            if ($response->successful()) {
                Cache::put('order_status_api_url', $request->api_url, now()->addDays(30));
                Cache::put('order_status_api_active', true, now()->addDays(30));
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status_code' => 0,
                'error' => $e->getMessage(),
                'response' => null
            ], 500);
        }
    }

    /**
     * Test cargo tracking API endpoint
     */
    public function testCargoTrackingApi(Request $request)
    {
        $request->validate([
            'api_url' => 'required|url'
        ]);

        try {
            $response = Http::timeout(10)->get($request->api_url);
            
            $result = [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response' => $response->json() ?: $response->body(),
                'headers' => $response->headers(),
                'response_time' => $response->handlerStats()['total_time'] ?? null
            ];

            // Cache successful API configuration
            if ($response->successful()) {
                Cache::put('cargo_tracking_api_url', $request->api_url, now()->addDays(30));
                Cache::put('cargo_tracking_api_active', true, now()->addDays(30));
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status_code' => 0,
                'error' => $e->getMessage(),
                'response' => null
            ], 500);
        }
    }

    /**
     * Get API configuration status
     */
    public function getApiConfig()
    {
        return response()->json([
            'order_status_api' => [
                'url' => Cache::get('order_status_api_url'),
                'active' => Cache::get('order_status_api_active', false)
            ],
            'cargo_tracking_api' => [
                'url' => Cache::get('cargo_tracking_api_url'),
                'active' => Cache::get('cargo_tracking_api_active', false)
            ]
        ]);
    }

    /**
     * Save API configuration
     */
    public function saveApiConfig(Request $request)
    {
        $request->validate([
            'order_status_api_url' => 'nullable|url',
            'cargo_tracking_api_url' => 'nullable|url'
        ]);

        try {
            if ($request->order_status_api_url) {
                Cache::put('order_status_api_url', $request->order_status_api_url, now()->addDays(30));
            }
            
            if ($request->cargo_tracking_api_url) {
                Cache::put('cargo_tracking_api_url', $request->cargo_tracking_api_url, now()->addDays(30));
            }

            return response()->json([
                'success' => true,
                'message' => 'API konfigürasyonu kaydedildi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'API konfigürasyonu kaydedilemedi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AI Field Mapping yap
     */
    public function performFieldMapping(Request $request)
    {
        $request->validate([
            'api_url' => 'required|url',
            'api_type' => 'required|in:order_status,cargo_tracking'
        ]);

        try {
            $aiService = new AIFieldMappingService();
            $result = $aiService->performFieldMapping($request->api_url, $request->api_type);
            
            if ($result['success']) {
                // Field mapping kalitesini değerlendir
                $quality = $aiService->evaluateMappingQuality($result['mapping']);
                $result['quality_evaluation'] = $quality;
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Field mapping sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Field mapping sonuçlarını getir
     */
    public function getFieldMapping(Request $request)
    {
        $request->validate([
            'api_url' => 'required|url',
            'api_type' => 'required|in:order_status,cargo_tracking'
        ]);

        try {
            $aiService = new AIFieldMappingService();
            $mapping = $aiService->getFieldMapping($request->api_type, $request->api_url);
            
            if ($mapping) {
                $quality = $aiService->evaluateMappingQuality($mapping);
                $mapping['quality_evaluation'] = $quality;
            }

            return response()->json([
                'success' => true,
                'mapping' => $mapping
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Field mapping bilgileri alınamadı: ' . $e->getMessage()
            ], 500);
        }
    }
}
