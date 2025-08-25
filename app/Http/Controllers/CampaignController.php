<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    /**
     * Display admin dashboard for campaigns
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            // API request - return JSON
            try {
                $siteId = $request->get('site_id', 1); // Default site ID
                
                $campaigns = Campaign::where('site_id', $siteId)
                    ->active()
                    ->valid()
                    ->orderBy('start_date', 'desc')
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => $campaigns,
                    'message' => 'Kampanyalar başarıyla getirildi'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kampanyalar getirilirken hata oluştu: ' . $e->getMessage()
                ], 500);
            }
        }

        // Web request - return admin view
        $campaigns = Campaign::with('site')->orderBy('created_at', 'desc')->get();
        return view('dashboard.campaigns', compact('campaigns'));
    }

    /**
     * Store a newly created campaign
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category' => 'required|string|max:100',
                'discount' => 'required|string|max:255',
                'valid_until' => 'nullable|date',
                'is_active' => 'boolean',
                'site_id' => 'required|exists:sites,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'discount_type' => 'required|in:percentage,fixed,buy_x_get_y,free_shipping',
                'discount_value' => 'nullable|numeric|min:0',
                'minimum_order_amount' => 'nullable|numeric|min:0',
                'max_usage' => 'nullable|integer|min:1',
                'image_url' => 'nullable|url',
                'terms_conditions' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasyon hatası',
                    'errors' => $validator->errors()
                ], 422);
            }

            $campaign = Campaign::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $campaign,
                'message' => 'Kampanya başarıyla oluşturuldu'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kampanya oluşturulurken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified campaign
     */
    public function show($id): JsonResponse
    {
        try {
            $campaign = Campaign::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $campaign,
                'message' => 'Kampanya başarıyla getirildi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kampanya bulunamadı: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified campaign
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $campaign = Campaign::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'category' => 'sometimes|required|string|max:100',
                'discount' => 'sometimes|required|string|max:255',
                'valid_until' => 'nullable|date',
                'is_active' => 'sometimes|boolean',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'discount_type' => 'sometimes|required|in:percentage,fixed,buy_x_get_y,free_shipping',
                'discount_value' => 'nullable|numeric|min:0',
                'minimum_order_amount' => 'nullable|numeric|min:0',
                'max_usage' => 'nullable|integer|min:1',
                'image_url' => 'nullable|url',
                'terms_conditions' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasyon hatası',
                    'errors' => $validator->errors()
                ], 422);
            }

            $campaign->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $campaign,
                'message' => 'Kampanya başarıyla güncellendi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kampanya güncellenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified campaign
     */
    public function destroy($id): JsonResponse
    {
        try {
            $campaign = Campaign::findOrFail($id);
            $campaign->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kampanya başarıyla silindi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kampanya silinirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get campaigns by category
     */
    public function getByCategory(Request $request, $category): JsonResponse
    {
        try {
            $siteId = $request->get('site_id', 1);
            
            $campaigns = Campaign::where('site_id', $siteId)
                ->where('category', $category)
                ->active()
                ->valid()
                ->orderBy('start_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $campaigns,
                'message' => 'Kategori kampanyaları başarıyla getirildi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori kampanyaları getirilirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active campaigns count
     */
    public function getActiveCount(Request $request): JsonResponse
    {
        try {
            $siteId = $request->get('site_id', 1);
            
            $count = Campaign::where('site_id', $siteId)
                ->active()
                ->valid()
                ->count();

            return response()->json([
                'success' => true,
                'data' => ['count' => $count],
                'message' => 'Aktif kampanya sayısı getirildi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kampanya sayısı getirilirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
}
