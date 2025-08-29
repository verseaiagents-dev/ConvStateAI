<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FAQController extends Controller
{
    /**
     * Display admin dashboard for FAQs
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            // API request - return JSON
            try {
                $siteId = $request->get('site_id', 1); // Default site ID
                
                // Site'in var olup olmadığını kontrol et
                $site = Site::find($siteId);
                if (!$site) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Belirtilen site bulunamadı',
                        'data' => []
                    ], 404);
                }
                
                $faqs = FAQ::where('site_id', $siteId)
                    ->active()
                    ->ordered()
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => $faqs,
                    'message' => 'SSS başarıyla getirildi'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'SSS getirilirken hata oluştu: ' . $e->getMessage()
                ], 500);
            }
        }

        // Web request - return admin view
        $faqs = FAQ::with('site')->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc')->get();
        return view('dashboard.faqs', compact('faqs'));
    }

    /**
     * Store a newly created FAQ
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Debug: Log request data
            \Log::info('FAQ Store Request:', $request->all());
            
            $validator = Validator::make($request->all(), [
                'question' => 'required|string|max:255',
                'answer' => 'required|string',
                'short_answer' => 'required|string|max:255',
                'category_id' => 'nullable|integer',
                'product_id' => 'nullable|integer',
                'is_active' => 'boolean',
                'site_id' => 'required|exists:sites,id',
                'sort_order' => 'nullable|integer|min:0',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
                'keywords' => 'nullable|string',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'seo_url' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasyon hatası',
                    'errors' => $validator->errors()
                ], 422);
            }

            $faq = FAQ::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $faq,
                'message' => 'SSS başarıyla oluşturuldu'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'SSS oluşturulurken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified FAQ
     */
    public function show($id): JsonResponse
    {
        try {
            $faq = FAQ::findOrFail($id);
            
            // Increment view count
            $faq->incrementViewCount();

            return response()->json([
                'success' => true,
                'data' => $faq,
                'message' => 'SSS başarıyla getirildi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'SSS bulunamadı: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified FAQ
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $faq = FAQ::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'question' => 'sometimes|required|string|max:255',
                'answer' => 'sometimes|required|string',
                'short_answer' => 'sometimes|required|string|max:255',
                'category_id' => 'nullable|integer',
                'product_id' => 'nullable|integer',
                'is_active' => 'sometimes|boolean',
                'sort_order' => 'nullable|integer|min:0',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
                'keywords' => 'nullable|string',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'seo_url' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasyon hatası',
                    'errors' => $validator->errors()
                ], 422);
            }

            $faq->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $faq,
                'message' => 'SSS başarıyla güncellendi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'SSS güncellenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified FAQ
     */
    public function destroy($id): JsonResponse
    {
        try {
            $faq = FAQ::findOrFail($id);
            $faq->delete();

            return response()->json([
                'success' => true,
                'message' => 'SSS başarıyla silindi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'SSS silinirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get FAQs by category
     */
    public function getByCategory(Request $request, $category): JsonResponse
    {
        try {
            $siteId = $request->get('site_id', 1);
            
            $faqs = FAQ::where('site_id', $siteId)
                ->where('category', $category)
                ->active()
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $faqs,
                'message' => 'Kategori SSS\'leri başarıyla getirildi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori SSS\'leri getirilirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark FAQ as helpful
     */
    public function markAsHelpful($id): JsonResponse
    {
        try {
            $faq = FAQ::findOrFail($id);
            $faq->markAsHelpful();

            return response()->json([
                'success' => true,
                'data' => ['helpful_count' => $faq->helpful_count],
                'message' => 'SSS faydalı olarak işaretlendi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'İşlem sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark FAQ as not helpful
     */
    public function markAsNotHelpful($id): JsonResponse
    {
        try {
            $faq = FAQ::findOrFail($id);
            $faq->markAsNotHelpful();

            return response()->json([
                'success' => true,
                'data' => ['not_helpful_count' => $faq->not_helpful_count],
                'message' => 'SSS faydalı değil olarak işaretlendi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'İşlem sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search FAQs
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $siteId = $request->get('site_id', 1);
            $query = $request->get('q', '');
            
            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arama sorgusu gerekli'
                ], 400);
            }

            $faqs = FAQ::where('site_id', $siteId)
                ->where('is_active', true)
                ->where(function($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhere('answer', 'like', "%{$query}%")
                      ->orWhere('category', 'like', "%{$query}%");
                })
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $faqs,
                'message' => 'Arama sonuçları getirildi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Arama sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get popular FAQs
     */
    public function getPopular(Request $request): JsonResponse
    {
        try {
            $siteId = $request->get('site_id', 1);
            $limit = $request->get('limit', 10);
            
            $faqs = FAQ::where('site_id', $siteId)
                ->active()
                ->popular()
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $faqs,
                'message' => 'Popüler SSS\'ler getirildi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Popüler SSS\'ler getirilirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
}
