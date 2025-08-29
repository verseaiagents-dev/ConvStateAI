<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KnowledgeBase\AIService;
use App\Services\KnowledgeBase\ContentChunker;
use App\Models\KnowledgeBase;
use App\Models\KnowledgeChunk;
use App\Models\Product;
use App\Models\EnhancedChatSession;
use App\Models\ProductInteraction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TestAPI extends Controller
{
    private $aiService;
    private $contentChunker;

    public function __construct(AIService $aiService, ContentChunker $contentChunker)
    {
        $this->aiService = $aiService;
        $this->contentChunker = $contentChunker;
    }

    public function chat(Request $request)
    {
        try {
            // Kullanıcıdan gelen message parametresini al
            $userMessage = $request->input('message');
            $sessionId = $request->input('session_id');
            
            // Message parametresi kontrolü
            if (!$userMessage) {
                return response()->json(['error' => 'Message parameter is required'], 400);
            }
            
            // Debug log
            Log::info('Chat request received:', [
                'message' => $userMessage,
                'session_id' => $sessionId
            ]);
            
            // 1. Intent detection yap
            $intentResult = $this->aiService->detectIntent($userMessage);
            $intent = $intentResult['intent'];
            $confidence = $intentResult['confidence'];
            
            // Debug log
            Log::info('Intent detection result:', [
                'intent' => $intent,
                'confidence' => $confidence,
                'userMessage' => $userMessage
            ]);
            
            // 2. Knowledge base'de semantic search yap
            $searchResults = $this->performSemanticSearch($userMessage);
            
            // 3. Intent'e göre response oluştur
            $response = $this->generateAIResponse($intent, $userMessage, $searchResults);
            
            // Debug log
            Log::info('Generated response:', [
                'type' => $response['type'],
                'message' => $response['message'],
                'products_count' => count($response['data']['products'] ?? [])
            ]);
            
            // 4. Session bilgilerini ekle
            $response['session_id'] = $sessionId ?? uniqid();
            $response['intent'] = $intent;
            $response['confidence'] = $confidence;
            $response['search_results'] = $searchResults;
            
            // 5. AI interaction'ı logla
            $this->logAIInteraction($userMessage, $intent, $response, $sessionId);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Chat işlenirken hata oluştu',
                'message' => 'Üzgünüm, şu anda yanıt veremiyorum. Lütfen daha sonra tekrar deneyin.',
                'type' => 'error'
            ], 500);
        }
    }

    /**
     * Semantic search yapar
     */
    private function performSemanticSearch(string $query): array
    {
        try {
            if (config('app.debug')) {
                Log::info('Starting performSemanticSearch for query:', ['query' => $query]);
            }
            
            // Knowledge base'den chunk'ları al
            $chunks = KnowledgeChunk::with('knowledgeBase')
                ->where('content_type', 'product')
                ->get()
                ->toArray();
            
            if (config('app.debug')) {
                Log::info('Found chunks:', ['count' => count($chunks)]);
            }
            
            if (empty($chunks)) {
                if (config('app.debug')) {
                    Log::warning('No chunks found for content_type: product');
                }
                return [
                    'query' => $query,
                    'results' => [],
                    'total_found' => 0,
                    'search_type' => 'no_data'
                ];
            }
            
            // AIService ile semantic search yap
            $searchResults = $this->aiService->semanticSearch($query, $chunks);
            
            // ContentChunker ile de fuzzy search yap
            $fuzzyResults = $this->contentChunker->fuzzyChunkSearch($chunks, $query, 0.6);
            
            // Sonuçları birleştir ve deduplicate yap
            $combinedResults = $this->combineSearchResults($searchResults, $fuzzyResults);
            
            if (config('app.debug')) {
                Log::info('Combined results:', $combinedResults);
            }
            
            return $combinedResults;
            
        } catch (\Exception $e) {
            Log::error('Semantic search error: ' . $e->getMessage());
            return [
                'query' => $query,
                'results' => [],
                'total_found' => 0,
                'search_type' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Search sonuçlarını birleştirir
     */
    private function combineSearchResults(array $semanticResults, array $fuzzyResults): array
    {
        $combined = [];
        $seenIds = [];
        
        // Semantic search sonuçlarını ekle
        foreach ($semanticResults['results'] ?? [] as $result) {
            $chunkId = $result['id'] ?? $result['chunk_index'];
            if (!in_array($chunkId, $seenIds)) {
                $combined[] = $result;
                $seenIds[] = $chunkId;
            }
        }
        
        // Fuzzy search sonuçlarını ekle
        foreach ($fuzzyResults as $result) {
            $chunkId = $result['id'] ?? $result['chunk_index'];
            if (!in_array($chunkId, $seenIds)) {
                $combined[] = $result;
                $seenIds[] = $chunkId;
            }
        }
        
        // Relevance score'a göre sırala
        usort($combined, function($a, $b) {
            $scoreA = $a['relevance_score'] ?? $a['fuzzy_score'] ?? 0;
            $scoreB = $b['relevance_score'] ?? $b['fuzzy_score'] ?? 0;
            return $scoreB <=> $scoreA;
        });
        
        return [
            'query' => $semanticResults['query'] ?? 'unknown',
            'results' => $combined,
            'total_found' => count($combined),
            'search_type' => 'combined',
            'semantic_count' => count($semanticResults['results'] ?? []),
            'fuzzy_count' => count($fuzzyResults)
        ];
    }

    /**
     * Intent'e göre AI response oluşturur
     */
    private function generateAIResponse(string $intent, string $userMessage, array $searchResults): array
    {
        // Debug log
        Log::info('generateAIResponse called:', [
            'intent' => $intent,
            'userMessage' => $userMessage,
            'hasSearchResults' => !empty($searchResults['results'])
        ]);
        
        switch ($intent) {
            case 'product_search':
            case 'product_inquiry':
            case 'oyuncak önerisi': // Oyuncak arama için
            case 'oyuncak_önerisi': // Underscore ile de
                Log::info('Calling generateProductSearchResponse');
                return $this->generateProductSearchResponse($userMessage, $searchResults);
                
            case 'product_recommendation': // Özel case
                Log::info('Calling generateProductRecommendationResponse');
                return $this->generateProductRecommendationResponse($userMessage, $searchResults);
                
            case 'category_browse':
                Log::info('Calling generateCategoryResponse');
                return $this->generateCategoryResponse($userMessage, $searchResults);
                
            case 'brand_search':
                Log::info('Calling generateBrandResponse');
                return $this->generateBrandResponse($userMessage, $searchResults);
                
            case 'faq_search':
                Log::info('Calling generateFAQResponse');
                return $this->generateFAQResponse($userMessage, $searchResults);
                
            case 'order_tracking':
                Log::info('Calling generateOrderTrackingResponse');
                return $this->generateOrderTrackingResponse();
                
            case 'greeting':
                Log::info('Calling generateGreetingResponse');
                return $this->generateGreetingResponse();
                
            case 'help_request':
                Log::info('Calling generateHelpResponse');
                return $this->generateHelpResponse();
                
            default:
                Log::info('Calling generateGeneralResponse (default case)');
                return $this->generateGeneralResponse($userMessage, $searchResults);
        }
    }

    /**
     * Ürün arama response'u
     */
    private function generateProductSearchResponse(string $userMessage, array $searchResults): array
    {
        $products = [];
        $message = '';
        
        // Kullanıcı mesajını analiz et
        $isPersonalizedRequest = preg_match('/(bana göre|benim için|öner|tavsiye)/i', $userMessage);
        $hasSpecificProduct = preg_match('/(saat|telefon|bilgisayar|elbise|ayakkabı|çanta|aksesuar|kozmetik|kitap|oyuncak)/i', $userMessage);
        
        // "bana oyuncak öner" gibi spesifik ürün aramaları için arama yap
        $isSpecificProductRequest = preg_match('/(bana|benim için)\s+(oyuncak|kitap|saat|telefon|bilgisayar|elbise|ayakkabı|çanta|aksesuar|kozmetik)\s+(öner|tavsiye|bul|ara)/i', $userMessage);
        
        // Elektronik ürünleri listele gibi kategori aramaları için
        $categorySearch = preg_match('/(elektronik|giyim|oyuncak|kitap|kozmetik|aksesuar|mobilya|bahçe|müzik|film)\s+(ürünleri?|listele|göster|ara)/i', $userMessage);
        
        if ($categorySearch) {
            // Kategori adını çıkar
            preg_match('/(elektronik|giyim|oyuncak|kitap|kozmetik|aksesuar|mobilya|bahçe|müzik|film)/i', $userMessage, $matches);
            $category = strtolower($matches[1]);
            
            // ProductData'dan kategoriye göre ürünleri al
            $productData = new \App\Http\Services\ProductData();
            
            // AI'ın kategori eşleştirmesi yapması için tüm ürünleri al ve AI'a gönder
            $allProducts = $productData->getAllProducts();
            
            if (!empty($allProducts)) {
                // AI'a kategori eşleştirmesi yaptır
                $aiResponse = $this->aiService->generateResponse(
                    "Sen bir e-ticaret ürün filtreleme uzmanısın. " .
                    "Kullanıcı '{$category}' kategorisinde ürün arıyor. " .
                    "Mevcut ürün kategorileri: " . implode(', ', array_unique(array_column($allProducts, 'category'))) . ". " .
                    "Bu kategorideki ürünleri filtrele ve JSON formatında döndür. " .
                    "Format: {\"products\": [{\"id\": 1, \"name\": \"Ürün Adı\", \"category\": \"Kategori\"}]}. " .
                    "Sadece JSON döndür, başka açıklama yapma.",
                    [], // chunks
                    ['context' => 'product_category_search', 'products' => $allProducts]
                );
                
                // AI response'dan ürünleri çıkar
                $filteredProducts = $this->extractProductsFromAIResponse($aiResponse, $allProducts, $category);
                
                if (!empty($filteredProducts)) {
                    $products = array_slice($filteredProducts, 0, 8); // En fazla 8 ürün göster
                    $message = ucfirst($category) . " kategorisinde " . count($products) . " ürün buldum:";
                } else {
                    // AI eşleştirme yapamadıysa, fuzzy search yap
                    $products = $this->fuzzyCategorySearch($allProducts, $category);
                    if (!empty($products)) {
                        $products = array_slice($products, 0, 8);
                        $message = ucfirst($category) . " kategorisinde " . count($products) . " ürün buldum:";
                    } else {
                        $message = ucfirst($category) . " kategorisinde ürün bulamadım. Farklı bir kategori deneyin.";
                    }
                }
            } else {
                $message = "Ürün veritabanında hiç ürün bulunamadı.";
            }
        } elseif (!empty($searchResults['results'])) {
            // Chunk'lardan ürün bilgilerini çıkar
            foreach (array_slice($searchResults['results'], 0, 5) as $result) {
                $product = $this->extractProductFromChunk($result);
                if ($product) {
                    $products[] = $product;
                }
            }
            
            if (!empty($products)) {
                if ($isPersonalizedRequest) {
                    $message = "Size özel olarak " . count($products) . " ürün öneriyorum:";
                } elseif ($hasSpecificProduct) {
                    $message = "Aradığınız ürünlerden " . count($products) . " tanesini buldum:";
                } else {
                    $message = "Aradığınız kriterlere uygun " . count($products) . " ürün buldum:";
                }
            } else {
                $message = "Aradığınız kriterlere uygun ürün bulamadım.";
            }
        } else {
            if ($isPersonalizedRequest) {
                $message = "Size özel ürün önerisi yapmak için daha fazla bilgiye ihtiyacım var. Hangi kategoride ürün arıyorsunuz?";
            } else {
                $message = "Aradığınız kriterlere uygun ürün bulunamadı. Farklı bir arama yapmayı deneyin.";
            }
        }
        
        // Intent'e göre type belirle
        if ($isSpecificProductRequest) {
            $responseType = 'product_search'; // Spesifik ürün araması
        } elseif ($isPersonalizedRequest) {
            $responseType = 'product_recommendation'; // Genel öneri
        } else {
            $responseType = 'product_search'; // Normal arama
        }
        
        return [
            'type' => $responseType,
            'message' => $message,
            'products' => $products, // products'ı doğrudan ekle
            'data' => [
                'products' => $products,
                'search_query' => $userMessage,
                'total_found' => count($products),
                'search_confidence' => count($products) > 0 ? 'high' : 'low',
                'is_personalized' => $isPersonalizedRequest
            ],
            'suggestions' => [
                'Farklı kelimelerle ara',
                'Kategori seç',
                'Fiyat aralığı belirle',
                'Marka seç',
                'Size özel öneriler için profil bilgilerinizi güncelleyin'
            ]
        ];
    }

    /**
     * Ürün önerisi response'u - ÖZEL
     */
    private function generateProductRecommendationResponse(string $userMessage, array $searchResults): array
    {
        $products = [];
        $message = '';
        
        // Kullanıcı mesajını analiz et
        $isPersonalizedRequest = true; // product_recommendation için her zaman true
        $hasSpecificProduct = preg_match('/(saat|telefon|bilgisayar|elbise|ayakkabı|çanta|aksesuar|kozmetik|kitap|oyuncak|kazak|gömlek|pantolon|etek|ceket)/i', $userMessage);
        
        // "bana ürün öner" gibi genel öneri istekleri için rastgele ürünler öner
        if (preg_match('/(ürün öner|ürün tavsiye|ne önerirsin|bana öner|bana tavsiye)/i', $userMessage)) {
            $message = "Size rastgele ürün önerileri yapayım:";
            
            // ProductData'dan rastgele ürünler seç
            $productData = new \App\Http\Services\ProductData();
            $allProducts = $productData->getAllProducts();
            
            if (!empty($allProducts)) {
                // Rastgele 6 ürün seç
                shuffle($allProducts);
                $products = array_slice($allProducts, 0, 6);
            }
        } else {
            // Spesifik ürün önerisi için
            $message = "Size özel ürün önerileri yapayım:";
            
            // ProductData'dan kategoriye göre ürünler seç
            $productData = new \App\Http\Services\ProductData();
            if ($hasSpecificProduct) {
                // Spesifik ürün kategorisinden seç
                preg_match('/(saat|telefon|bilgisayar|elbise|ayakkabı|çanta|aksesuar|kozmetik|kitap|oyuncak)/i', $userMessage, $matches);
                $category = $matches[1];
                
                // AI'ın kategori eşleştirmesi yapması için tüm ürünleri al
                $allProducts = $productData->getAllProducts();
                
                if (!empty($allProducts)) {
                    // AI'a kategori eşleştirmesi yaptır
                    $aiResponse = $this->aiService->generateResponse(
                        "Sen bir e-ticaret ürün filtreleme uzmanısın. " .
                        "Kullanıcı '{$category}' kategorisinde ürün arıyor. " .
                        "Mevcut ürün kategorileri: " . implode(', ', array_unique(array_column($allProducts, 'category'))) . ". " .
                        "Bu kategorideki ürünleri filtrele ve JSON formatında döndür. " .
                        "Format: {\"products\": [{\"id\": 1, \"name\": \"Ürün Adı\", \"category\": \"Kategori\"}]}. " .
                        "Sadece JSON döndür, başka açıklama yapma.",
                        [], // chunks
                        ['context' => 'product_category_search', 'products' => $allProducts]
                    );
                    
                    // AI response'dan ürünleri çıkar
                    $filteredProducts = $this->extractProductsFromAIResponse($aiResponse, $allProducts, $category);
                    
                    if (!empty($filteredProducts)) {
                        $products = array_slice($filteredProducts, 0, 6); // En fazla 6 ürün
                    } else {
                        // AI eşleştirme yapamadıysa, fuzzy search yap
                        $products = $this->fuzzyCategorySearch($allProducts, $category);
                        $products = array_slice($products, 0, 6);
                    }
                }
            }
        }
        
        return [
            'type' => 'product_recommendation',
            'message' => $message,
            'products' => $products, // products'ı doğrudan ekle
            'data' => [
                'products' => $products,
                'total_found' => count($products),
                'is_personalized' => $isPersonalizedRequest
            ],
            'suggestions' => [
                'Farklı kategori seç',
                'Fiyat aralığı belirle',
                'Marka seç',
                'Daha fazla ürün göster'
            ]
        ];
    }
    

    
    /**
     * Rastgele ürünler ekler
     */
    private function addRandomProducts(array &$products): void
    {
        $allChunks = KnowledgeChunk::with('knowledgeBase')
            ->where('content_type', 'product')
            ->get()
            ->toArray();
        
        if (!empty($allChunks)) {
            shuffle($allChunks);
            foreach (array_slice($allChunks, 0, 6) as $chunk) {
                $product = $this->extractProductFromChunk($chunk);
                if ($product && count($products) < 6) {
                    $products[] = $product;
                }
            }
        }
    }
    
    /**
     * Mesajdan renk bilgisini çıkarır
     */
    private function extractColorFromMessage(string $message): ?string
    {
        $colors = [
            'kırmızı' => 'kırmızı',
            'mavi' => 'mavi',
            'yeşil' => 'yeşil',
            'sarı' => 'sarı',
            'siyah' => 'siyah',
            'beyaz' => 'beyaz',
            'pembe' => 'pembe',
            'mor' => 'mor',
            'turuncu' => 'turuncu',
            'gri' => 'gri',
            'kahverengi' => 'kahverengi'
        ];
        
        foreach ($colors as $color => $value) {
            if (stripos($message, $color) !== false) {
                return $value;
            }
        }
        
        return null;
    }
    
    /**
     * Ürünün belirtilen renge uyup uymadığını kontrol eder
     */
    private function productMatchesColor(array $product, string $searchColor): bool
    {
        $productName = strtolower($product['name']);
        $productDescription = strtolower($product['description'] ?? '');
        
        // Ürün adında veya açıklamasında renk geçiyor mu?
        if (stripos($productName, $searchColor) !== false || stripos($productDescription, $searchColor) !== false) {
            return true;
        }
        
        // Renk eşleştirmeleri
        $colorMatches = [
            'kırmızı' => ['red', 'crimson', 'scarlet'],
            'mavi' => ['blue', 'navy', 'azure'],
            'yeşil' => ['green', 'emerald', 'forest'],
            'sarı' => ['yellow', 'gold', 'amber'],
            'siyah' => ['black', 'dark', 'ebony'],
            'beyaz' => ['white', 'ivory', 'cream'],
            'pembe' => ['pink', 'rose', 'fuchsia'],
            'mor' => ['purple', 'violet', 'lavender'],
            'turuncu' => ['orange', 'tangerine', 'coral'],
            'gri' => ['gray', 'grey', 'silver'],
            'kahverengi' => ['brown', 'chocolate', 'tan']
        ];
        
        if (isset($colorMatches[$searchColor])) {
            foreach ($colorMatches[$searchColor] as $englishColor) {
                if (stripos($productName, $englishColor) !== false || stripos($productDescription, $englishColor) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Mesajdan kategori bilgisini çıkarır
     */
    private function extractCategoryFromMessage(string $message): string
    {
        $categories = [
            'elektronik' => ['elektronik', 'electronics', 'electronic', 'tech', 'technology', 'monitör', 'monitor', 'ekran', 'screen', 'tv', 'televizyon', 'bilgisayar', 'computer', 'pc', 'laptop', 'desktop', 'tablet', 'telefon', 'phone', 'mobile', 'smartphone', 'saat', 'watch', 'kamera', 'camera', 'kulaklık', 'headphone', 'hoparlör', 'speaker', 'klavye', 'keyboard', 'mouse', 'fare', 'yazıcı', 'printer', 'scanner', 'tarayıcı', 'qled', 'oled', 'led', '4k', '8k', 'hd', 'fullhd', 'ultrahd', 'gaming', 'oyun', 'game'],
            'giyim' => ['giyim', 'clothing', 'clothes', 'fashion', 'apparel', 'wear', 'elbise', 'dress', 'gömlek', 'shirt', 'pantolon', 'pants', 'trousers', 'etek', 'skirt', 'ceket', 'jacket', 'kazak', 'sweater', 'bluz', 'blouse', 'ayakkabı', 'shoe', 'çanta', 'bag', 'şapka', 'hat', 'kemer', 'belt', 'çorap', 'sock', 'iç çamaşır', 'underwear', 'mayo', 'swimsuit', 'spor', 'sport', 'fitness', 'athletic'],
            'oyuncak' => ['oyuncak', 'toy', 'toys', 'game', 'games', 'gaming', 'play', 'puzzle', 'yapboz', 'lego', 'bebek', 'doll', 'arabalar', 'cars', 'robot', 'robot', 'eğitici', 'educational', 'yapı', 'construction', 'sanat', 'art', 'craft', 'boyama', 'coloring', 'müzik', 'music', 'enstrüman', 'instrument'],
            'kitap' => ['kitap', 'book', 'books', 'literature', 'reading', 'roman', 'novel', 'hikaye', 'story', 'şiir', 'poetry', 'dergi', 'magazine', 'gazete', 'newspaper', 'ansiklopedi', 'encyclopedia', 'sözlük', 'dictionary', 'atlas', 'atlas', 'çizgi roman', 'comic', 'manga', 'manga'],
            'saat' => ['saat', 'watch', 'watches', 'clock', 'timepiece', 'kol saati', 'wristwatch', 'duvar saati', 'wall clock', 'masa saati', 'desk clock', 'çalar saat', 'alarm clock', 'akıllı saat', 'smartwatch', 'dijital', 'digital', 'analog', 'analog'],
            'telefon' => ['telefon', 'phone', 'mobile', 'smartphone', 'cell', 'cep telefonu', 'mobile phone', 'akıllı telefon', 'smartphone', 'iphone', 'iphone', 'samsung', 'samsung', 'huawei', 'huawei', 'xiaomi', 'xiaomi', 'oppo', 'oppo', 'vivo', 'vivo'],
            'bilgisayar' => ['bilgisayar', 'computer', 'pc', 'laptop', 'desktop', 'notebook', 'netbook', 'ultrabook', 'macbook', 'macbook', 'imac', 'imac', 'mac', 'mac', 'windows', 'windows', 'linux', 'linux', 'macos', 'macos', 'işlemci', 'processor', 'cpu', 'cpu', 'ram', 'ram', 'ssd', 'ssd', 'hdd', 'hdd', 'ekran kartı', 'graphics card', 'gpu', 'gpu'],
            'ev' => ['ev', 'home', 'house', 'dekorasyon', 'decoration', 'interior', 'mobilya', 'furniture', 'halı', 'carpet', 'perde', 'curtain', 'lamba', 'lamp', 'mum', 'candle', 'vazo', 'vase', 'resim', 'picture', 'tablo', 'painting', 'çerçeve', 'frame', 'yastık', 'pillow', 'battaniye', 'blanket'],
            'spor' => ['spor', 'sport', 'fitness', 'exercise', 'athletic', 'koşu', 'running', 'yürüyüş', 'walking', 'bisiklet', 'bicycle', 'yoga', 'yoga', 'pilates', 'pilates', 'ağırlık', 'weight', 'dumbbell', 'dumbbell', 'halter', 'barbell', 'top', 'ball', 'raket', 'racket', 'kayak', 'ski'],
            'kozmetik' => ['kozmetik', 'cosmetic', 'beauty', 'makeup', 'skincare', 'makyaj', 'makeup', 'parfüm', 'perfume', 'krem', 'cream', 'losyon', 'lotion', 'şampuan', 'shampoo', 'saç', 'hair', 'cilt', 'skin', 'tırnak', 'nail', 'dudak', 'lip', 'göz', 'eye'],
            'aksesuar' => ['aksesuar', 'accessory', 'accessories', 'jewelry', 'takı', 'jewelry', 'kolye', 'necklace', 'yüzük', 'ring', 'küpe', 'earring', 'bilezik', 'bracelet', 'saat', 'watch', 'çanta', 'bag', 'cüzdan', 'wallet', 'güneş gözlüğü', 'sunglasses', 'şal', 'scarf', 'fular', 'scarf'],
            'mobilya' => ['mobilya', 'furniture', 'furnishings', 'furnishing', 'koltuk', 'sofa', 'sandalye', 'chair', 'masa', 'table', 'dolap', 'cabinet', 'wardrobe', 'wardrobe', 'yatak', 'bed', 'komodin', 'nightstand', 'vitrin', 'display case', 'raf', 'shelf', 'çekmece', 'drawer'],
            'bahçe' => ['bahçe', 'garden', 'outdoor', 'yard', 'patio', 'çiçek', 'flower', 'bitki', 'plant', 'ağaç', 'tree', 'çim', 'grass', 'çit', 'fence', 'havuz', 'pool', 'şömine', 'fireplace', 'barbekü', 'barbecue', 'hamak', 'hammock', 'salıncak', 'swing'],
            'müzik' => ['müzik', 'music', 'audio', 'sound', 'musical', 'gitar', 'guitar', 'piyano', 'piano', 'keman', 'violin', 'flüt', 'flute', 'davul', 'drum', 'bateri', 'drum set', 'mikrofon', 'microphone', 'hoparlör', 'speaker', 'kulaklık', 'headphone', 'cd', 'cd', 'vinyl', 'vinyl'],
            'film' => ['film', 'movie', 'cinema', 'video', 'dvd', 'dvd', 'bluray', 'bluray', '4k', '4k', 'uhd', 'uhd', 'projeksiyon', 'projection', 'perde', 'screen', 'kamera', 'camera', 'video kamera', 'video camera', 'drone', 'drone']
        ];
        
        $messageLower = strtolower($message);
        
        // Önce tam eşleşme ara
        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($messageLower, $keyword) !== false) {
                    return $category;
                }
            }
        }
        
        // Eğer hiçbir kategori bulunamazsa, mesaj içeriğine göre tahmin et
        if (preg_match('/(monitör|monitor|ekran|screen|tv|televizyon|bilgisayar|computer|pc|laptop|desktop|tablet|telefon|phone|mobile|smartphone|saat|watch|kamera|camera|kulaklık|headphone|hoparlör|speaker|klavye|keyboard|mouse|fare|yazıcı|printer|scanner|tarayıcı|qled|oled|led|4k|8k|hd|fullhd|ultrahd|gaming|oyun|game)/i', $messageLower)) {
            return 'elektronik';
        }
        
        if (preg_match('/(elbise|dress|gömlek|shirt|pantolon|pants|trousers|etek|skirt|ceket|jacket|kazak|sweater|bluz|blouse|ayakkabı|shoe|çanta|bag|şapka|hat|kemer|belt|çorap|sock|iç çamaşır|underwear|mayo|swimsuit)/i', $messageLower)) {
            return 'giyim';
        }
        
        if (preg_match('/(oyuncak|toy|toys|game|games|gaming|play|puzzle|yapboz|lego|bebek|doll|arabalar|cars|robot|robot|eğitici|educational|yapı|construction|sanat|art|craft|boyama|coloring)/i', $messageLower)) {
            return 'oyuncak';
        }
        
        if (preg_match('/(kitap|book|books|literature|reading|roman|novel|hikaye|story|şiir|poetry|dergi|magazine|gazete|newspaper|ansiklopedi|encyclopedia|sözlük|dictionary|atlas|atlas|çizgi roman|comic|manga|manga)/i', $messageLower)) {
            return 'kitap';
        }
        
        if (preg_match('/(saat|watch|watches|clock|timepiece|kol saati|wristwatch|duvar saati|wall clock|masa saati|desk clock|çalar saat|alarm clock|akıllı saat|smartwatch)/i', $messageLower)) {
            return 'saat';
        }
        
        if (preg_match('/(telefon|phone|mobile|smartphone|cell|cep telefonu|mobile phone|akıllı telefon|smartphone|iphone|iphone|samsung|samsung|huawei|huawei|xiaomi|xiaomi|oppo|oppo|vivo|vivo)/i', $messageLower)) {
            return 'telefon';
        }
        
        if (preg_match('/(bilgisayar|computer|pc|laptop|desktop|notebook|netbook|ultrabook|macbook|macbook|imac|imac|mac|mac|windows|windows|linux|linux|macos|macos|işlemci|processor|cpu|cpu|ram|ram|ssd|ssd|hdd|hdd|ekran kartı|graphics card|gpu|gpu)/i', $messageLower)) {
            return 'bilgisayar';
        }
        
        if (preg_match('/(ev|home|house|dekorasyon|decoration|interior|mobilya|furniture|halı|carpet|perde|curtain|lamba|lamp|mum|candle|vazo|vase|resim|picture|tablo|painting|çerçeve|frame|yastık|pillow|battaniye|blanket)/i', $messageLower)) {
            return 'ev';
        }
        
        if (preg_match('/(spor|sport|fitness|exercise|athletic|koşu|running|yürüyüş|walking|bisiklet|bicycle|yoga|yoga|pilates|pilates|ağırlık|weight|dumbbell|dumbbell|halter|barbell|top|ball|raket|racket|kayak|ski)/i', $messageLower)) {
            return 'spor';
        }
        
        if (preg_match('/(kozmetik|cosmetic|beauty|makeup|skincare|makyaj|makeup|parfüm|perfume|krem|cream|losyon|lotion|şampuan|shampoo|saç|hair|cilt|skin|tırnak|nail|dudak|lip|göz|eye)/i', $messageLower)) {
            return 'kozmetik';
        }
        
        if (preg_match('/(aksesuar|accessory|accessories|jewelry|takı|jewelry|kolye|necklace|yüzük|ring|küpe|earring|bilezik|bracelet|saat|watch|çanta|bag|cüzdan|wallet|güneş gözlüğü|sunglasses|şal|scarf|fular|scarf)/i', $messageLower)) {
            return 'aksesuar';
        }
        
        if (preg_match('/(mobilya|furniture|furnishings|furnishing|koltuk|sofa|sandalye|chair|masa|table|dolap|cabinet|wardrobe|wardrobe|yatak|bed|komodin|nightstand|vitrin|display case|raf|shelf|çekmece|drawer)/i', $messageLower)) {
            return 'mobilya';
        }
        
        if (preg_match('/(bahçe|garden|outdoor|yard|patio|çiçek|flower|bitki|plant|ağaç|tree|çim|grass|çit|fence|havuz|pool|şömine|fireplace|barbekü|barbecue|hamak|hammock|salıncak|swing)/i', $messageLower)) {
            return 'bahçe';
        }
        
        if (preg_match('/(müzik|music|audio|sound|musical|gitar|guitar|piyano|piano|keman|violin|flüt|flute|davul|drum|bateri|drum set|mikrofon|microphone|hoparlör|speaker|kulaklık|headphone|cd|cd|vinyl|vinyl)/i', $messageLower)) {
            return 'müzik';
        }
        
        if (preg_match('/(film|movie|cinema|video|dvd|dvd|bluray|bluray|4k|4k|uhd|uhd|projeksiyon|projection|perde|screen|kamera|camera|video kamera|video camera|drone|drone)/i', $messageLower)) {
            return 'film';
        }
        
        // Hiçbir kategori bulunamazsa genel kategori döndür
        return 'genel';
    }
    
    /**
     * Ürünün belirtilen kategoriye uyup uymadığını kontrol eder
     */
    private function productMatchesCategory(array $product, string $searchCategory): bool
    {
        // Kategori belirtilmemişse veya genel ise tüm ürünleri göster
        if (empty($searchCategory) || $searchCategory === 'genel') {
            return true;
        }
        
        $productCategory = strtolower($product['category'] ?? '');
        $productName = strtolower($product['name'] ?? '');
        $productDescription = strtolower($product['description'] ?? '');
        
        // Kategori eşleştirmeleri - daha kapsamlı
        $categoryMatches = [
            'elektronik' => ['electronics', 'electronic', 'tech', 'technology', 'gadget', 'monitör', 'monitor', 'ekran', 'screen', 'tv', 'televizyon', 'bilgisayar', 'computer', 'pc', 'laptop', 'desktop', 'tablet', 'telefon', 'phone', 'mobile', 'smartphone', 'saat', 'watch', 'kamera', 'camera', 'kulaklık', 'headphone', 'hoparlör', 'speaker', 'klavye', 'keyboard', 'mouse', 'fare', 'yazıcı', 'printer', 'scanner', 'tarayıcı', 'qled', 'oled', 'led', '4k', '8k', 'hd', 'fullhd', 'ultrahd', 'gaming', 'oyun', 'game'],
            'giyim' => ['clothing', 'clothes', 'fashion', 'apparel', 'wear', 'elbise', 'dress', 'gömlek', 'shirt', 'pantolon', 'pants', 'trousers', 'etek', 'skirt', 'ceket', 'jacket', 'kazak', 'sweater', 'bluz', 'blouse', 'ayakkabı', 'shoe', 'çanta', 'bag', 'şapka', 'hat', 'kemer', 'belt', 'çorap', 'sock', 'iç çamaşır', 'underwear', 'mayo', 'swimsuit', 'spor', 'sport', 'fitness', 'athletic'],
            'oyuncak' => ['toy', 'toys', 'game', 'games', 'gaming', 'play', 'puzzle', 'yapboz', 'lego', 'bebek', 'doll', 'arabalar', 'cars', 'robot', 'robot', 'eğitici', 'educational', 'yapı', 'construction', 'sanat', 'art', 'craft', 'boyama', 'coloring', 'müzik', 'music', 'enstrüman', 'instrument'],
            'kitap' => ['book', 'books', 'literature', 'reading', 'roman', 'novel', 'hikaye', 'story', 'şiir', 'poetry', 'dergi', 'magazine', 'gazete', 'newspaper', 'ansiklopedi', 'encyclopedia', 'sözlük', 'dictionary', 'atlas', 'atlas', 'çizgi roman', 'comic', 'manga', 'manga'],
            'saat' => ['watch', 'watches', 'clock', 'timepiece', 'kol saati', 'wristwatch', 'duvar saati', 'wall clock', 'masa saati', 'desk clock', 'çalar saat', 'alarm clock', 'akıllı saat', 'smartwatch', 'dijital', 'digital', 'analog', 'analog'],
            'telefon' => ['phone', 'mobile', 'smartphone', 'cell', 'cep telefonu', 'mobile phone', 'akıllı telefon', 'smartphone', 'iphone', 'iphone', 'samsung', 'samsung', 'huawei', 'huawei', 'xiaomi', 'xiaomi', 'oppo', 'oppo', 'vivo', 'vivo'],
            'bilgisayar' => ['computer', 'pc', 'laptop', 'desktop', 'notebook', 'netbook', 'ultrabook', 'macbook', 'macbook', 'imac', 'imac', 'mac', 'mac', 'windows', 'windows', 'linux', 'linux', 'macos', 'macos', 'işlemci', 'processor', 'cpu', 'cpu', 'ram', 'ram', 'ssd', 'ssd', 'hdd', 'hdd', 'ekran kartı', 'graphics card', 'gpu', 'gpu'],
            'ev' => ['home', 'house', 'dekorasyon', 'decoration', 'interior', 'mobilya', 'furniture', 'halı', 'carpet', 'perde', 'curtain', 'lamba', 'lamp', 'mum', 'candle', 'vazo', 'vase', 'resim', 'picture', 'tablo', 'painting', 'çerçeve', 'frame', 'yastık', 'pillow', 'battaniye', 'blanket'],
            'spor' => ['sport', 'fitness', 'exercise', 'athletic', 'koşu', 'running', 'yürüyüş', 'walking', 'bisiklet', 'bicycle', 'yoga', 'yoga', 'pilates', 'pilates', 'ağırlık', 'weight', 'dumbbell', 'dumbbell', 'halter', 'barbell', 'top', 'ball', 'raket', 'racket', 'kayak', 'ski'],
            'kozmetik' => ['cosmetic', 'beauty', 'makeup', 'skincare', 'makyaj', 'makeup', 'parfüm', 'perfume', 'krem', 'cream', 'losyon', 'lotion', 'şampuan', 'shampoo', 'saç', 'hair', 'cilt', 'skin', 'tırnak', 'nail', 'dudak', 'lip', 'göz', 'eye'],
            'aksesuar' => ['accessory', 'accessories', 'jewelry', 'takı', 'jewelry', 'kolye', 'necklace', 'yüzük', 'ring', 'küpe', 'earring', 'bilezik', 'bracelet', 'saat', 'watch', 'çanta', 'bag', 'cüzdan', 'wallet', 'güneş gözlüğü', 'sunglasses', 'şal', 'scarf', 'fular', 'scarf'],
            'mobilya' => ['furniture', 'furnishings', 'furnishing', 'koltuk', 'sofa', 'sandalye', 'chair', 'masa', 'table', 'dolap', 'cabinet', 'wardrobe', 'wardrobe', 'yatak', 'bed', 'komodin', 'nightstand', 'vitrin', 'display case', 'raf', 'shelf', 'çekmece', 'drawer'],
            'bahçe' => ['garden', 'outdoor', 'yard', 'patio', 'çiçek', 'flower', 'bitki', 'plant', 'ağaç', 'tree', 'çim', 'grass', 'çit', 'fence', 'havuz', 'pool', 'şömine', 'fireplace', 'barbekü', 'barbecue', 'hamak', 'hammock', 'salıncak', 'swing'],
            'müzik' => ['music', 'audio', 'sound', 'musical', 'gitar', 'guitar', 'piyano', 'piano', 'keman', 'violin', 'flüt', 'flute', 'davul', 'drum', 'bateri', 'drum set', 'mikrofon', 'microphone', 'hoparlör', 'speaker', 'kulaklık', 'headphone', 'cd', 'cd', 'vinyl', 'vinyl'],
            'film' => ['movie', 'cinema', 'video', 'dvd', 'dvd', 'bluray', 'bluray', '4k', '4k', 'uhd', 'uhd', 'projeksiyon', 'projection', 'perde', 'screen', 'kamera', 'camera', 'video kamera', 'video camera', 'drone', 'drone']
        ];
        
        // Tam kategori eşleşmesi
        if (stripos($productCategory, $searchCategory) !== false) {
            return true;
        }
        
        // Kategori anahtar kelimeleri ile eşleşme
        if (isset($categoryMatches[$searchCategory])) {
            foreach ($categoryMatches[$searchCategory] as $keyword) {
                if (stripos($productCategory, $keyword) !== false || 
                    stripos($productName, $keyword) !== false || 
                    stripos($productDescription, $keyword) !== false) {
                    return true;
                }
            }
        }
        
        // Ürün adı ve açıklamasında kategori anahtar kelimelerini ara
        $searchKeywords = [];
        switch ($searchCategory) {
            case 'elektronik':
                $searchKeywords = ['monitör', 'monitor', 'ekran', 'screen', 'tv', 'televizyon', 'bilgisayar', 'computer', 'pc', 'laptop', 'desktop', 'tablet', 'telefon', 'phone', 'mobile', 'smartphone', 'saat', 'watch', 'kamera', 'camera', 'kulaklık', 'headphone', 'hoparlör', 'speaker', 'klavye', 'keyboard', 'mouse', 'fare', 'yazıcı', 'printer', 'scanner', 'tarayıcı', 'qled', 'oled', 'led', '4k', '8k', 'hd', 'fullhd', 'ultrahd', 'gaming', 'oyun', 'game'];
                break;
            case 'giyim':
                $searchKeywords = ['elbise', 'dress', 'gömlek', 'shirt', 'pantolon', 'pants', 'trousers', 'etek', 'skirt', 'ceket', 'jacket', 'kazak', 'sweater', 'bluz', 'blouse', 'ayakkabı', 'shoe', 'çanta', 'bag', 'şapka', 'hat', 'kemer', 'belt', 'çorap', 'sock', 'iç çamaşır', 'underwear', 'mayo', 'swimsuit'];
                break;
            case 'oyuncak':
                $searchKeywords = ['oyuncak', 'toy', 'toys', 'game', 'games', 'gaming', 'play', 'puzzle', 'yapboz', 'lego', 'bebek', 'doll', 'arabalar', 'cars', 'robot', 'robot', 'eğitici', 'educational', 'yapı', 'construction', 'sanat', 'art', 'craft', 'boyama', 'coloring'];
                break;
            case 'kitap':
                $searchKeywords = ['kitap', 'book', 'books', 'literature', 'reading', 'roman', 'novel', 'hikaye', 'story', 'şiir', 'poetry', 'dergi', 'magazine', 'gazete', 'newspaper', 'ansiklopedi', 'encyclopedia', 'sözlük', 'dictionary', 'atlas', 'atlas', 'çizgi roman', 'comic', 'manga', 'manga'];
                break;
            case 'saat':
                $searchKeywords = ['saat', 'watch', 'watches', 'clock', 'timepiece', 'kol saati', 'wristwatch', 'duvar saati', 'wall clock', 'masa saati', 'desk clock', 'çalar saat', 'alarm clock', 'akıllı saat', 'smartwatch', 'dijital', 'digital', 'analog', 'analog'];
                break;
            case 'telefon':
                $searchKeywords = ['telefon', 'phone', 'mobile', 'smartphone', 'cell', 'cep telefonu', 'mobile phone', 'akıllı telefon', 'smartphone', 'iphone', 'iphone', 'samsung', 'samsung', 'huawei', 'huawei', 'xiaomi', 'xiaomi', 'oppo', 'oppo', 'vivo', 'vivo'];
                break;
            case 'bilgisayar':
                $searchKeywords = ['bilgisayar', 'computer', 'pc', 'laptop', 'desktop', 'notebook', 'netbook', 'ultrabook', 'macbook', 'macbook', 'imac', 'imac', 'mac', 'mac', 'windows', 'windows', 'linux', 'linux', 'macos', 'macos', 'işlemci', 'processor', 'cpu', 'cpu', 'ram', 'ram', 'ssd', 'ssd', 'hdd', 'hdd', 'ekran kartı', 'graphics card', 'gpu', 'gpu'];
                break;
            case 'ev':
                $searchKeywords = ['ev', 'home', 'house', 'dekorasyon', 'decoration', 'interior', 'mobilya', 'furniture', 'halı', 'carpet', 'perde', 'curtain', 'lamba', 'lamp', 'mum', 'candle', 'vazo', 'vase', 'resim', 'picture', 'tablo', 'painting', 'çerçeve', 'frame', 'yastık', 'pillow', 'battaniye', 'blanket'];
                break;
            case 'spor':
                $searchKeywords = ['spor', 'sport', 'fitness', 'exercise', 'athletic', 'koşu', 'running', 'yürüyüş', 'walking', 'bisiklet', 'bicycle', 'yoga', 'yoga', 'pilates', 'pilates', 'ağırlık', 'weight', 'dumbbell', 'dumbbell', 'halter', 'barbell', 'top', 'ball', 'raket', 'racket', 'kayak', 'ski'];
                break;
            case 'kozmetik':
                $searchKeywords = ['kozmetik', 'cosmetic', 'beauty', 'makeup', 'skincare', 'makyaj', 'makeup', 'parfüm', 'perfume', 'krem', 'cream', 'losyon', 'lotion', 'şampuan', 'shampoo', 'saç', 'hair', 'cilt', 'skin', 'tırnak', 'nail', 'dudak', 'lip', 'göz', 'eye'];
                break;
            case 'aksesuar':
                $searchKeywords = ['aksesuar', 'accessory', 'accessories', 'jewelry', 'takı', 'jewelry', 'kolye', 'necklace', 'yüzük', 'ring', 'küpe', 'earring', 'bilezik', 'bracelet', 'saat', 'watch', 'çanta', 'bag', 'cüzdan', 'wallet', 'güneş gözlüğü', 'sunglasses', 'şal', 'scarf', 'fular', 'scarf'];
                break;
            case 'mobilya':
                $searchKeywords = ['mobilya', 'furniture', 'furnishings', 'furnishing', 'koltuk', 'sofa', 'sandalye', 'chair', 'masa', 'table', 'dolap', 'cabinet', 'wardrobe', 'wardrobe', 'yatak', 'bed', 'komodin', 'nightstand', 'vitrin', 'display case', 'raf', 'shelf', 'çekmece', 'drawer'];
                break;
            case 'bahçe':
                $searchKeywords = ['bahçe', 'garden', 'outdoor', 'yard', 'patio', 'çiçek', 'flower', 'bitki', 'plant', 'ağaç', 'tree', 'çim', 'grass', 'çit', 'fence', 'havuz', 'pool', 'şömine', 'fireplace', 'barbekü', 'barbecue', 'hamak', 'hammock', 'salıncak', 'swing'];
                break;
            case 'müzik':
                $searchKeywords = ['müzik', 'music', 'audio', 'sound', 'musical', 'gitar', 'guitar', 'piyano', 'piano', 'keman', 'violin', 'flüt', 'flute', 'davul', 'drum', 'bateri', 'drum set', 'mikrofon', 'microphone', 'hoparlör', 'speaker', 'kulaklık', 'headphone', 'cd', 'cd', 'vinyl', 'vinyl'];
                break;
            case 'film':
                $searchKeywords = ['film', 'movie', 'cinema', 'video', 'dvd', 'dvd', 'bluray', 'bluray', '4k', '4k', 'uhd', 'uhd', 'projeksiyon', 'projection', 'perde', 'screen', 'kamera', 'camera', 'video kamera', 'video camera', 'drone', 'drone'];
                break;
        }
        
        // Ürün adı ve açıklamasında kategori anahtar kelimelerini ara
        foreach ($searchKeywords as $keyword) {
            if (stripos($productName, $keyword) !== false || 
                stripos($productDescription, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Chunk'tan ürün bilgilerini çıkarır
     */
    private function extractProductFromChunk(array $chunk): ?array
    {
        try {
            $content = $chunk['content'];
            $metadata = $chunk['metadata'] ?? [];
            
            // JSON content ise parse et
            if (is_string($content) && $this->isJson($content)) {
                $jsonData = json_decode($content, true);
                if (is_array($jsonData) && !empty($jsonData)) {
                    // Eğer array ise ilk elemanı al, değilse direkt kullan
                    $productData = is_numeric(array_keys($jsonData)[0]) ? $jsonData[0] : $jsonData;
                    
                    return [
                        'id' => $productData['id'] ?? $chunk['id'] ?? uniqid(),
                        'name' => $productData['title'] ?? $productData['name'] ?? 'Ürün',
                        'brand' => $productData['brand'] ?? 'Marka',
                        'price' => $productData['price'] ?? 0,
                        'image' => $productData['image'] ?? '/imgs/default-product.jpeg',
                        'category' => $productData['category'] ?? 'Genel',
                        'rating' => is_array($productData['rating']) ? ($productData['rating']['rate'] ?? 4.0) : ($productData['rating'] ?? 4.0),
                        'relevance_score' => $chunk['relevance_score'] ?? $chunk['fuzzy_score'] ?? 0
                    ];
                }
            }
            
            // Metadata'dan ürün bilgilerini al
            if (!empty($metadata)) {
                return [
                    'id' => $metadata['product_id'] ?? $chunk['id'] ?? uniqid(),
                    'name' => $metadata['product_title'] ?? 'Ürün',
                    'brand' => 'Marka',
                    'price' => $metadata['product_price'] ?? 0,
                    'image' => '/imgs/default-product.jpeg',
                    'category' => $metadata['product_category'] ?? 'Genel',
                    'rating' => $metadata['product_rating']['rate'] ?? 4.0,
                    'relevance_score' => $chunk['relevance_score'] ?? $chunk['fuzzy_score'] ?? 0
                ];
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::warning('Product extraction error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * JSON string kontrolü
     */
    private function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Kategori response'u
     */
    private function generateCategoryResponse(string $userMessage, array $searchResults): array
    {
        $products = [];
        $categoryName = $this->extractCategoryFromMessage($userMessage);
        
        if (!empty($searchResults['results'])) {
            foreach ($searchResults['results'] as $result) {
                if (($result['content_type'] ?? '') === 'product') {
                    $product = $this->extractProductFromChunk($result);
                    if ($product && $this->productMatchesCategory($product, $categoryName)) {
                        $products[] = $product;
                    }
                }
            }
        }
        
        // Eğer search results'da ürün bulunamazsa, tüm ürünlerden kategoriye göre filtrele
        if (empty($products) && $categoryName) {
            $allChunks = KnowledgeChunk::with('knowledgeBase')
                ->where('content_type', 'product')
                ->get()
                ->toArray();
            
            foreach ($allChunks as $chunk) {
                $product = $this->extractProductFromChunk($chunk);
                if ($product && $this->productMatchesCategory($product, $categoryName)) {
                    $products[] = $product;
                }
            }
        }
        
        $message = '';
        if (!empty($products)) {
            $message = $categoryName 
                ? "{$categoryName} kategorisinde " . count($products) . " ürün buldum:"
                : "Kategoriye göre " . count($products) . " ürün buldum:";
        } else {
            $message = $categoryName 
                ? "{$categoryName} kategorisinde ürün bulunamadı."
                : "Kategoriye göre ürün bulunamadı.";
        }
        
        return [
            'type' => 'category_browse',
            'message' => $message,
            'data' => [
                'products' => $products,
                'category' => $categoryName,
                'total_products' => count($products),
                'search_query' => $userMessage
            ],
            'suggestions' => [
                'Farklı kategori dene',
                'Fiyat aralığı belirle',
                'Marka seç',
                'Ana sayfaya dön'
            ]
        ];
    }

    /**
     * Marka response'u
     */
    private function generateBrandResponse(string $userMessage, array $searchResults): array
    {
        $brands = [];
        
        if (!empty($searchResults['results'])) {
            foreach ($searchResults['results'] as $result) {
                $metadata = $result['metadata'] ?? [];
                if (isset($metadata['product_brand'])) {
                    $brands[] = $metadata['product_brand'];
                }
            }
            $brands = array_unique($brands);
        }
        
        return [
            'type' => 'brand_search',
            'message' => !empty($brands) 
                ? "Bulunan markalar: " . implode(', ', $brands)
                : "Marka bulunamadı.",
            'data' => [
                'brands' => $brands,
                'total_brands' => count($brands)
            ],
            'suggestions' => [
                'Ürünleri göster',
                'Farklı marka ara',
                'Kategori seç'
            ]
        ];
    }

    /**
     * FAQ response'u
     */
    private function generateFAQResponse(string $userMessage, array $searchResults): array
    {
        $faqs = [];
        
        if (!empty($searchResults['results'])) {
            foreach ($searchResults['results'] as $result) {
                if (($result['content_type'] ?? '') === 'faq') {
                    $faqs[] = [
                        'question' => $result['content'] ?? 'Soru',
                        'answer' => $result['metadata']['answer'] ?? 'Cevap bulunamadı',
                        'relevance_score' => $result['relevance_score'] ?? 0
                    ];
                }
            }
        }
        
        return [
            'type' => 'faq_search',
            'message' => !empty($faqs) 
                ? "Aradığınız soruya " . count($faqs) . " cevap buldum:"
                : "Aradığınız soruya cevap bulunamadı.",
            'data' => [
                'faqs' => $faqs,
                'total_faqs' => count($faqs)
            ],
            'suggestions' => [
                'Farklı soru sor',
                'Yardım al',
                'İletişime geç'
            ]
        ];
    }

    /**
     * Sipariş takip response'u
     */
    private function generateOrderTrackingResponse(): array
    {
        return [
            'type' => 'order_tracking',
            'message' => 'Kargo takip numaranızı veya sipariş numaranızı girin:',
            'data' => [
                'requires_input' => true,
                'input_type' => 'order_number',
                'placeholder' => 'Sipariş/Kargo numarası girin...',
                'button_text' => 'Takip Et'
            ],
            'suggestions' => ['Kargo takip', 'Sipariş geçmişi', 'İletişim', 'Yardım al']
        ];
    }

    /**
     * Selamlama response'u
     */
    private function generateGreetingResponse(): array
    {
        return [
            'type' => 'greeting',
            'message' => 'Merhaba! Ben Kadir, senin dijital asistanınım. Size nasıl yardımcı olabilirim?',
            'suggestions' => ['Ürünleri göster', 'Yardım al', 'SSS']
        ];
    }

    /**
     * Yardım response'u
     */
    private function generateHelpResponse(): array
    {
        return [
            'type' => 'help',
            'message' => 'Size yardımcı olmak için buradayım! Ürünler hakkında bilgi almak, sipariş vermek veya herhangi bir sorunuzu çözmek için bana yazabilirsiniz.',
            'suggestions' => ['Ürün katalogu', 'Sipariş takibi', 'İade işlemleri']
        ];
    }

    /**
     * Genel response
     */
    private function generateGeneralResponse(string $userMessage, array $searchResults): array
    {
        $message = 'Anlıyorum. Size daha iyi yardımcı olabilmem için biraz daha detay verebilir misiniz?';
        
        if (!empty($searchResults['results'])) {
            $message = 'Aradığınız konuyla ilgili bazı sonuçlar buldum. Daha spesifik bir arama yapabilir misiniz?';
        }
        
        return [
            'type' => 'general',
            'message' => $message,
            'suggestions' => ['Ürünler hakkında bilgi', 'Teknik destek', 'Sipariş yardımı']
        ];
    }

    /**
     * AI interaction'ı loglar
     */
    private function logAIInteraction(string $userMessage, string $intent, array $response, string $sessionId): void
    {
        try {
            // AI interactions tablosuna kaydet (mevcut)
            \DB::table('ai_interactions')->insert([
                'session_id' => $sessionId,
                'user_message' => $userMessage,
                'detected_intent' => $intent,
                'ai_response' => json_encode($response),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Enhanced Chat Session'ı güncelle veya oluştur
            $chatSession = EnhancedChatSession::where('session_id', $sessionId)->first();
            
            if (!$chatSession) {
                // Yeni session oluştur
                $chatSession = EnhancedChatSession::create([
                    'session_id' => $sessionId,
                    'daily_view_limit' => 100,
                    'status' => 'active',
                    'last_activity' => now(),
                    'expires_at' => now()->addDays(7) // 7 gün sonra expire et
                ]);
            } else {
                // Mevcut session'ı güncelle
                $chatSession->updateLastActivity();
            }

            // Intent history'ye ekle
            $chatSession->addIntent($intent, $response['confidence'] ?? 0.0);

            // Chat history'ye ekle
            $chatSession->addChatMessage('user', $userMessage, $intent);
            $chatSession->addChatMessage('bot', $response['message'] ?? 'Response', $intent);

            // User preferences güncelle (intent'e göre)
            $this->updateUserPreferencesFromIntent($chatSession, $intent, $response);

            Log::info('Enhanced AI interaction logged successfully', [
                'session_id' => $sessionId,
                'intent' => $intent,
                'session_updated' => true
            ]);

        } catch (\Exception $e) {
            Log::warning('AI interaction logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Intent'e göre user preferences güncelle
     */
    private function updateUserPreferencesFromIntent(EnhancedChatSession $session, string $intent, array $response): void
    {
        try {
            $preferences = [];
            
            switch ($intent) {
                case 'product_search':
                    $preferences['preferred_categories'] = $this->extractCategoriesFromResponse($response);
                    $preferences['search_frequency'] = 'high';
                    break;
                    
                case 'category_browse':
                    $preferences['browsing_behavior'] = 'exploratory';
                    $preferences['preferred_categories'] = $this->extractCategoriesFromResponse($response);
                    break;
                    
                case 'brand_search':
                    $preferences['brand_preferences'] = $this->extractBrandsFromResponse($response);
                    break;
                    
                case 'order_tracking':
                    $preferences['order_concern'] = 'tracking';
                    break;
                    
                case 'faq_search':
                    $preferences['support_needs'] = 'information';
                    break;
                    
                default:
                    $preferences['general_interests'] = $intent;
                    break;
            }
            
            if (!empty($preferences)) {
                $session->updateUserPreferences($preferences);
            }
            
        } catch (\Exception $e) {
            Log::warning('User preferences update failed: ' . $e->getMessage());
        }
    }

    /**
     * Response'dan kategorileri çıkar
     */
    private function extractCategoriesFromResponse(array $response): array
    {
        $categories = [];
        
        if (isset($response['search_results']['results'])) {
            foreach ($response['search_results']['results'] as $result) {
                if (isset($result['metadata']['product_category'])) {
                    $categories[] = $result['metadata']['product_category'];
                }
            }
        }
        
        return array_unique($categories);
    }

    /**
     * Response'dan markaları çıkar
     */
    private function extractBrandsFromResponse(array $response): array
    {
        $brands = [];
        
        if (isset($response['search_results']['results'])) {
            foreach ($response['search_results']['results'] as $result) {
                if (isset($result['metadata']['product_brand'])) {
                    $brands[] = $result['metadata']['product_brand'];
                }
            }
        }
        
        return array_unique($brands);
    }

    /**
     * Feedback işleme
     */
    public function handleFeedback(Request $request) {
        $feedbackData = $request->all();
        
        // Feedback'i logla veya veritabanına kaydet
        Log::info('Feedback received:', $feedbackData);
        
        return response()->json([
            'success' => true,
            'message' => 'Feedback başarıyla alındı',
            'data' => $feedbackData
        ]);
    }

    /**
     * Ürün tıklama işleme
     */
    public function handleProductClick(Request $request) {
        $productData = $request->all();
        
        // Ürün tıklama verisini logla veya veritabanına kaydet
        Log::info('Product click:', $productData);
        
        return response()->json([
            'success' => true,
            'message' => 'Ürün tıklama kaydedildi',
            'data' => $productData
        ]);
    }

    /**
     * Product Interaction API - Ürün etkileşimlerini track eder
     */
    public function handleProductInteraction(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'session_id' => 'required|string',
                'product_id' => 'required|integer', // products tablosu henüz yok, validation'ı kaldır
                'action' => 'required|string|in:view,compare,add_to_cart,buy',
                'timestamp' => 'required|date',
                'source' => 'required|string|in:chat_widget,product_page,checkout',
                'metadata' => 'sometimes|array'
            ]);

            // Find or create session
            $session = EnhancedChatSession::firstOrCreate([
                'session_id' => $validated['session_id']
            ], [
                'status' => 'active',
                'daily_view_count' => 0,
                'daily_view_limit' => 100
            ]);

            // Check daily view limits
            if (!$session->canViewMore()) {
                // Log rate limit exceeded
                \App\Services\AuditLogService::logSecurityEvent('rate_limit_exceeded', [
                    'session_id' => $validated['session_id'],
                    'action' => $validated['action'],
                    'daily_view_count' => $session->daily_view_count,
                    'daily_view_limit' => $session->daily_view_limit
                ]);

                return response()->json([
                    'error' => 'Daily view limit reached',
                    'daily_view_count' => $session->daily_view_count,
                    'daily_view_limit' => $session->daily_view_limit
                ], 429);
            }

            // Create product interaction
            $interaction = ProductInteraction::create([
                'session_id' => $validated['session_id'],
                'product_id' => $validated['product_id'],
                'action' => $validated['action'],
                'timestamp' => $validated['timestamp'],
                'source' => $validated['source'],
                'metadata' => $validated['metadata'] ?? [],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Update session
            $session->incrementViewCount();
            $session->updateLastActivity();

            // Log the interaction for audit
            \App\Services\AuditLogService::logProductInteraction(
                $validated['session_id'],
                $validated['product_id'],
                $validated['action'],
                $validated['metadata'] ?? []
            );

            // Log chat session activity
            \App\Services\AuditLogService::logChatSessionActivity(
                $validated['session_id'],
                'product_interaction',
                [
                    'action' => $validated['action'],
                    'product_id' => $validated['product_id'],
                    'source' => $validated['source']
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Product interaction tracked successfully',
                'session_id' => $validated['session_id'],
                'daily_view_count' => $session->fresh()->daily_view_count,
                'daily_view_limit' => $session->daily_view_limit
            ]);

        } catch (\Exception $e) {
            \Log::error('Product interaction tracking failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            // Log security event
            \App\Services\AuditLogService::logSecurityEvent('product_interaction_failed', [
                'error' => $e->getMessage(),
                'session_id' => $request->input('session_id'),
                'product_id' => $request->input('product_id')
            ]);

            return response()->json([
                'error' => 'Failed to track product interaction',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Session analytics'ini güncelle
     */
    private function updateSessionAnalytics(EnhancedChatSession $session, string $action): void
    {
        try {
            // Action'a göre session metadata'sını güncelle
            $metadata = $session->metadata ?? [];
            
            if (!isset($metadata['action_counts'])) {
                $metadata['action_counts'] = [];
            }
            
            if (!isset($metadata['action_counts'][$action])) {
                $metadata['action_counts'][$action] = 0;
            }
            
            $metadata['action_counts'][$action]++;
            $metadata['last_action'] = $action;
            $metadata['last_action_time'] = now()->toISOString();
            
            $session->update(['metadata' => $metadata]);
            
        } catch (\Exception $e) {
            Log::warning('Session analytics update failed: ' . $e->getMessage());
        }
    }

    /**
     * Session Analytics API - Session detaylarını ve analytics'i getirir
     */
    public function getSessionAnalytics(Request $request, string $sessionId) {
        try {
            // Session'ı bul
            $chatSession = EnhancedChatSession::where('session_id', $sessionId)->first();
            
            if (!$chatSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session bulunamadı'
                ], 404);
            }

            // Product interactions'ları getir
            $interactions = ProductInteraction::where('session_id', $sessionId)
                ->with('product')
                ->orderBy('timestamp', 'desc')
                ->get();

            // Intent history'yi analiz et
            $intentAnalysis = $this->analyzeIntentHistory($chatSession->intent_history ?? []);

            // Product interaction patterns'ı analiz et
            $interactionPatterns = $this->analyzeInteractionPatterns($interactions);

            // User preferences'ı analiz et
            $userPreferences = $this->analyzeUserPreferences($chatSession->user_preferences ?? []);

            // Session statistics'ini hesapla
            $sessionStats = $this->calculateSessionStats($chatSession, $interactions);

            return response()->json([
                'success' => true,
                'data' => [
                    'session' => [
                        'session_id' => $chatSession->session_id,
                        'status' => $chatSession->status,
                        'created_at' => $chatSession->created_at,
                        'last_activity' => $chatSession->last_activity,
                        'daily_view_count' => $chatSession->daily_view_count,
                        'daily_view_limit' => $chatSession->daily_view_limit,
                        'can_view_more' => $chatSession->canViewMore(),
                        'is_active' => $chatSession->isActive(),
                        'is_expired' => $chatSession->isExpired()
                    ],
                    'analytics' => [
                        'intent_analysis' => $intentAnalysis,
                        'interaction_patterns' => $interactionPatterns,
                        'user_preferences' => $userPreferences,
                        'session_stats' => $sessionStats
                    ],
                    'interactions' => $interactions->map(function($interaction) {
                        return [
                            'id' => $interaction->id,
                            'action' => $interaction->action,
                            'timestamp' => $interaction->timestamp,
                            'source' => $interaction->source,
                            'product' => $interaction->product ? [
                                'id' => $interaction->product->id,
                                'title' => $interaction->product->title ?? 'Unknown',
                                'category' => $interaction->product->category ?? 'Unknown'
                            ] : null,
                            'metadata' => $interaction->metadata
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Session analytics error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Session analytics alınırken hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Intent history'yi analiz et
     */
    private function analyzeIntentHistory(array $intentHistory): array
    {
        if (empty($intentHistory)) {
            return [
                'total_intents' => 0,
                'intent_distribution' => [],
                'most_common_intent' => null,
                'confidence_avg' => 0
            ];
        }

        $intentCounts = [];
        $totalConfidence = 0;
        $intentCount = count($intentHistory);

        foreach ($intentHistory as $intent) {
            $intentName = $intent['intent'] ?? 'unknown';
            $confidence = $intent['confidence'] ?? 0;

            if (!isset($intentCounts[$intentName])) {
                $intentCounts[$intentName] = 0;
            }
            $intentCounts[$intentName]++;
            $totalConfidence += $confidence;
        }

        arsort($intentCounts);
        $mostCommonIntent = array_key_first($intentCounts);

        return [
            'total_intents' => $intentCount,
            'intent_distribution' => $intentCounts,
            'most_common_intent' => $mostCommonIntent,
            'confidence_avg' => $intentCount > 0 ? round($totalConfidence / $intentCount, 2) : 0
        ];
    }

    /**
     * Interaction patterns'ı analiz et
     */
    private function analyzeInteractionPatterns($interactions): array
    {
        if ($interactions->isEmpty()) {
            return [
                'total_interactions' => 0,
                'action_distribution' => [],
                'source_distribution' => [],
                'conversion_rate' => 0,
                'most_active_hour' => null
            ];
        }

        $actionCounts = [];
        $sourceCounts = [];
        $conversionActions = 0;
        $hourlyActivity = [];

        foreach ($interactions as $interaction) {
            // Action counts
            $action = $interaction->action;
            if (!isset($actionCounts[$action])) {
                $actionCounts[$action] = 0;
            }
            $actionCounts[$action]++;

            // Source counts
            $source = $interaction->source;
            if (!isset($sourceCounts[$source])) {
                $sourceCounts[$source] = 0;
            }
            $sourceCounts[$source]++;

            // Conversion tracking
            if (in_array($action, ['buy', 'add_to_cart'])) {
                $conversionActions++;
            }

            // Hourly activity
            $hour = $interaction->timestamp->hour;
            if (!isset($hourlyActivity[$hour])) {
                $hourlyActivity[$hour] = 0;
            }
            $hourlyActivity[$hour]++;
        }

        arsort($actionCounts);
        arsort($sourceCounts);
        arsort($hourlyActivity);

        $totalInteractions = $interactions->count();
        $conversionRate = $totalInteractions > 0 ? round(($conversionActions / $totalInteractions) * 100, 2) : 0;
        $mostActiveHour = array_key_first($hourlyActivity);

        return [
            'total_interactions' => $totalInteractions,
            'action_distribution' => $actionCounts,
            'source_distribution' => $sourceCounts,
            'conversion_rate' => $conversionRate,
            'most_active_hour' => $mostActiveHour,
            'hourly_activity' => $hourlyActivity
        ];
    }

    /**
     * User preferences'ı analiz et
     */
    private function analyzeUserPreferences(array $userPreferences): array
    {
        if (empty($userPreferences)) {
            return [
                'has_preferences' => false,
                'preference_summary' => []
            ];
        }

        return [
            'has_preferences' => true,
            'preference_summary' => $userPreferences
        ];
    }

    /**
     * Session statistics'ini hesapla
     */
    private function calculateSessionStats(EnhancedChatSession $session, $interactions): array
    {
        $totalInteractions = $interactions->count();
        $conversionInteractions = $interactions->whereIn('action', ['buy', 'add_to_cart'])->count();
        $conversionRate = $totalInteractions > 0 ? round(($conversionInteractions / $totalInteractions) * 100, 2) : 0;

        $sessionDuration = $session->created_at->diffInMinutes($session->last_activity ?? $session->created_at);

        return [
            'total_interactions' => $totalInteractions,
            'conversion_interactions' => $conversionInteractions,
            'conversion_rate' => $conversionRate,
            'session_duration_minutes' => $sessionDuration,
            'daily_view_usage' => round(($session->daily_view_count / $session->daily_view_limit) * 100, 2)
        ];
    }

    /**
     * Kargo takip işleme
     */
    public function handleCargoTracking(Request $request) {
        $cargoNumber = $request->input('cargo_number');
        
        // Kargo takip numarasını logla
        Log::info('Cargo tracking request:', ['cargo_number' => $cargoNumber]);
        
        // Simüle edilmiş kargo takip sonucu
        $cargoStatus = $this->simulateCargoTracking($cargoNumber);
        
        return response()->json([
            'success' => true,
            'message' => 'Kargo takip bilgisi alındı',
            'data' => $cargoStatus
        ]);
    }

    /**
     * Sipariş numarası ile kargo takip işleme
     */
    public function handleOrderTracking(Request $request) {
        $orderNumber = $request->input('order_number');
        
        // Sipariş numarasını logla
        Log::info('Order tracking request:', ['order_number' => $orderNumber]);
        
        // Simüle edilmiş sipariş takip sonucu - yeni format
        $trackingData = [
            'intent' => 'order_tracking',
            'phase' => 'cargo',
            'order_id' => 'ORD-998877',
            'status' => 'in_transit',
            'courier' => 'Yurtiçi Kargo',
            'tracking_number' => 'YT123456789TR',
            'last_update' => '2025-08-18T14:30:00Z',
            'estimated_delivery' => '2025-08-20'
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Sipariş takip bilgisi alındı',
            'data' => $trackingData
        ]);
    }

    /**
     * Kargo takip simülasyonu
     */
    private function simulateCargoTracking($cargoNumber) {
        // Gerçek uygulamada burada kargo firması API'si kullanılır
        $statuses = [
            'Kargo kabul edildi',
            'Transfer merkezinde',
            'Yolda',
            'Dağıtım merkezinde',
            'Kurye yola çıktı',
            'Teslim edildi'
        ];
        
        $randomStatus = $statuses[array_rand($statuses)];
        $estimatedDelivery = date('Y-m-d', strtotime('+2 days'));
        
        return [
            'cargo_number' => $cargoNumber,
            'status' => $randomStatus,
            'estimated_delivery' => $estimatedDelivery,
            'current_location' => 'İstanbul Transfer Merkezi',
            'last_update' => date('Y-m-d H:i:s'),
            'tracking_url' => "https://tracking.example.com/{$cargoNumber}"
        ];
    }

    public function testIntentSystem() {
        $testQueries = [
            'Merhaba, nasılsın?',
            'Selam!',
            'iPhone fiyatı ne kadar?',
            'Samsung telefon kaç para?',
            'Elektronik kategorisinde neler var?',
            'Giyim türleri göster',
            'Nike ayakkabıları göster',
            'Apple markası var mı?',
            'Stokta olan ürünler neler?',
            'Depoda kalan ürünler',
            'Bana öneri ver',
            'En iyi ürünler neler?',
            'iPhone vs Samsung karşılaştır',
            'Hangisi daha iyi?',
            'Yardım almak istiyorum',
            'Ne yapabilirsin?',
            'Bilmiyorum ne yapayım',
            'Güle güle',
            'Teşekkürler',
            'iPhone istiyorum',
            'Spor ayakkabı arıyorum',
            'Ev eşyası kategorisi',
            'Teknoloji ürünleri',
            'Moda kıyafetler',
            'Fitness malzemeleri'
        ];
        
        $intentSystem = new IntentDetectionService();
        $results = [];
        
        foreach ($testQueries as $query) {
            $detectedIntent = $intentSystem->detectIntent($query);
            $response = $intentSystem->generateResponse($detectedIntent, $query);
            $results[] = [
                'query' => $query,
                'detected_intent' => $detectedIntent,
                'response' => $response
            ];
        }
        
        return response()->json([
            'message' => 'Advanced Intent Detection System Test Results',
            'total_queries' => count($testQueries),
            'system_features' => [
                'thesaurus_support' => true,
                'fuzzy_matching' => true,
                'flexible_thresholds' => true,
                'context_suggestion' => true,
                'synonym_detection' => true
            ],
            'results' => $results
        ]);
    }

    public function getprompt($usermessage){
     $prompt =[
          ['role'=>'system','content'=>'Sen bir e-ticaret asistanısın'],
          ['role'=>'user','content'=>"Bana şu ürünlerden öneri yap: ".json_encode($this->productData->getAllProducts())]
     ];
     return $prompt;
    }

    /**
     * Veritabanından ürünleri getir
     */
    public function getProductsFromDB(Request $request)
    {
        $query = Product::query();

        // Filtreleme
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->has('min_price') && $request->has('max_price')) {
            $query->whereBetween('price', [$request->min_price, $request->max_price]);
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('brand', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }

        // Sıralama
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Sayfalama
        $perPage = $request->get('per_page', 20);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products,
            'total' => $products->total(),
            'per_page' => $products->perPage(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage()
        ]);
    }

    /**
     * Kategori bazında ürün istatistikleri
     */
    public function getCategoryStats()
    {
        $stats = Product::selectRaw('category, COUNT(*) as product_count, AVG(price) as avg_price, AVG(rating) as avg_rating')
            ->groupBy('category')
            ->orderBy('product_count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * En yüksek puanlı ürünler
     */
    public function getTopRatedProducts(Request $request)
    {
        $limit = $request->get('limit', 10);
        $products = Product::orderBy('rating', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function createSession() {
        $chatSession = new ChatSession();
        return response()->json([
            'session_id' => $chatSession->getSessionId(),
            'message' => 'Yeni chat session oluşturuldu',
            'timestamp' => time()
        ]);
    }
    
    public function getSessionInfo($sessionId) {
        $chatSession = new ChatSession($sessionId);
        return response()->json([
            'session_id' => $chatSession->getSessionId(),
            'session_info' => [
                'total_messages' => $chatSession->getTotalMessages(),
                'conversation_context' => $chatSession->getConversationContext(),
                'last_intent' => $chatSession->getLastIntent(),
                'session_duration' => $chatSession->getSessionDuration(),
                'context_summary' => $chatSession->getContextSummary()
            ]
        ]);
    }
    
    public function clearSession($sessionId) {
        $chatSession = new ChatSession($sessionId);
        $chatSession->clearSession();
        return response()->json([
            'session_id' => $chatSession->getSessionId(),
            'message' => 'Session başarıyla temizlendi',
            'timestamp' => time()
        ]);
    }

    /**
     * Get all chat messages for a specific session
     */
    public function getChatSession($sessionId) {
        try {
            // Session ID'yi temizle
            $sessionId = trim($sessionId);
            
            if (empty($sessionId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session ID gerekli'
                ], 400);
            }

            // ChatSession sınıfını kullanarak session'ı bul
            $chatSession = new ChatSession($sessionId);
            
            // Session'ın var olup olmadığını kontrol et
            if (!$chatSession->hasSession()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session bulunamadı: ' . $sessionId
                ], 404);
            }

            // Session'daki tüm mesajları al
            $messages = $chatSession->getAllMessages();
            
            // Session bilgilerini al
            $sessionInfo = [
                'session_id' => $sessionId,
                'created_at' => $chatSession->getCreatedAt(),
                'last_activity' => $chatSession->getLastActivity(),
                'message_count' => count($messages),
                'total_tokens' => $chatSession->getTotalTokens()
            ];

            return response()->json([
                'success' => true,
                'session_info' => $sessionInfo,
                'messages' => $messages,
                'total_messages' => count($messages)
            ]);

        } catch (\Exception $e) {
            \Log::error('getChatSession error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Session mesajları alınırken hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clear all messages and context from a specific session
     */
    public function clearChatSession($sessionId) {
        try {
            // Session ID'yi temizle
            $sessionId = trim($sessionId);
            
            if (empty($sessionId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session ID gerekli'
                ], 400);
            }

            // ChatSession sınıfını kullanarak session'ı bul
            $chatSession = new ChatSession($sessionId);
            
            // Session'ın var olup olmadığını kontrol et
            if (!$chatSession->hasSession()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session bulunamadı: ' . $sessionId
                ], 404);
            }

            // Session'ı temizle
            $clearResult = $chatSession->clearSession();
            
            return response()->json([
                'success' => true,
                'message' => 'Session başarıyla temizlendi',
                'session_id' => $sessionId,
                'cleared_at' => date('Y-m-d H:i:s'),
                'cleared_info' => [
                    'messages_removed' => $clearResult['messages_removed'],
                    'context_cleared' => $clearResult['context_cleared'],
                    'session_reset' => $clearResult['session_reset']
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('clearChatSession error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Session temizlenirken hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get AI-generated intents
     */
    public function getAIGeneratedIntents() {
        try {
            $intentSystem = new IntentDetectionService();
            $aiIntents = $intentSystem->getAIGeneratedIntents();
            
            return response()->json([
                'success' => true,
                'ai_generated_intents' => $aiIntents,
                'total_ai_intents' => count($aiIntents),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            \Log::error('getAIGeneratedIntents error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'AI-generated intent\'ler alınırken hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get intent system statistics
     */
    public function getIntentStats() {
        try {
            $intentSystem = new IntentDetectionService();
            $aiIntents = $intentSystem->getAIGeneratedIntents();
            
            // Tüm intent'leri say
            $totalIntents = count($intentSystem->getAllIntents());
            $aiGeneratedCount = count($aiIntents);
            $originalIntents = $totalIntents - $aiGeneratedCount;
            
            // Kullanım istatistikleri
            $totalUsage = 0;
            $mostUsedIntent = null;
            $maxUsage = 0;
            
            foreach ($aiIntents as $intentName => $intentData) {
                $usage = $intentData['usage_count'] ?? 0;
                $totalUsage += $usage;
                
                if ($usage > $maxUsage) {
                    $maxUsage = $usage;
                    $mostUsedIntent = $intentName;
                }
            }
            
            return response()->json([
                'success' => true,
                'statistics' => [
                    'total_intents' => $totalIntents,
                    'original_intents' => $originalIntents,
                    'ai_generated_intents' => $aiGeneratedCount,
                    'ai_generation_rate' => $aiGeneratedCount > 0 ? round(($aiGeneratedCount / $totalIntents) * 100, 2) : 0,
                    'total_ai_usage' => $totalUsage,
                    'most_used_ai_intent' => $mostUsedIntent,
                    'max_usage_count' => $maxUsage
                ],
                'ai_intents_summary' => array_map(function($intent) {
                    return [
                        'keywords_count' => count($intent['keywords']),
                        'usage_count' => $intent['usage_count'] ?? 0,
                        'created_at' => $intent['created_at']
                    ];
                }, $aiIntents),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            \Log::error('getIntentStats error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Intent istatistikleri alınırken hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get all categories with analysis
     */
    public function getAllCategories() {
        try {
            $productData = new ProductData();
            $categories = $productData->getCategoryAnalysis();
            
            return response()->json([
                'success' => true,
                'categories' => $categories,
                'total_categories' => count($categories),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            \Log::error('getAllCategories error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kategoriler alınırken hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get detailed analysis for a specific category
     */
    public function getCategoryDetails($category) {
        try {
            $productData = new ProductData();
            $categoryDetails = $productData->getCategoryDetails($category);
            
            if (!$categoryDetails) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori bulunamadı: ' . $category
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'category_details' => $categoryDetails,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            \Log::error('getCategoryDetails error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kategori detayları alınırken hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get category recommendations
     */
    public function getCategoryRecommendations() {
        try {
            $productData = new ProductData();
            $recommendations = $productData->getCategoryRecommendations(10);
            
            return response()->json([
                'success' => true,
                'category_recommendations' => $recommendations,
                'total_recommendations' => count($recommendations),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            \Log::error('getCategoryRecommendations error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kategori önerileri alınırken hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GDPR Compliance - Export user data
     */
    public function exportUserData(Request $request, string $sessionId)
    {
        try {
            // Validate session exists
            $session = EnhancedChatSession::where('session_id', $sessionId)->first();
            
            if (!$session) {
                return response()->json([
                    'error' => 'Session not found'
                ], 404);
            }

            // Export data using GDPR service
            $exportedData = \App\Services\GDPRComplianceService::exportUserData($sessionId);

            // Log data export for audit
            \App\Services\AuditLogService::logDataAccess('user_data_export', $sessionId);

            return response()->json([
                'success' => true,
                'data' => $exportedData,
                'exported_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            \Log::error('GDPR data export failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to export user data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GDPR Compliance - Delete user data
     */
    public function deleteUserData(Request $request, string $sessionId)
    {
        try {
            // Validate request
            $request->validate([
                'reason' => 'required|string|max:500',
                'confirmation' => 'required|string|in:DELETE'
            ]);

            // Validate session exists
            $session = EnhancedChatSession::where('session_id', $sessionId)->first();
            
            if (!$session) {
                return response()->json([
                    'error' => 'Session not found'
                ], 404);
            }

            // Delete data using GDPR service
            $deleted = \App\Services\GDPRComplianceService::deleteUserData($sessionId);

            if ($deleted) {
                // Log deletion for audit
                \App\Services\AuditLogService::logGDPRAction('data_deletion', $sessionId, [
                    'reason' => $request->input('reason'),
                    'deleted_by' => $request->ip()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'User data deleted successfully',
                    'deleted_at' => now()->toISOString()
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to delete user data'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('GDPR data deletion failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to delete user data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GDPR Compliance - Anonymize user data
     */
    public function anonymizeUserData(Request $request, string $sessionId)
    {
        try {
            // Validate request
            $request->validate([
                'reason' => 'required|string|max:500'
            ]);

            // Validate session exists
            $session = EnhancedChatSession::where('session_id', $sessionId)->first();
            
            if (!$session) {
                return response()->json([
                    'error' => 'Session not found'
                ], 404);
            }

            // Anonymize data using GDPR service
            $anonymized = \App\Services\GDPRComplianceService::anonymizeUserData($sessionId);

            if ($anonymized) {
                // Log anonymization for audit
                \App\Services\AuditLogService::logGDPRAction('data_anonymization', $sessionId, [
                    'reason' => $request->input('reason'),
                    'anonymized_by' => $request->ip()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'User data anonymized successfully',
                    'anonymized_at' => now()->toISOString()
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to anonymize user data'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('GDPR data anonymization failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to anonymize user data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GDPR Compliance - Get data retention summary
     */
    public function getDataRetentionSummary()
    {
        try {
            $summary = \App\Services\GDPRComplianceService::getDataRetentionSummary();

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            \Log::error('GDPR data retention summary failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get data retention summary',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * AI response'dan ürünleri çıkarır
     */
    private function extractProductsFromAIResponse(string $aiResponse, array $allProducts, string $searchCategory): array
    {
        try {
            // AI response'u JSON olarak parse etmeye çalış
            $decoded = json_decode($aiResponse, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // JSON başarıyla parse edildi, ürün ID'lerini çıkar
                $productIds = [];
                if (isset($decoded['products'])) {
                    foreach ($decoded['products'] as $product) {
                        if (isset($product['id'])) {
                            $productIds[] = $product['id'];
                        }
                    }
                }
                
                // ID'lere göre ürünleri filtrele
                if (!empty($productIds)) {
                    return array_filter($allProducts, function($product) use ($productIds) {
                        return in_array($product['id'], $productIds);
                    });
                }
            }
            
            // JSON parse edilemediyse, AI response'da ürün adlarını ara
            $filteredProducts = [];
            foreach ($allProducts as $product) {
                if (stripos($aiResponse, $product['name']) !== false || 
                    stripos($aiResponse, $product['category']) !== false) {
                    $filteredProducts[] = $product;
                }
            }
            
            return $filteredProducts;
            
        } catch (\Exception $e) {
            Log::warning('AI response parsing failed, using fallback', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Fuzzy kategori araması yapar
     */
    private function fuzzyCategorySearch(array $allProducts, string $searchCategory): array
    {
        $filteredProducts = [];
        $searchCategory = strtolower($searchCategory);
        
        foreach ($allProducts as $product) {
            $productCategory = strtolower($product['category']);
            $productName = strtolower($product['name']);
            
            // Kategori eşleşmesi
            if (stripos($productCategory, $searchCategory) !== false) {
                $filteredProducts[] = $product;
                continue;
            }
            
            // Ürün adında kategori anahtar kelimeleri ara
            $categoryKeywords = $this->getCategoryKeywords($searchCategory);
            foreach ($categoryKeywords as $keyword) {
                if (stripos($productName, $keyword) !== false || 
                    stripos($productCategory, $keyword) !== false) {
                    $filteredProducts[] = $product;
                    break;
                }
            }
        }
        
        return $filteredProducts;
    }
    
    /**
     * Kategori için anahtar kelimeleri döndürür
     */
    private function getCategoryKeywords(string $category): array
    {
        $keywords = [
            'elektronik' => ['telefon', 'bilgisayar', 'tablet', 'tv', 'televizyon', 'kulaklık', 'kamera', 'monitör', 'ekran', 'laptop', 'pc', 'oyun', 'konsol'],
            'giyim' => ['elbise', 'pantolon', 'gömlek', 'ayakkabı', 'çanta', 'ceket', 'etek', 'tshirt', 'hırka', 'kazak', 'şort', 'eşofman'],
            'oyuncak' => ['oyuncak', 'oyun', 'lego', 'bebek', 'puzzle', 'yapboz', 'robot', 'arabalar'],
            'kitap' => ['kitap', 'roman', 'hikaye', 'şiir', 'dergi', 'gazete', 'ansiklopedi'],
            'kozmetik' => ['kozmetik', 'makyaj', 'cilt', 'saç', 'parfüm', 'ruj', 'fondöten', 'şampuan', 'krem'],
            'aksesuar' => ['aksesuar', 'takı', 'saat', 'güneş gözlüğü', 'kolye', 'yüzük', 'küpe'],
            'mobilya' => ['mobilya', 'koltuk', 'masa', 'sandalye', 'dolap', 'yatak', 'komodin'],
            'bahçe' => ['bahçe', 'çiçek', 'bitki', 'ağaç', 'çim', 'havuz', 'şömine'],
            'müzik' => ['müzik', 'gitar', 'piyano', 'keman', 'flüt', 'davul', 'mikrofon'],
            'film' => ['film', 'dvd', 'bluray', '4k', 'uhd', 'projeksiyon']
        ];
        
        return $keywords[$category] ?? [];
    }
}

class ChatSession {
    private $sessionId;
    private $messages = [];
    private $context = [];
    private $startTime;
    private $lastIntent;
    private $productContext = [];
    private $storagePath;
    
    public function __construct($sessionId = null) {
        $this->sessionId = $sessionId ?: $this->generateSessionId();
        $this->startTime = time();
        $this->storagePath = storage_path('app/chat_sessions/');
        
        // Storage dizinini oluştur
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
        
        $this->initializeContext();
        $this->loadSession();
    }
    
    private function generateSessionId() {
        return 'chat_' . uniqid() . '_' . time();
    }
    
    private function getSessionFilePath() {
        return $this->storagePath . $this->sessionId . '.json';
    }
    
    private function loadSession() {
        $filePath = $this->getSessionFilePath();
        
        if (file_exists($filePath)) {
            try {
                $sessionData = json_decode(file_get_contents($filePath), true);
                if ($sessionData) {
                    $this->messages = $sessionData['messages'] ?? [];
                    $this->context = $sessionData['context'] ?? [];
                    $this->startTime = $sessionData['start_time'] ?? time();
                    $this->lastIntent = $sessionData['last_intent'] ?? null;
                }
            } catch (\Exception $e) {
                \Log::error('Session load error: ' . $e->getMessage());
            }
        }
    }
    
    private function saveSession() {
        $filePath = $this->getSessionFilePath();
        
        try {
            $sessionData = [
                'session_id' => $this->sessionId,
                'start_time' => $this->startTime,
                'messages' => $this->messages,
                'context' => $this->context,
                'last_intent' => $this->lastIntent
            ];
            
            file_put_contents($filePath, json_encode($sessionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Exception $e) {
            \Log::error('Session save error: ' . $e->getMessage());
        }
    }
    
    public function addMessage($role, $content, $intent = null, $response = null) {
        $message = [
            'id' => count($this->messages) + 1,
            'role' => $role, // 'user' veya 'bot'
            'content' => $content,
            'timestamp' => time(),
            'intent' => $intent,
            'response_data' => $response
        ];
        
        $this->messages[] = $message;
        
        // Context güncelle
        if ($intent) {
            $this->updateContext($intent, $content, $response);
        }
        
        // Son 10 mesajı tut (performans için)
        if (count($this->messages) > 10) {
            $this->messages = array_slice($this->messages, -10);
        }
        
        // Session'ı kaydet
        $this->saveSession();
    }
    
    private function updateContext($intent, $content, $response) {
        $this->lastIntent = $intent;
        $this->context['intent_history'][] = $intent;
        
        // Son 5 niyeti tut
        if (count($this->context['intent_history']) > 5) {
            $this->context['intent_history'] = array_slice($this->context['intent_history'], -5);
        }
        
        // Ürün context'i güncelle
        if (isset($response['products']) && !empty($response['products'])) {
            $this->context['last_products'] = array_slice($response['products'], 0, 3);
        }
        
        // Kategori context'i güncelle
        if (isset($response['category'])) {
            $this->context['current_category'] = $response['category'];
        }
        
        // Marka context'i güncelle
        if (isset($response['brand'])) {
            $this->context['current_brand'] = $response['brand'];
        }
        
        // Fiyat aralığı context'i güncelle
        if (isset($response['price_range'])) {
            $this->context['current_price_range'] = $response['price_range'];
        }
        
        // Kullanıcı tercihleri
        if ($intent === 'product_search' || $intent === 'category_browse') {
            $this->context['user_preferences'][] = $content;
            if (count($this->context['user_preferences']) > 5) {
                $this->context['user_preferences'] = array_slice($this->context['user_preferences'], -5);
            }
        }
        
        // Konuşma akışı
        $this->context['conversation_flow'][] = [
            'intent' => $intent,
            'timestamp' => time(),
            'user_message' => $content
        ];
        
        if (count($this->context['conversation_flow']) > 10) {
            $this->context['conversation_flow'] = array_slice($this->context['conversation_flow'], -10);
        }
    }
    
    public function getSessionId() {
        return $this->sessionId;
    }
    
    public function getTotalMessages() {
        return count($this->messages);
    }
    
    public function getConversationContext() {
        return $this->context;
    }
    
    public function getLastIntent() {
        return $this->lastIntent;
    }
    
    public function getSessionDuration() {
        return time() - $this->startTime;
    }
    
    public function getLastProducts() {
        return $this->context['last_products'];
    }
    
    public function getCurrentCategory() {
        return $this->context['current_category'];
    }
    
    public function getCurrentBrand() {
        return $this->context['current_brand'];
    }
    
    public function getUserPreferences() {
        return $this->context['user_preferences'];
    }
    
    public function getIntentHistory() {
        return $this->context['intent_history'];
    }
    
    public function getConversationFlow() {
        return $this->context['conversation_flow'];
    }
    
    public function hasContext($type) {
        switch ($type) {
            case 'category':
                return !empty($this->context['current_category']);
            case 'brand':
                return !empty($this->context['current_brand']);
            case 'products':
                return !empty($this->context['last_products']);
            case 'price_range':
                return !empty($this->context['current_price_range']);
            default:
                return false;
        }
    }
    
    public function getContextSummary() {
        $summary = [];
        
        if ($this->context['current_category']) {
            $summary[] = "Kategori: " . $this->context['current_category'];
        }
        
        if ($this->context['current_brand']) {
            $summary[] = "Marka: " . $this->context['current_brand'];
        }
        
        if ($this->context['last_products']) {
            $summary[] = "Son ürünler: " . count($this->context['last_products']) . " adet";
        }
        
        if ($this->context['user_preferences']) {
            $summary[] = "Kullanıcı tercihleri: " . implode(', ', array_slice($this->context['user_preferences'], -3));
        }
        
        return implode(' | ', $summary);
    }
    
    public function clearContext() {
        $this->initializeContext();
    }
    
    /**
     * Clear all messages and context from the session
     */
    public function clearSession() {
        $messagesCount = count($this->messages);
        $contextCleared = !empty($this->context);
        
        // Mesajları temizle
        $this->messages = [];
        
        // Context'i temizle
        $this->clearContext();
        
        // Session dosyasını sil
        $filePath = $this->getSessionFilePath();
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Session'ı yeniden başlat
        $this->startTime = time();
        
        return [
            'messages_removed' => $messagesCount,
            'context_cleared' => $contextCleared,
            'session_reset' => true
        ];
    }
    
    public function exportSession() {
        return [
            'session_id' => $this->sessionId,
            'start_time' => $this->startTime,
            'total_messages' => count($this->messages),
            'context' => $this->context,
            'messages' => $this->messages
        ];
    }
    
    /**
     * Check if session exists and has messages
     */
    public function hasSession() {
        $filePath = $this->getSessionFilePath();
        return file_exists($filePath) && !empty($this->sessionId);
    }
    
    /**
     * Get all messages in the session
     */
    public function getAllMessages() {
        return $this->messages;
    }
    
    /**
     * Get session creation time
     */
    public function getCreatedAt() {
        return date('Y-m-d H:i:s', $this->startTime);
    }
    
    /**
     * Get last activity time
     */
    public function getLastActivity() {
        if (empty($this->messages)) {
            return $this->getCreatedAt();
        }
        
        $lastMessage = end($this->messages);
        return date('Y-m-d H:i:s', $lastMessage['timestamp']);
    }
    
    /**
     * Get total tokens used in session (estimated)
     */
    public function getTotalTokens() {
        $totalTokens = 0;
        
        foreach ($this->messages as $message) {
            // Basit token hesaplama (yaklaşık)
            $content = $message['content'];
            $tokens = ceil(mb_strlen($content) / 4); // 1 token ≈ 4 karakter
            $totalTokens += $tokens;
        }
        
        return $totalTokens;
    }
    
    private function initializeContext() {
        $this->context = [
            'current_category' => null,
            'current_brand' => null,
            'current_price_range' => null,
            'last_products' => [],
            'user_preferences' => [],
            'conversation_flow' => [],
            'intent_history' => []
        ];
    }
}

class SmartProductRecommender {
    private $productData;
    private $categoryKeywords = [];
    private $productSynonyms = [];
    private $contextualRules = [];
    private $colorKeywords = [];
    
    public function __construct($productData) {
        $this->productData = $productData;
        $this->initializeCategoryKeywords();
        $this->initializeProductSynonyms();
        $this->initializeContextualRules();
        $this->initializeColorKeywords();
    }
    
    private function initializeCategoryKeywords() {
        $this->categoryKeywords = [
            'pet_shop' => [
                'keywords' => ['köpek', 'kedi', 'pet', 'hayvan', 'mama', 'evcil', 'kuş', 'balık', 'hamster', 'tavşan', 'kaplumbağa', 'sürüngen'],
                'categories' => ['Kedi Maması', 'Köpek Maması', 'Pet Shop'],
                'priority' => 1
            ],
            'electronics' => [
                'keywords' => ['telefon', 'iphone', 'samsung', 'bilgisayar', 'laptop', 'tablet', 'ipad', 'kulaklık', 'televizyon', 'tv', 'oyun', 'playstation', 'xbox'],
                'categories' => ['Telefon', 'Bilgisayar', 'Tablet', 'Kulaklık', 'Televizyon', 'Oyun Konsolu'],
                'priority' => 1
            ],
            'clothing' => [
                'keywords' => ['giyim', 'kıyafet', 'elbise', 'ayakkabı', 'spor', 'nike', 'adidas', 'ceket', 'pantolon', 'gömlek', 'tshirt', 'hırka'],
                'categories' => ['Spor Ayakkabı', 'Kot Pantolon', 'Polo Yaka', 'Ceket', 'Elbise', 'Gömlek', 'Sweatshirt', 'Etek', 'Çanta'],
                'priority' => 1
            ],
            'home_living' => [
                'keywords' => ['ev', 'mobilya', 'dekorasyon', 'mutfak', 'banyo', 'yatak odası', 'salon', 'ikea', 'mobilya', 'ev eşyası', 'yaşam'],
                'categories' => ['Mobilya', 'Aydınlatma', 'Beyaz Eşya', 'Mutfak'],
                'priority' => 1
            ],
            'sports_outdoor' => [
                'keywords' => ['spor', 'fitness', 'egzersiz', 'koşu', 'yürüyüş', 'bisiklet', 'yüzme', 'futbol', 'basketbol', 'tenis', 'golf', 'outdoor'],
                'categories' => ['Bisiklet', 'Spor Ayakkabı', 'Mont', 'Ceket', 'Hırka', 'Spor Çanta', 'Spor Çorap', 'Şort', 'Spor Tshirt', 'Spor Pantolon'],
                'priority' => 1
            ],
            'beauty_cosmetics' => [
                'keywords' => ['kozmetik', 'makyaj', 'cilt bakımı', 'saç bakımı', 'parfüm', 'ruj', 'fondöten', 'göz farı', 'şampuan', 'nemlendirici'],
                'categories' => ['Şampuan', 'Yüz Bakımı', 'Nemlendirici', 'Makyaj', 'Güneş Bakımı', 'Serum'],
                'priority' => 1
            ],
            'books_hobbies' => [
                'keywords' => ['kitap', 'okuma', 'hobi', 'oyuncak', 'lego', 'oyun', 'puzzle', 'sanat', 'müzik', 'film', 'dizi', 'roman', 'bilim'],
                'categories' => ['Kitap', 'Oyuncak', 'Oyun'],
                'priority' => 1
            ],
            'automotive' => [
                'keywords' => ['araba', 'otomobil', 'araç', 'lastik', 'akü', 'motor yağı', 'bakım', 'servis', 'parça', 'aksesuar'],
                'categories' => ['Lastik', 'Akü', 'Motor Yağı'],
                'priority' => 1
            ],
            'health_medicine' => [
                'keywords' => ['sağlık', 'ilaç', 'vitamin', 'mineral', 'ağrı kesici', 'ateş düşürücü', 'bağışıklık', 'enerji', 'uyku', 'stres'],
                'categories' => ['Ağrı Kesici', 'Vitamin', 'Mineral'],
                'priority' => 1
            ],
            'garden_tools' => [
                'keywords' => ['bahçe', 'çiçek', 'bitki', 'ağaç', 'çim', 'tırmık', 'makas', 'çim biçme', 'testere', 'el aleti', 'matkap', 'tornavida'],
                'categories' => ['Bahçe Aleti', 'Bahçe Makinesi', 'El Aleti'],
                'priority' => 1
            ]
        ];
    }
    
    private function initializeProductSynonyms() {
        $this->productSynonyms = [
            // Pet Shop
            'köpek' => ['pet', 'hayvan', 'evcil', 'mama', 'köpek maması', 'köpek oyuncağı', 'köpek tasması'],
            'kedi' => ['pet', 'hayvan', 'evcil', 'mama', 'kedi maması', 'kedi oyuncağı', 'kedi kumu'],
            'pet' => ['köpek', 'kedi', 'hayvan', 'evcil', 'mama', 'oyuncak', 'aksesuar'],
            
            // Electronics
            'telefon' => ['iphone', 'samsung', 'galaxy', 'smartphone', 'mobil', 'cep telefonu'],
            'bilgisayar' => ['laptop', 'macbook', 'dell', 'hp', 'lenovo', 'notebook', 'dizüstü'],
            'tablet' => ['ipad', 'samsung tab', 'android tablet', 'dijital tablet'],
            
            // Clothing
            'ayakkabı' => ['spor ayakkabı', 'günlük ayakkabı', 'topuklu', 'düz', 'sneaker'],
            'kıyafet' => ['elbise', 'pantolon', 'gömlek', 'tshirt', 'hırka', 'ceket', 'eşofman'],
            
            // Home & Living
            'ev' => ['mobilya', 'dekorasyon', 'mutfak', 'banyo', 'yatak odası', 'salon'],
            'mobilya' => ['yatak', 'dolap', 'masa', 'sandalye', 'koltuk', 'sehpa'],
            
            // Sports
            'spor' => ['fitness', 'egzersiz', 'koşu', 'yürüyüş', 'bisiklet', 'yüzme'],
            'fitness' => ['spor', 'egzersiz', 'koşu', 'yürüyüş', 'bisiklet', 'yüzme'],
            
            // Beauty
            'kozmetik' => ['makyaj', 'cilt bakımı', 'saç bakımı', 'parfüm', 'ruj', 'fondöten'],
            'makyaj' => ['kozmetik', 'ruj', 'fondöten', 'göz farı', 'maskara', 'allık'],
            
            // Books & Hobbies
            'kitap' => ['roman', 'bilim', 'tarih', 'felsefe', 'psikoloji', 'roman'],
            'hobi' => ['oyuncak', 'lego', 'oyun', 'puzzle', 'sanat', 'müzik'],
            
            // Automotive
            'araba' => ['otomobil', 'araç', 'lastik', 'akü', 'motor yağı', 'bakım'],
            'lastik' => ['araba', 'otomobil', 'araç', 'tekerlek', 'kauçuk'],
            
            // Health
            'sağlık' => ['ilaç', 'vitamin', 'mineral', 'ağrı kesici', 'ateş düşürücü'],
            'vitamin' => ['sağlık', 'mineral', 'bağışıklık', 'enerji', 'beslenme'],
            
            // Garden & Tools
            'bahçe' => ['çiçek', 'bitki', 'ağaç', 'çim', 'tırmık', 'makas'],
            'el aleti' => ['matkap', 'tornavida', 'çekiç', 'pense', 'anahtar']
        ];
    }
    
    private function initializeContextualRules() {
        $this->contextualRules = [
            'color_preference' => [
                'keywords' => ['kırmızı', 'mavi', 'yeşil', 'sarı', 'turuncu', 'mor', 'pembe', 'siyah', 'beyaz', 'gri', 'kahverengi', 'lacivert', 'turkuaz', 'altın', 'gümüş'],
                'priority' => 2,
                'response_template' => 'Renk tercihinize göre ürünleri öneriyorum. {color} renkteki en kaliteli seçenekler:'
            ],
            'size_preference' => [
                'keywords' => ['küçük', 'orta', 'büyük', 'xs', 's', 'm', 'l', 'xl', 'xxl', 'küçük boy', 'büyük boy'],
                'priority' => 1,
                'response_template' => 'Boyut tercihinize göre ürünleri öneriyorum. {size} boyuttaki seçenekler:'
            ],
            'price_preference' => [
                'keywords' => ['ucuz', 'pahalı', 'ekonomik', 'premium', 'lüks', 'bütçe', 'indirimli', 'kampanyalı'],
                'priority' => 1,
                'response_template' => 'Fiyat tercihinize göre ürünleri öneriyorum. {price_type} fiyatlı seçenekler:'
            ],
            'brand_preference' => [
                'keywords' => ['apple', 'samsung', 'nike', 'adidas', 'sony', 'lg', 'dell', 'hp', 'lenovo'],
                'priority' => 1,
                'response_template' => 'Marka tercihinize göre ürünleri öneriyorum. {brand} markasının en iyi ürünleri:'
            ]
        ];
    }
    
    private function initializeColorKeywords() {
        $this->colorKeywords = [
            'kırmızı' => [
                'synonyms' => ['kırmızı', 'red', 'kızıl', 'al', 'kan rengi'],
                'hex_codes' => ['#FF0000', '#DC143C', '#B22222', '#8B0000'],
                'categories' => ['Telefon', 'Bilgisayar', 'Kulaklık', 'Spor Ayakkabı', 'Elbise', 'Çanta', 'Aksesuar'],
                'priority' => 3
            ],
            'mavi' => [
                'synonyms' => ['mavi', 'blue', 'lacivert', 'navy', 'gök mavisi'],
                'hex_codes' => ['#0000FF', '#000080', '#4169E1', '#1E90FF'],
                'categories' => ['Telefon', 'Bilgisayar', 'Kulaklık', 'Spor Ayakkabı', 'Elbise', 'Çanta', 'Aksesuar'],
                'priority' => 3
            ],
            'yeşil' => [
                'synonyms' => ['yeşil', 'green', 'açık yeşil', 'koyu yeşil', 'zümrüt'],
                'hex_codes' => ['#008000', '#228B22', '#32CD32', '#90EE90'],
                'categories' => ['Telefon', 'Bilgisayar', 'Kulaklık', 'Spor Ayakkabı', 'Elbise', 'Çanta', 'Aksesuar'],
                'priority' => 3
            ],
            'sarı' => [
                'synonyms' => ['sarı', 'yellow', 'altın sarısı', 'açık sarı'],
                'hex_codes' => ['#FFFF00', '#FFD700', '#FFA500', '#FF8C00'],
                'categories' => ['Telefon', 'Bilgisayar', 'Kulaklık', 'Spor Ayakkabı', 'Elbise', 'Çanta', 'Aksesuar'],
                'priority' => 2
            ],
            'turuncu' => [
                'synonyms' => ['turuncu', 'orange', 'portakal rengi', 'açık turuncu'],
                'hex_codes' => ['#FFA500', '#FF8C00', '#FF7F50', '#FF6347'],
                'categories' => ['Telefon', 'Bilgisayar', 'Kulaklık', 'Spor Ayakkabı', 'Elbise', 'Çanta', 'Aksesuar'],
                'priority' => 2
            ],
            'mor' => [
                'synonyms' => ['mor', 'purple', 'lila', 'eflatun', 'koyu mor'],
                'hex_codes' => ['#800080', '#9370DB', '#8A2BE2', '#9932CC'],
                'categories' => ['Telefon', 'Bilgisayar', 'Kulaklık', 'Spor Ayakkabı', 'Elbise', 'Çanta', 'Aksesuar'],
                'priority' => 2
            ],
            'pembe' => [
                'synonyms' => ['pembe', 'pink', 'açık pembe', 'fuchsia', 'magenta'],
                'hex_codes' => ['#FFC0CB', '#FF69B4', '#FF1493', '#DC143C'],
                'categories' => ['Telefon', 'Bilgisayar', 'Kulaklık', 'Spor Ayakkabı', 'Elbise', 'Çanta', 'Aksesuar'],
                'priority' => 2
            ],
            'siyah' => [
                'synonyms' => ['siyah', 'black', 'koyu', 'kara', 'ebony'],
                'hex_codes' => ['#000000', '#1C1C1C', '#2F2F2F', '#404040'],
                'categories' => ['Telefon', 'Bilgisayar', 'Kulaklık', 'Spor Ayakkabı', 'Elbise', 'Çanta', 'Aksesuar'],
                'priority' => 3
            ],
            'beyaz' => [
                'synonyms' => ['beyaz', 'white', 'açık', 'buz beyazı', 'fildişi'],
                'hex_codes' => ['#FFFFFF', '#F5F5F5', '#F0F0F0', '#E8E8E8'],
                'categories' => ['Telefon', 'Bilgisayar', 'Kulaklık', 'Spor Ayakkabı', 'Elbise', 'Çanta', 'Aksesuar'],
                'priority' => 3
            ],
            'gri' => [
                'synonyms' => ['gri', 'gray', 'gris', 'açık gri', 'koyu gri'],
                'hex_codes' => ['#808080', '#A9A9A9', '#C0C0C0', '#696969'],
                'categories' => ['Telefon', 'Bilgisayar', 'Kulaklık', 'Spor Ayakkabı', 'Elbise', 'Çanta', 'Aksesuar'],
                'priority' => 2
            ],
            'kahverengi' => [
                'synonyms' => ['kahverengi', 'brown', 'kahve', 'çikolata', 'bej'],
                'hex_codes' => ['#A52A2A', '#8B4513', '#D2691E', '#CD853F'],
                'categories' => ['Telefon', 'Bilgisayar', 'Kulaklık', 'Spor Ayakkabı', 'Elbise', 'Çanta', 'Aksesuar'],
                'priority' => 2
            ]
        ];
    }
    
    public function getSmartRecommendations($message) {
        $message = mb_strtolower($message, 'UTF-8');
        
        // Renk tercihi algıla
        $detectedColor = $this->detectColorFromMessage($message);
        
        // Kategori tespit et
        $detectedCategory = $this->detectCategoryFromMessage($message);
        
        // Fiyat tercihi algıla
        $detectedPrice = $this->detectPricePreference($message);
        
        // Marka tercihi algıla
        $detectedBrand = $this->detectBrandPreference($message);
        
        // Ürünleri filtrele
        $filteredProducts = $this->filterProductsByPreferences($detectedColor, $detectedCategory, $detectedPrice, $detectedBrand);
        
        if (empty($filteredProducts)) {
            return [
                'products' => [],
                'response' => 'Aradığınız kriterlere uygun ürün bulamadım. Farklı bir arama yapmayı deneyin.',
                'reason' => 'Arama sonucu bulunamadı',
                'category_matched' => $detectedCategory ? $detectedCategory['name'] : null,
                'total_found' => 0,
                'suggestions' => $this->generateSuggestions($detectedColor, $detectedCategory, $detectedPrice, $detectedBrand)
            ];
        }
        
        // Yanıt metni oluştur
        $response = $this->generateColorBasedResponse($detectedColor, $detectedCategory, $detectedPrice, $detectedBrand);
        
        return [
            'products' => array_slice($filteredProducts, 0, 5),
            'response' => $response,
            'reason' => $this->generateRecommendationReason($detectedColor, $detectedCategory, $detectedPrice, $detectedBrand),
            'category_matched' => $detectedCategory ? $detectedCategory['name'] : null,
            'total_found' => count($filteredProducts),
            'suggestions' => $this->generateSuggestions($detectedColor, $detectedCategory, $detectedPrice, $detectedBrand),
            'preferences' => [
                'color' => $detectedColor,
                'category' => $detectedCategory,
                'price' => $detectedPrice,
                'brand' => $detectedBrand
            ]
        ];
    }
    
    private function detectColorFromMessage($message) {
        foreach ($this->colorKeywords as $colorName => $colorData) {
            foreach ($colorData['synonyms'] as $synonym) {
                if (mb_strpos($message, $synonym) !== false) {
                    return [
                        'name' => $colorName,
                        'synonyms' => $colorData['synonyms'],
                        'hex_codes' => $colorData['hex_codes'],
                        'categories' => $colorData['categories'],
                        'priority' => $colorData['priority']
                    ];
                }
            }
        }
        return null;
    }
    
    private function detectCategoryFromMessage($message) {
        $bestMatch = null;
        $highestScore = 0;
        
        foreach ($this->categoryKeywords as $categoryKey => $categoryData) {
            $score = $this->calculateCategoryMatchScore($message, $categoryData['keywords']);
            
            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = [
                    'name' => $categoryKey,
                    'categories' => $categoryData['categories'],
                    'priority' => $categoryData['priority']
                ];
            }
        }
        
        // Eşik değeri kontrolü
        if ($highestScore >= 0.1) {
            return $bestMatch;
        }
        
        return null;
    }
    
    private function calculateCategoryMatchScore($message, $keywords) {
        $score = 0;
        $keywordCount = 0;
        $totalKeywords = count($keywords);
        
        // Doğrudan anahtar kelime eşleşmesi
        foreach ($keywords as $keyword) {
            if (mb_strpos($message, $keyword) !== false) {
                $score += 2.0;
                $keywordCount++;
            }
        }
        
        // Eşanlamlı kelimeleri kontrol et
        foreach ($this->productSynonyms as $mainWord => $synonyms) {
            if (mb_strpos($message, $mainWord) !== false) {
                foreach ($synonyms as $synonym) {
                    if (mb_strpos($message, $synonym) !== false) {
                        $score += 1.5;
                        $keywordCount++;
                    }
                }
            }
        }
        
        if ($keywordCount > 0) {
            $confidence = $score / $totalKeywords;
            return min($confidence, 1.0);
        }
        
        return 0;
    }
    
    private function detectPricePreference($message) {
        $priceKeywords = [
            'ucuz' => ['ucuz', 'ekonomik', 'uygun fiyat', 'düşük fiyat', 'bütçe'],
            'pahalı' => ['pahalı', 'premium', 'lüks', 'kaliteli', 'yüksek fiyat'],
            'orta' => ['orta', 'normal', 'makul', 'standart']
        ];
        
        foreach ($priceKeywords as $priceType => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_strpos($message, $keyword) !== false) {
                    return [
                        'type' => $priceType,
                        'keywords' => $keywords
                    ];
                }
            }
        }
        return null;
    }
    
    private function detectBrandPreference($message) {
        $brandKeywords = [
            'apple' => ['apple', 'iphone', 'macbook', 'ipad', 'mac'],
            'samsung' => ['samsung', 'galaxy', 'note', 'tab'],
            'nike' => ['nike', 'nike ayakkabı', 'nike spor'],
            'adidas' => ['adidas', 'adidas ayakkabı', 'adidas spor'],
            'sony' => ['sony', 'sony tv', 'sony kulaklık'],
            'lg' => ['lg', 'lg tv', 'lg telefon']
        ];
        
        foreach ($brandKeywords as $brandName => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_strpos($message, $keyword) !== false) {
                    return [
                        'name' => $brandName,
                        'keywords' => $keywords
                    ];
                }
            }
        }
        return null;
    }
    
    private function filterProductsByPreferences($color, $category, $price, $brand) {
        $allProducts = $this->productData->getAllProducts();
        $filteredProducts = [];
        
        foreach ($allProducts as $product) {
            $score = 0;
            
            // Renk skoru (en yüksek öncelik)
            if ($color) {
                $score += $this->calculateColorScore($product, $color);
            }
            
            // Kategori skoru
            if ($category && in_array($product['category'], $category['categories'])) {
                $score += 2;
            }
            
            // Marka skoru
            if ($brand && mb_strtolower($product['brand']) === mb_strtolower($brand['name'])) {
                $score += 2;
            }
            
            // Fiyat skoru
            if ($price) {
                $score += $this->calculatePriceScore($product, $price);
            }
            
            // Genel kalite skoru
            $score += $product['rating'] * 0.5;
            
            if ($score > 0) {
                $product['_score'] = $score;
                $filteredProducts[] = $product;
            }
        }
        
        // Skora göre sırala
        usort($filteredProducts, function($a, $b) {
            return $b['_score'] <=> $a['_score'];
        });
        
        return $filteredProducts;
    }
    
    private function calculateColorScore($product, $color) {
        // Renk önceliğine göre skor ver
        $baseScore = $color['priority'];
        
        // Ürün kategorisi renk kategorilerinde varsa bonus puan
        if (in_array($product['category'], $color['categories'])) {
            $baseScore += 1;
        }
        
        // Ürün adında renk geçiyorsa bonus puan
        $productName = mb_strtolower($product['name']);
        foreach ($color['synonyms'] as $synonym) {
            if (mb_strpos($productName, $synonym) !== false) {
                $baseScore += 2;
                break;
            }
        }
        
        return $baseScore;
    }
    
    private function calculatePriceScore($product, $price) {
        $productPrice = $product['price'];
        
        switch ($price['type']) {
            case 'ucuz':
                return $productPrice < 1000 ? 3 : ($productPrice < 5000 ? 1 : 0);
            case 'pahalı':
                return $productPrice > 10000 ? 3 : ($productPrice > 5000 ? 1 : 0);
            case 'orta':
                return ($productPrice >= 1000 && $productPrice <= 10000) ? 2 : 0;
            default:
                return 0;
        }
    }
    
    private function generateColorBasedResponse($color, $category, $price, $brand) {
        $response = '';
        
        if ($color) {
            $response .= "🎨 {$color['name']} renkteki ";
        }
        
        if ($category) {
            $response .= "{$category['name']} kategorisinde ";
        }
        
        if ($brand) {
            $response .= "{$brand['name']} markasının ";
        }
        
        if ($price) {
            $response .= "{$price['type']} fiyatlı ";
        }
        
        $response .= "en kaliteli ürünleri öneriyorum. İşte harika seçenekler:";
        
        return $response;
    }
    
    private function generateRecommendationReason($color, $category, $price, $brand) {
        $reasons = [];
        
        if ($color) {
            $reasons[] = "Renk: {$color['name']}";
        }
        
        if ($category) {
            $reasons[] = "Kategori: {$category['name']}";
        }
        
        if ($brand) {
            $reasons[] = "Marka: {$brand['name']}";
        }
        
        if ($price) {
            $reasons[] = "Fiyat: {$price['type']}";
        }
        
        return implode(', ', $reasons);
    }
    
    private function generateSuggestions($color, $category, $price, $brand) {
        $suggestions = [];
        
        if ($color) {
            $suggestions[] = "Farklı renk seçenekleri";
        }
        
        if ($category) {
            $suggestions[] = "Farklı kategori öner";
        }
        
        if ($price) {
            $suggestions[] = "Fiyat aralığını değiştir";
        }
        
        if ($brand) {
            $suggestions[] = "Farklı marka öner";
        }
        
        if (empty($suggestions)) {
            $suggestions = ["Farklı kategori öner", "Fiyat aralığı belirle", "Marka önerisi al"];
        }
        
        return $suggestions;
    }
}

