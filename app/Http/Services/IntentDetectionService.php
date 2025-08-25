<?php

namespace App\Http\Services;

use App\Http\Services\ProductData;
use App\Http\Services\SmartProductRecommenderService;
use Illuminate\Support\Facades\Log;

class IntentDetectionService {
    private $intents = [];
    private $thesaurus = [];
    private $productData;
    private $aiIntentCache = [];
    private $unknownIntentThreshold = 0.15;
    
    public function __construct() {
        $this->productData = new ProductData();
        $this->initializeIntents();
        $this->initializeThesaurus();
        $this->loadAIIntentCache();
    }
    
    /**
     * AI-powered intent detection for unknown words/phrases
     */
    public function detectIntentWithAI($message) {
        // Önce mevcut sistemle dene
        $detectedIntent = $this->detectIntent($message);
        
        // Eğer intent bulunamadıysa veya confidence çok düşükse AI kullan
        if ($detectedIntent['intent'] === 'unknown' || $detectedIntent['confidence'] < $this->unknownIntentThreshold) {
            $aiIntent = $this->analyzeIntentWithAI($message);
            
            if ($aiIntent && $aiIntent['confidence'] > 0.6) {
                // AI'dan gelen intent'i sisteme ekle
                $this->addDynamicIntent($aiIntent);
                
                // Cache'e kaydet
                $this->saveAIIntentCache();
                
                return [
                    'intent' => $aiIntent['intent'],
                    'confidence' => $aiIntent['confidence'],
                    'message' => $message,
                    'threshold_met' => true,
                    'ai_generated' => true,
                    'new_keywords' => $aiIntent['keywords'],
                    'closest_intent' => $aiIntent['intent']
                ];
            }
        }
        
        return $detectedIntent;
    }
    
    /**
     * AI ile intent analizi yap
     */
    private function analyzeIntentWithAI($message) {
        try {
            // AI prompt'u hazırla
            $prompt = $this->generateIntentAnalysisPrompt($message);
            
            // AI'dan yanıt al (burada gerçek AI API'si kullanılacak)
            $aiResponse = $this->callAIForIntentAnalysis($prompt);
            
            if ($aiResponse && isset($aiResponse['intent'])) {
                return [
                    'intent' => $aiResponse['intent'],
                    'confidence' => $aiResponse['confidence'],
                    'keywords' => $aiResponse['keywords'] ?? [$message],
                    'response' => $aiResponse['response'] ?? $this->getDefaultResponseForIntent($aiResponse['intent']),
                    'confidence_threshold' => 0.25,
                    'ai_analyzed' => true
                ];
            }
        } catch (\Exception $e) {
            Log::error('AI intent analysis error: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Intent analizi için AI prompt'u oluştur
     */
    private function generateIntentAnalysisPrompt($message) {
        $existingIntents = array_keys($this->intents);
        $existingKeywords = [];
        
        foreach ($this->intents as $intent => $data) {
            $existingKeywords[$intent] = $data['keywords'];
        }
        
        $prompt = "Aşağıdaki Kullanıcının diliyle yazılan mesajı analiz et ve hangi intent'e ait olduğunu belirle:\n\n";
        $prompt .= "Mesaj: \"{$message}\"\n\n";
        $prompt .= "Mevcut intent'ler ve anahtar kelimeleri:\n";
        
        foreach ($existingIntents as $intent) {
            $keywords = implode(', ', $existingKeywords[$intent]);
            $prompt .= "- {$intent}: {$keywords}\n";
        }
        
        $prompt .= "\nLütfen aşağıdaki JSON formatında yanıt ver:\n";
        $prompt .= "{\n";
        $prompt .= "  \"intent\": \"intent_adı\",\n";
        $prompt .= "  \"confidence\": 0.85,\n";
        $prompt .= "  \"keywords\": [\"yeni_anahtar_kelime1\", \"yeni_anahtar_kelime2\"],\n";
        $prompt .= "  \"response\": \"Yanıt metni\",\n";
        $prompt .= "  \"reasoning\": \"Neden bu intent seçildi\"\n";
        $prompt .= "}\n\n";
        $prompt .= "Eğer hiçbir intent'e uymuyorsa 'unknown' olarak işaretle.";
        
        return $prompt;
    }
    
    /**
     * AI API'sini çağır (şimdilik simüle ediyoruz)
     */
    private function callAIForIntentAnalysis($prompt) {
        // Burada gerçek AI API'si kullanılacak (OpenAI, Claude, vb.)
        // Şimdilik simüle ediyoruz
        
        $message = mb_strtolower($prompt, 'UTF-8');
        
        // Basit AI simülasyonu - gerçek implementasyonda bu kısım değişecek
        if (mb_strpos($message, 'kırmızı') !== false || mb_strpos($message, 'renk') !== false) {
            return [
                'intent' => 'color_preference',
                'confidence' => 0.9,
                'keywords' => ['renk', 'kırmızı', 'renkli'],
                'response' => 'Renk tercihinize göre ürünleri öneriyorum.',
                'reasoning' => 'Mesajda renk tercihi belirtilmiş'
            ];
        }
        
        if (mb_strpos($message, 'ucuz') !== false || mb_strpos($message, 'ekonomik') !== false) {
            return [
                'intent' => 'price_preference',
                'confidence' => 0.85,
                'keywords' => ['ucuz', 'ekonomik', 'uygun fiyat'],
                'response' => 'Ekonomik fiyatlı ürünleri öneriyorum.',
                'reasoning' => 'Mesajda fiyat tercihi belirtilmiş'
            ];
        }
        
        if (mb_strpos($message, 'yeni') !== false || mb_strpos($message, 'güncel') !== false) {
            return [
                'intent' => 'trend_products',
                'confidence' => 0.8,
                'keywords' => ['yeni', 'güncel', 'trend', 'popüler'],
                'response' => 'En yeni ve trend ürünleri öneriyorum.',
                'reasoning' => 'Mesajda yenilik/güncellik aranıyor'
            ];
        }
        
        return null;
    }
    
    /**
     * AI'dan gelen intent'i sisteme dinamik olarak ekle
     */
    private function addDynamicIntent($aiIntent) {
        $intentName = $aiIntent['intent'];
        
        // Eğer intent zaten varsa, sadece yeni keywords ekle
        if (isset($this->intents[$intentName])) {
            $existingKeywords = $this->intents[$intentName]['keywords'];
            $newKeywords = array_diff($aiIntent['keywords'], $existingKeywords);
            
            if (!empty($newKeywords)) {
                $this->intents[$intentName]['keywords'] = array_merge($existingKeywords, $newKeywords);
                
                // Thesaurus'u da güncelle
                foreach ($newKeywords as $keyword) {
                    $this->thesaurus[$keyword] = $existingKeywords;
                }
                
                Log::info("Added new keywords to existing intent '{$intentName}': " . implode(', ', $newKeywords));
            }
        } else {
            // Yeni intent oluştur
            $this->intents[$intentName] = [
                'keywords' => $aiIntent['keywords'],
                'response' => $aiIntent['response'],
                'confidence_threshold' => $aiIntent['confidence_threshold'] ?? 0.25,
                'ai_generated' => true,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Thesaurus'a ekle
            foreach ($aiIntent['keywords'] as $keyword) {
                $this->thesaurus[$keyword] = $aiIntent['keywords'];
            }
            
            Log::info("Created new AI-generated intent '{$intentName}' with keywords: " . implode(', ', $aiIntent['keywords']));
        }
        
        // Cache'e kaydet
        $this->aiIntentCache[$intentName] = [
            'keywords' => $aiIntent['keywords'],
            'response' => $aiIntent['response'],
            'confidence' => $aiIntent['confidence'],
            'created_at' => date('Y-m-d H:i:s'),
            'usage_count' => 0
        ];
    }
    
    /**
     * AI intent cache'ini yükle
     */
    private function loadAIIntentCache() {
        $cacheFile = storage_path('app/ai_intent_cache.json');
        
        if (file_exists($cacheFile)) {
            try {
                $cacheData = json_decode(file_get_contents($cacheFile), true);
                if ($cacheData) {
                    $this->aiIntentCache = $cacheData;
                    
                    // Cache'deki intent'leri sisteme yükle
                    foreach ($this->aiIntentCache as $intentName => $intentData) {
                        if (!isset($this->intents[$intentName])) {
                            $this->intents[$intentName] = [
                                'keywords' => $intentData['keywords'],
                                'response' => $intentData['response'],
                                'confidence_threshold' => 0.25,
                                'ai_generated' => true,
                                'created_at' => $intentData['created_at']
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('AI intent cache load error: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * AI intent cache'ini kaydet
     */
    private function saveAIIntentCache() {
        $cacheFile = storage_path('app/ai_intent_cache.json');
        
        try {
            // Kullanım sayısını artır
            foreach ($this->aiIntentCache as $intentName => &$intentData) {
                if (isset($this->intents[$intentName])) {
                    $intentData['usage_count']++;
                    $intentData['last_used'] = date('Y-m-d H:i:s');
                }
            }
            
            file_put_contents($cacheFile, json_encode($this->aiIntentCache, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Exception $e) {
            Log::error('AI intent cache save error: ' . $e->getMessage());
        }
    }
    
    /**
     * Intent için varsayılan yanıt al
     */
    private function getDefaultResponseForIntent($intentName) {
        $defaultResponses = [
            'color_preference' => 'Renk tercihinize göre ürünleri öneriyorum.',
            'price_preference' => 'Fiyat tercihinize göre ürünleri öneriyorum.',
            'trend_products' => 'En yeni ve trend ürünleri öneriyorum.',
            'size_preference' => 'Boyut tercihinize göre ürünleri öneriyorum.',
            'quality_preference' => 'Kalite tercihinize göre ürünleri öneriyorum.'
        ];
        
        return $defaultResponses[$intentName] ?? 'Size yardımcı olmaya çalışıyorum.';
    }
    
    /**
     * AI-generated intent'leri listele
     */
    public function getAIGeneratedIntents() {
        $aiIntents = [];
        
        foreach ($this->intents as $intentName => $intentData) {
            if (isset($intentData['ai_generated']) && $intentData['ai_generated']) {
                $aiIntents[$intentName] = [
                    'keywords' => $intentData['keywords'],
                    'response' => $intentData['response'],
                    'created_at' => $intentData['created_at'],
                    'usage_count' => $this->aiIntentCache[$intentName]['usage_count'] ?? 0
                ];
            }
        }
        
        return $aiIntents;
    }
    
    /**
     * Intent için threshold değerini al
     */
    public function getIntentThreshold($intentName) {
        if (isset($this->intents[$intentName])) {
            return $this->intents[$intentName]['confidence_threshold'];
        }
        
        return 0.25; // Varsayılan threshold
    }
    
    /**
     * Tüm intent'leri al
     */
    public function getAllIntents() {
        return $this->intents;
    }

    /**
     * AI ile akıllı ürün önerisi al
     */
    public function getSmartRecommendations($message) {
        try {
            // SmartProductRecommenderService kullan
            $productData = new \App\Http\Services\ProductData();
            $smartRecommender = new \App\Http\Services\SmartProductRecommenderService($productData);
            
            $recommendations = $smartRecommender->getSmartRecommendations($message);
            
            if ($recommendations && !empty($recommendations['products'])) {
                return $recommendations;
            }
            
            // AI önerisi yoksa veritabanından rastgele ürünler çek
            $products = \App\Models\Product::inRandomOrder()->limit(6)->get()->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'price' => $product->price,
                    'image' => $product->image ?: '/imgs/smartwatch.jpeg',
                    'category' => $product->category,
                    'rating' => $product->rating ?: 4.0
                ];
            })->toArray();
            
            return [
                'products' => $products,
                'response' => 'Size özel ürün önerileri',
                'reason' => 'Veritabanından seçilen ürünler',
                'suggestions' => ['Daha fazla ürün göster', 'Fiyat bilgisi', 'Teknik özellikler', 'Kategori değiştir']
            ];
            
        } catch (\Exception $e) {
            \Log::error('Smart recommendations error: ' . $e->getMessage());
            
            // Hata durumunda fallback
            return [
                'products' => $products,
                'response' => 'Ürün önerisi yapılamadı',
                'reason' => 'Sistem hatası',
                'suggestions' => ['Tekrar dene', 'Yardım al']
            ];
        }
    }
    
    private function initializeIntents() {
        $this->intents = [
            'greeting' => [
                'keywords' => ['merhaba', 'selam', 'hey', 'hi', 'hello', 'günaydın', 'iyi günler', 'iyi akşamlar', 'iyi geceler'],
                'response' => 'Merhaba! Size nasıl yardımcı olabilirim? Ürün arama, fiyat sorgulama veya genel bilgi için yardımcı olabilirim.',
                'confidence_threshold' => 0.3
            ],
            'product_search' => [
                'keywords' => ['ara', 'bul', 'hangi', 'nerede', 'var mı', 'göster', 'listele', 'ürün', 'ne var', 'bulabilir miyim'],
                'response' => 'Ürün arama yapıyorum. Hangi kategoride veya markada ürün arıyorsunuz?',
                'confidence_threshold' => 0.25
            ],
            'price_inquiry' => [
                'keywords' => ['fiyat', 'kaç para', 'ne kadar', 'ücret', 'bedel', 'maliyet', 'para', 'tl', 'lira', 'kuruş'],
                'response' => 'Fiyat bilgisi için hangi ürünü öğrenmek istiyorsunuz?',
                'confidence_threshold' => 0.25
            ],
            'category_browse' => [
                'keywords' => ['kategori', 'elektronik', 'giyim', 'ev', 'spor', 'kozmetik', 'kitap', 'otomotiv', 'sağlık', 'bahçe', 'pet', 'tür', 'çeşit', 'göster', 'öner', 'listele', 'kategorileri'],
                'response' => 'Kategoriye göre ürünleri listeliyorum. Hangi kategoriyi detaylı görmek istiyorsunuz?',
                'confidence_threshold' => 0.3
            ],
            'brand_search' => [
                'keywords' => ['apple', 'samsung', 'nike', 'adidas', 'ikea', 'bosch', 'sony', 'lg', 'dell', 'hp', 'lenovo', 'marka', 'firma'],
                'response' => 'Marka bazlı ürün arama yapıyorum. Hangi markanın ürünlerini görmek istiyorsunuz?',
                'confidence_threshold' => 0.25
            ],
            'stock_inquiry' => [
                'keywords' => ['stok', 'mevcut', 'var mı', 'bulunuyor mu', 'tükenmiş', 'kalmış', 'elde', 'depoda', 'mağazada'],
                'response' => 'Stok durumu için hangi ürünü kontrol etmemi istiyorsunuz?',
                'confidence_threshold' => 0.25
            ],

            'comparison' => [
                'keywords' => ['karşılaştır', 'hangi daha iyi', 'fark', 'benzer', 'aynı', 'vs', 'veya', 'ya da', 'hangisi'],
                'response' => 'Ürün karşılaştırması yapıyorum. Hangi ürünleri karşılaştırmak istiyorsunuz?',
                'confidence_threshold' => 0.25
            ],
            'help' => [
                'keywords' => ['yardım', 'nasıl', 'ne yapabilir', 'destek', 'bilgi', 'açıkla', 'öğren', 'bilmiyorum', 'kafam karıştı'],
                'response' => 'Size yardımcı olabilirim! Ürün arama, fiyat sorgulama, kategori tarama, marka arama, stok kontrolü, sipariş sorgulama ve ürün önerileri sunabilirim. Ne yapmak istiyorsunuz?',
                'confidence_threshold' => 0.3
            ],
            'goodbye' => [
                'keywords' => ['güle güle', 'hoşça kal', 'görüşürüz', 'bye', 'çıkış', 'kapat', 'teşekkür', 'sağol', 'tamam'],
                'response' => 'Görüşmek üzere! Başka bir sorunuz olursa yardımcı olmaktan mutluluk duyarım.',
                'confidence_threshold' => 0.3
            ],
            'cart_add' => [
                'keywords' => ['sepete ekle', 'sepete ekler misin', 'ekle', 'ekler misin', 'sepete koy', 'sepete koyar mısın', 'koy', 'koyar mısın', 'ürün ekle', 'ürün koy', 'cart', 'basket'],
                'response' => 'Ürünü sepete ekliyorum. Hangi ürünü eklemek istiyorsunuz?',
                'confidence_threshold' => 0.25
            ],
            'cart_remove' => [
                'keywords' => ['sepetten çıkar', 'sepetten çıkarır mısın', 'çıkar', 'çıkarır mısın', 'sepetten sil', 'sepetten siler misin', 'sil', 'siler misin', 'ürün çıkar', 'ürün sil'],
                'response' => 'Ürünü sepetten çıkarıyorum. Hangi ürünü çıkarmak istiyorsunuz?',
                'confidence_threshold' => 0.25
            ],
            'cart_view' => [
                'keywords' => ['sepetim', 'sepetimi göster', 'sepetimi aç', 'sepetimde ne var', 'sepetimdeki ürünler', 'cart', 'basket', 'sepet', 'sepeti göster'],
                'response' => 'Sepetinizdeki ürünleri gösteriyorum.',
                'confidence_threshold' => 0.25
            ],
            'order_inquiry' => [
                'keywords' => ['sipariş', 'siparişim', 'sipariş durumu', 'sipariş takibi', 'sipariş nerede', 'sipariş bilgisi', 'sipariş sorgula', 'sipariş numarası', 'sipariş geçmişi', 'sipariş durumumu öğren', 'siparişim nerede', 'öner', 'tavsiye', 'en iyi', 'popüler', 'trend', 'yeni', 'güncel', 'öneri', 'tavsiye et', 'ne alayım'],
                'response' => 'Sipariş durumunuzu kontrol ediyorum veya size ürün önerileri sunuyorum. Sipariş numaranızı girebilir veya hangi kategoride öneri istediğinizi belirtebilirsiniz.',
                'confidence_threshold' => 0.25
            ],
            'cargo_tracking' => [
                'keywords' => ['kargo', 'kargom', 'kargom nerede', 'kargo takip', 'kargo durumu', 'kargo numarası', 'kargo firması', 'kargo takip numarası', 'kargo bilgisi', 'kargo sorgula'],
                'response' => 'Kargo takip numaranızı girerek kargo durumunuzu öğrenebilirsiniz.',
                'confidence_threshold' => 0.25
            ],
            'campaign_inquiry' => [
                'keywords' => ['kampanya', 'kampanyalar', 'kampanyalarda', 'indirim', 'fırsat', 'bedava', 'ücretsiz', 'taksit', 'promosyon', 'teklif', 'özel', 'avantaj'],
                'response' => 'Aktif kampanyalarımızı listeliyorum. Size özel fırsatları kaçırmayın!',
                'confidence_threshold' => 0.25
            ]
        ];
    }
    
    private function initializeThesaurus() {
        $this->thesaurus = [
            // Ürün arama eşanlamlıları
            'ara' => ['bul', 'hangi', 'nerede', 'var mı', 'göster', 'listele', 'ürün', 'ne var', 'bulabilir miyim', 'istiyorum', 'arıyorum'],
            'bul' => ['ara', 'hangi', 'nerede', 'var mı', 'göster', 'listele', 'ürün', 'ne var', 'bulabilir miyim'],
            'göster' => ['ara', 'bul', 'hangi', 'nerede', 'var mı', 'listele', 'ürün', 'ne var'],
            'listele' => ['ara', 'bul', 'hangi', 'nerede', 'var mı', 'göster', 'ürün', 'ne var'],
            
            // Fiyat eşanlamlıları
            'fiyat' => ['kaç para', 'ne kadar', 'ücret', 'bedel', 'maliyet', 'para', 'tl', 'lira', 'kuruş', 'pahalı', 'ucuz'],
            'kaç para' => ['fiyat', 'ne kadar', 'ücret', 'bedel', 'maliyet', 'para', 'tl', 'lira'],
            'ne kadar' => ['fiyat', 'kaç para', 'ücret', 'bedel', 'maliyet', 'para', 'tl'],
            
            // Kategori eşanlamlıları
            'kategori' => ['tür', 'çeşit', 'bölüm', 'alan', 'sektör', 'tip', 'sınıf'],
            'elektronik' => ['elektrik', 'teknoloji', 'tech', 'digital', 'akıllı'],
            'giyim' => ['kıyafet', 'elbise', 'moda', 'textil', 'konfeksiyon'],
            'ev' => ['ev eşyası', 'mobilya', 'dekorasyon', 'yaşam', 'household'],
            'spor' => ['spor malzemesi', 'fitness', 'egzersiz', 'atletik'],
            
            // Marka eşanlamlıları
            'marka' => ['firma', 'şirket', 'brand', 'üretici', 'yapımcı'],
            'apple' => ['iphone', 'mac', 'ipad', 'macbook', 'airpods'],
            'samsung' => ['galaxy', 'note', 'tab', 'samsung telefon'],
            'nike' => ['nike ayakkabı', 'nike spor', 'nike giyim'],
            'adidas' => ['adidas ayakkabı', 'adidas spor', 'adidas giyim'],
            
            // Stok eşanlamlıları
            'stok' => ['mevcut', 'var mı', 'bulunuyor mu', 'elde', 'depoda', 'mağazada', 'kalmış'],
            'mevcut' => ['stok', 'var mı', 'bulunuyor mu', 'elde', 'depoda'],
            'var mı' => ['stok', 'mevcut', 'bulunuyor mu', 'elde', 'depoda'],
            
            // Öneri eşanlamlıları
            'öner' => ['tavsiye', 'öneri', 'tavsiye et', 'ne alayım', 'hangisini alayım', 'en iyi'],
            
            // Sepet eşanlamlıları
            'sepete ekle' => ['ekle', 'ekler misin', 'sepete koy', 'sepete koyar mısın', 'koy', 'koyar mısın', 'ürün ekle', 'ürün koy', 'cart', 'basket'],
            'ekle' => ['sepete ekle', 'sepete ekler misin', 'sepete koy', 'sepete koyar mısın', 'koy', 'koyar mısın', 'ürün ekle', 'ürün koy'],
            'sepete koy' => ['sepete ekle', 'ekle', 'ekler misin', 'koy', 'koyar mısın', 'ürün ekle', 'ürün koy'],
            'koy' => ['sepete ekle', 'ekle', 'ekler misin', 'sepete koy', 'sepete koyar mısın', 'ürün ekle', 'ürün koy'],
            'sepet' => ['cart', 'basket', 'sepetim', 'sepetimi göster', 'sepetimi aç'],
            'sepetim' => ['sepet', 'cart', 'basket', 'sepetimi göster', 'sepetimi aç', 'sepetimde ne var']
        ];
    }
    
    public function detectIntent($message) {
        $message = mb_strtolower(trim($message), 'UTF-8');
        $bestIntent = null;
        $highestConfidence = 0;
        
        // Check for order tracking keywords first (special case)
        $orderTrackingKeywords = ['siparişim nerede', 'sipariş durumu', 'sipariş takibi', 'sipariş nerede'];
        foreach ($orderTrackingKeywords as $keyword) {
            if (mb_strpos($message, $keyword) !== false) {
                return [
                    'intent' => 'order_tracking',
                    'confidence' => 0.95,
                    'message' => $message,
                    'threshold_met' => true
                ];
            }
        }
        
        // Her niyet için güvenilirlik hesapla
        foreach ($this->intents as $intentName => $intentData) {
            $confidence = $this->calculateAdvancedConfidence($message, $intentData['keywords']);
            
            if ($confidence > $highestConfidence) {
                $highestConfidence = $confidence;
                $bestIntent = $intentName;
            }
        }
        
        // Eşik değeri kontrolü - daha esnek
        $threshold = $this->intents[$bestIntent]['confidence_threshold'] ?? 0.25;
        
        if ($highestConfidence >= $threshold) {
            return [
                'intent' => $bestIntent,
                'confidence' => $highestConfidence,
                'message' => $message,
                'threshold_met' => true
            ];
        } else {
            // Eşik değeri karşılanmazsa, en yakın niyeti döndür ama düşük güvenilirlikle
            return [
                'intent' => $bestIntent ?? 'unknown',
                'confidence' => $highestConfidence,
                'message' => $message,
                'threshold_met' => false,
                'closest_intent' => $bestIntent
            ];
        }
    }
    
    private function calculateAdvancedConfidence($message, $keywords) {
        $confidence = 0;
        $keywordCount = 0;
        $totalKeywords = count($keywords);
        
        // Doğrudan anahtar kelime eşleşmesi
        foreach ($keywords as $keyword) {
            if (mb_strpos($message, $keyword) !== false) {
                $confidence += 1.0;
                $keywordCount++;
            }
        }
        
        // Thesaurus ile genişletilmiş arama
        foreach ($this->thesaurus as $mainWord => $synonyms) {
            if (mb_strpos($message, $mainWord) !== false) {
                foreach ($synonyms as $synonym) {
                    if (mb_strpos($message, $synonym) !== false) {
                        $confidence += 0.8; // Eşanlamlı kelime bonusu
                        $keywordCount++;
                    }
                }
            }
        }
        
        // Kısmi kelime eşleşmesi (fuzzy matching)
        $words = explode(' ', $message);
        foreach ($words as $word) {
            foreach ($keywords as $keyword) {
                $similarity = $this->calculateSimilarity($word, $keyword);
                if ($similarity > 0.7) { // %70 benzerlik eşiği
                    $confidence += $similarity * 0.6;
                    $keywordCount++;
                }
            }
        }
        
        // Mesaj uzunluğu ve anahtar kelime yoğunluğu
        if ($keywordCount > 0) {
            $baseConfidence = $confidence / $totalKeywords;
            $densityBonus = min($keywordCount / count(explode(' ', $message)), 1.0) * 0.2;
            $confidence = $baseConfidence + $densityBonus;
        }
        
        return min($confidence, 1.0); // Maksimum 1.0
    }
    
    private function calculateSimilarity($str1, $str2) {
        $str1 = mb_strtolower($str1, 'UTF-8');
        $str2 = mb_strtolower($str2, 'UTF-8');
        
        // Levenshtein mesafesi hesapla
        $lev = levenshtein($str1, $str2);
        $maxLen = max(mb_strlen($str1), mb_strlen($str2));
        
        if ($maxLen === 0) return 1.0;
        
        return 1 - ($lev / $maxLen);
    }
    
    public function generateResponse($detectedIntent, $originalMessage) {
        $intent = $detectedIntent['intent'];
        $confidence = $detectedIntent['confidence'];
        $thresholdMet = $detectedIntent['threshold_met'] ?? true;
        
        // Eşik değeri karşılanmazsa ama en yakın niyet varsa, o niyeti kullan
        if (!$thresholdMet && $detectedIntent['closest_intent'] && $confidence > 0.15) {
            $intent = $detectedIntent['closest_intent'];
            $thresholdMet = true;
        }
        
        // Bilinmeyen niyet durumunda akıllı tahmin yap
        if ($intent === 'unknown' || !$thresholdMet) {
            return $this->handleUnknownIntent($originalMessage, $confidence);
        }
        
        // Niyete göre özel yanıtlar
        switch ($intent) {
            case 'product_search':
                return $this->handleProductSearch($originalMessage);
                
            case 'price_inquiry':
                return $this->handlePriceInquiry($originalMessage);
                
            case 'category_browse':
                return $this->handleCategoryBrowse($originalMessage);
                
            case 'brand_search':
                return $this->handleBrandSearch($originalMessage);
                
            case 'stock_inquiry':
                return $this->handleStockInquiry($originalMessage);
                
            case 'recommendation':
                // Redirect to order_inquiry since recommendation is now part of it
                return $this->handleOrderInquiry($originalMessage);
                
            case 'comparison':
                return $this->handleComparison($originalMessage);
                
            case 'cart_add':
                return $this->handleCartAdd($originalMessage);
                
            case 'cart_remove':
                return $this->handleCartRemove($originalMessage);
                
            case 'cart_view':
                return $this->handleCartView($originalMessage);
                
            case 'order_inquiry':
                return $this->handleOrderInquiry($originalMessage);
                
            case 'order_tracking':
                return $this->handleOrderTracking($originalMessage);
                
            default:
                return [
                    'intent' => $intent,
                    'confidence' => $confidence,
                    'response' => $this->intents[$intent]['response'],
                    'products' => null,
                    'suggestions' => $this->getSuggestionsForIntent($intent),
                    'threshold_met' => $thresholdMet
                ];
        }
    }
    
    public function generateResponseWithContext($detectedIntent, $originalMessage, $chatSession) {
        $intent = $detectedIntent['intent'];
        $confidence = $detectedIntent['confidence'];
        $thresholdMet = $detectedIntent['threshold_met'] ?? true;
        
        // Session context'i kontrol et
        $context = $chatSession->getConversationContext();
        $lastIntent = $chatSession->getLastIntent();
        $lastProducts = $chatSession->getLastProducts();
        
        // Context-aware yanıt oluştur
        if ($intent === 'product_search' && $context['current_category']) {
            return $this->handleContextualProductSearch($originalMessage, $context);
        }
        
        if ($intent === 'order_inquiry' && $context['current_category']) {
            // Check if this is a recommendation request
            $message = mb_strtolower($originalMessage, 'UTF-8');
            $recommendationKeywords = ['öner', 'tavsiye', 'en iyi', 'popüler', 'trend', 'yeni', 'güncel', 'öneri', 'tavsiye et', 'ne alayım'];
            $isRecommendationRequest = false;
            
            foreach ($recommendationKeywords as $keyword) {
                if (mb_strpos($message, $keyword) !== false) {
                    $isRecommendationRequest = true;
                    break;
                }
            }
            
            if ($isRecommendationRequest) {
                return $this->handleContextualRecommendation($originalMessage, $context);
            }
        }
        
        if ($intent === 'comparison' && !empty($lastProducts)) {
            return $this->handleContextualComparison($originalMessage, $context, $lastProducts);
        }
        
        // Normal yanıt oluştur
        return $this->generateResponse($detectedIntent, $originalMessage);
    }
    
    private function handleUnknownIntent($message, $confidence) {
        // Mesaj içeriğine göre akıllı tahmin yap
        $suggestedIntent = $this->suggestIntentFromContext($message);
        
        if ($suggestedIntent) {
            return [
                'intent' => 'suggested_' . $suggestedIntent,
                'confidence' => $confidence,
                'response' => 'Mesajınızı tam anlayamadım ama muhtemelen ' . $this->getIntentDescription($suggestedIntent) . ' istiyorsunuz. Size yardımcı olayım mı?',
                'products' => null,
                'suggestions' => $this->getSuggestionsForIntent($suggestedIntent),
                'suggested_intent' => $suggestedIntent,
                'help_text' => 'Daha net yazarsanız size daha iyi yardımcı olabilirim.'
            ];
        }
        
        return [
            'intent' => 'unknown',
            'confidence' => $confidence,
            'response' => 'Mesajınızı tam olarak anlayamadım. Size nasıl yardımcı olabilirim?',
            'products' => null,
            'suggestions' => ['Ürün ara', 'Kategori göster', 'Fiyat öğren', 'Yardım al'],
            'help_text' => 'Örnek: "iPhone ara", "Elektronik kategorisi", "Nike fiyatı" gibi yazabilirsiniz.'
        ];
    }
    
    private function suggestIntentFromContext($message) {
        $message = mb_strtolower($message, 'UTF-8');
        
        // Ürün isimleri varsa arama niyeti
        $productKeywords = ['iphone', 'samsung', 'nike', 'adidas', 'apple', 'macbook', 'ipad', 'playstation', 'xbox'];
        foreach ($productKeywords as $product) {
            if (mb_strpos($message, $product) !== false) {
                return 'product_search';
            }
        }
        
        // Fiyat kelimeleri varsa fiyat sorgulama
        $priceKeywords = ['para', 'tl', 'lira', 'kuruş', 'pahalı', 'ucuz'];
        foreach ($priceKeywords as $price) {
            if (mb_strpos($message, $price) !== false) {
                return 'price_inquiry';
            }
        }
        
        // Kategori kelimeleri varsa kategori tarama
        $categoryKeywords = ['elektronik', 'giyim', 'ev', 'spor', 'kitap'];
        foreach ($categoryKeywords as $category) {
            if (mb_strpos($message, $category) !== false) {
                return 'category_browse';
            }
        }
        
        return null;
    }
    
    private function getIntentDescription($intent) {
        $descriptions = [
            'product_search' => 'ürün arama',
            'price_inquiry' => 'fiyat sorgulama',
            'category_browse' => 'kategori tarama',
            'brand_search' => 'marka arama',
            'stock_inquiry' => 'stok sorgulama',
            'order_inquiry' => 'sipariş sorgulama ve ürün önerisi',
            'comparison' => 'ürün karşılaştırma'
        ];
        
        return $descriptions[$intent] ?? 'yardım';
    }
    
    private function getSuggestionsForIntent($intent) {
        $suggestions = [
            'greeting' => ['Ürün ara', 'Kategori göster', 'Fiyat öğren'],
            'help' => ['Ürün ara', 'Kategori göster', 'Marka ara', 'Stok kontrolü'],
            'goodbye' => ['Tekrar görüşmek üzere!', 'Başka bir sorunuz olursa yardımcı olurum.']
        ];
        
        return $suggestions[$intent] ?? ['Ürün ara', 'Kategori göster', 'Yardım al'];
    }
    
    private function handleProductSearch($message) {
        $searchResults = $this->productData->searchProducts($message);
        $limitedResults = array_slice($searchResults, 0, 5);
        
        return [
            'intent' => 'product_search',
            'confidence' => 0.8,
            'response' => 'Arama sonuçlarınız: ' . count($searchResults) . ' ürün bulundu. İşte en uygun sonuçlar:',
            'products' => $limitedResults,
            'total_found' => count($searchResults),
            'suggestions' => ['Fiyata göre sırala', 'Markaya göre filtrele', 'Kategoriye göre filtrele']
        ];
    }
    
    private function handlePriceInquiry($message) {
        $searchResults = $this->productData->searchProducts($message);
        $priceInfo = [];
        
        foreach (array_slice($searchResults, 0, 3) as $product) {
            $priceInfo[] = $product['name'] . ': ' . number_format($product['price'], 2) . ' TL';
        }
        
        return [
            'intent' => 'price_inquiry',
            'confidence' => 0.85,
            'response' => 'Fiyat bilgileri: ' . implode(', ', $priceInfo),
            'products' => array_slice($searchResults, 0, 3),
            'suggestions' => ['Benzer ürünleri göster', 'Fiyat aralığı belirle', 'Kategoriye göre ara']
        ];
    }
    
    private function handleCategoryBrowse($message) {
        // Kategori önerisi isteniyorsa
        if (mb_strpos(mb_strtolower($message, 'UTF-8'), 'kategori') !== false && 
            mb_strpos(mb_strtolower($message, 'UTF-8'), 'öner') !== false) {
            
            $categoryRecommendations = $this->productData->getCategoryRecommendations(8);
            
            return [
                'intent' => 'category_recommendation',
                'confidence' => 0.95,
                'response' => 'Ürün kategorilerimizi analiz ettim! İşte size özel kategori önerileri:',
                'category_analysis' => [
                    'total_categories' => count($categoryRecommendations),
                    'recommendations' => $categoryRecommendations
                ],
                'suggestions' => [
                    'Kategori detayı göster',
                    'Fiyat aralığı belirle',
                    'En popüler kategoriler',
                    'Yeni kategoriler keşfet'
                ]
            ];
        }
        
        // Belirli bir kategori aranıyorsa
        $categories = ['Telefon', 'Bilgisayar', 'Giyim', 'Ev & Yaşam', 'Spor', 'Kozmetik', 'Kitap', 'Otomotiv', 'Sağlık', 'Bahçe', 'Pet Shop'];
        $category = $this->extractCategoryFromMessage($message, $categories);
        
        if ($category) {
            $categoryDetails = $this->productData->getCategoryDetails($category);
            
            if ($categoryDetails) {
                $limitedProducts = array_slice($categoryDetails['all_products'], 0, 5);
                
                return [
                    'intent' => 'category_browse',
                    'confidence' => 0.9,
                    'response' => "📊 **{$category}** kategorisi analizi:\n\n" .
                                 "• Toplam ürün: {$categoryDetails['summary']['product_count']}\n" .
                                 "• Ortalama fiyat: ₺{$categoryDetails['summary']['avg_price']}\n" .
                                 "• Ortalama puan: {$categoryDetails['summary']['avg_rating']}/5\n" .
                                 "• Pazar payı: %{$categoryDetails['summary']['market_share']}\n\n" .
                                 "İşte öne çıkan ürünler:",
                    'products' => $limitedProducts,
                    'category' => $category,
                    'category_analysis' => $categoryDetails,
                    'suggestions' => [
                        'Fiyata göre sırala',
                        'Markaya göre filtrele',
                        'En yüksek puanlılar',
                        'Başka kategori göster'
                    ]
                ];
            }
        }
        
        // Genel kategori listesi
        $categoryRecommendations = $this->productData->getCategoryRecommendations(6);
        
        return [
            'intent' => 'category_browse',
            'confidence' => 0.9,
            'response' => '🛍️ **Mevcut kategorilerimiz:**\n\n' . 
                          $this->formatCategoryList($categoryRecommendations) . 
                          '\n\nHangi kategoriyi detaylı incelemek istiyorsunuz?',
            'categories' => $categoryRecommendations,
            'suggestions' => [
                'Kategori önerisi al',
                'En popüler kategoriler',
                'Fiyat aralığı belirle',
                'Tüm ürünleri göster'
            ]
        ];
    }
    
    private function handleBrandSearch($message) {
        $brands = ['Apple', 'Samsung', 'Nike', 'Adidas', 'IKEA', 'Bosch', 'Sony', 'LG', 'Dell', 'HP', 'Lenovo'];
        $brand = $this->extractBrandFromMessage($message, $brands);
        
        if ($brand) {
            $products = $this->productData->getProductsByBrand($brand);
            $limitedProducts = array_slice($products, 0, 5);
            
            return [
                'intent' => 'brand_search',
                'confidence' => 0.8,
                'response' => $brand . ' markasında ' . count($products) . ' ürün bulundu. İşte öne çıkan ürünler:',
                'products' => $limitedProducts,
                'brand' => $brand,
                'total_products' => count($products),
                'suggestions' => ['Fiyata göre sırala', 'Kategoriye göre filtrele', 'Başka marka ara']
            ];
        }
        
        return [
            'intent' => 'brand_search',
            'confidence' => 0.8,
            'response' => 'Popüler markalar: ' . implode(', ', $brands),
            'brands' => $brands,
            'suggestions' => ['Marka seç', 'Tüm ürünleri göster', 'Marka karşılaştır']
        ];
    }
    
    private function handleStockInquiry($message) {
        $searchResults = $this->productData->searchProducts($message);
        $stockInfo = [];
        
        foreach (array_slice($searchResults, 0, 3) as $product) {
            $stockStatus = $product['stock'] > 0 ? 'Mevcut (' . $product['stock'] . ' adet)' : 'Tükendi';
            $stockInfo[] = $product['name'] . ': ' . $stockStatus;
        }
        
        return [
            'intent' => 'stock_inquiry',
            'confidence' => 0.75,
            'response' => 'Stok durumu: ' . implode(', ', $stockInfo),
            'products' => array_slice($searchResults, 0, 3),
            'suggestions' => ['Benzer ürünleri göster', 'Stokta olanları filtrele', 'Kategoriye göre ara']
        ];
    }
    
    private function handleRecommendation($message) {
        // Debug: Log the message
        Log::info('handleRecommendation called with message: ' . $message);
        
        // Akıllı ürün önerisi sistemi
        try {
            $smartRecommender = new SmartProductRecommenderService($this->productData);
            Log::info('SmartProductRecommender created successfully');
            
            $recommendations = $smartRecommender->getSmartRecommendations($message);
            Log::info('Smart recommendations result: ', $recommendations);
            
            if (!empty($recommendations['products'])) {
                Log::info('Smart recommendations found, returning smart response');
                return [
                    'intent' => 'smart_recommendation',
                    'confidence' => 0.9,
                    'response' => $recommendations['response'],
                    'products' => $recommendations['products'],
                    'recommendation_reason' => $recommendations['reason'],
                    'category_matched' => $recommendations['category_matched'],
                    'suggestions' => $recommendations['suggestions']
                ];
            }
        } catch (\Exception $e) {
            Log::error('Smart recommendation error: ' . $e->getMessage());
        }
        
        // Fallback to general recommendation
        Log::info('Falling back to general recommendation');
        return [
            'intent' => 'recommendation',
            'confidence' => 0.8,
            'response' => 'Size en popüler ve yüksek puanlı ürünleri öneriyorum:',
            'products' => $this->productData->getTopRatedProducts(5),
            'suggestions' => ['Farklı kategori öner', 'Fiyat aralığı belirle', 'Marka önerisi al']
        ];
    }
    
    private function handleComparison($message) {
        $searchResults = $this->productData->searchProducts($message);
        $comparisonProducts = array_slice($searchResults, 0, 2);
        
        if (count($comparisonProducts) >= 2) {
            $comparison = [];
            foreach ($comparisonProducts as $product) {
                $comparison[] = $product['name'] . ' - ' . number_format($product['price'], 2) . ' TL - Puan: ' . $product['rating'] . '/5';
            }
            
            return [
                'intent' => 'comparison',
                'confidence' => 0.7,
                'response' => 'Ürün karşılaştırması: ' . implode(' vs ', $comparison),
                'products' => $comparisonProducts,
                'suggestions' => ['Detaylı karşılaştır', 'Başka ürünler karşılaştır', 'Fiyat analizi']
            ];
        }
        
        return [
            'intent' => 'comparison',
            'confidence' => 0.7,
            'response' => 'Karşılaştırma için en az 2 ürün gerekli. Lütfen karşılaştırmak istediğiniz ürünleri belirtin.',
            'products' => null,
            'suggestions' => ['Ürün ara', 'Kategori göster', 'Marka seç']
        ];
    }
    
    private function handleCartAdd($message) {
        // Ürün numarası veya adından ürünü bul
        $productInfo = $this->extractProductFromMessage($message);
        
        if (!$productInfo) {
            return [
                'intent' => 'cart_add',
                'confidence' => 0.8,
                'response' => 'Hangi ürünü sepete eklemek istiyorsunuz? Ürün numarası veya adını belirtebilir misiniz?',
                'products' => null,
                'suggestions' => ['Ürün listesini göster', 'Kategoriye göre ara', 'Markaya göre ara']
            ];
        }
        
        // Ürünü sepete ekle (burada gerçek sepet işlemi yapılacak)
        $cartResult = $this->addToCart($productInfo);
        
        return [
            'intent' => 'cart_add',
            'confidence' => 0.9,
            'response' => $cartResult['message'],
            'products' => $cartResult['added_product'],
            'cart_status' => $cartResult['status'],
            'suggestions' => ['Sepetimi göster', 'Başka ürün ekle', 'Alışverişe devam et']
        ];
    }
    
    private function handleCartRemove($message) {
        // Ürün numarası veya adından ürünü bul
        $productInfo = $this->extractProductFromMessage($message);
        
        if (!$productInfo) {
            return [
                'intent' => 'cart_remove',
                'confidence' => 0.8,
                'response' => 'Hangi ürünü sepetten çıkarmak istiyorsunuz? Ürün numarası veya adını belirtebilir misiniz?',
                'products' => null,
                'suggestions' => ['Sepetimi göster', 'Ürün listesini göster', 'Yardım al']
            ];
        }
        
        // Ürünü sepetten çıkar (burada gerçek sepet işlemi yapılacak)
        $cartResult = $this->removeFromCart($productInfo);
        
        return [
            'intent' => 'cart_remove',
            'confidence' => 0.9,
            'response' => $cartResult['message'],
            'removed_product' => $cartResult['removed_product'],
            'cart_status' => $cartResult['status'],
            'suggestions' => ['Sepetimi göster', 'Başka ürün çıkar', 'Alışverişe devam et']
        ];
    }
    
    private function handleCartView($message) {
        // Sepet içeriğini göster (burada gerçek sepet işlemi yapılacak)
        $cartContent = $this->getCartContent();
        
        return [
            'intent' => 'cart_view',
            'confidence' => 0.9,
            'response' => $cartContent['message'],
            'cart_items' => $cartContent['items'],
            'cart_total' => $cartContent['total'],
            'cart_count' => $cartContent['count'],
            'suggestions' => ['Ürün ekle', 'Ürün çıkar', 'Ödeme yap', 'Alışverişe devam et']
        ];
    }
    
    private function handleOrderInquiry($message) {
        $message = mb_strtolower($message, 'UTF-8');
        
        // Check if this is a product recommendation request
        $recommendationKeywords = ['öner', 'tavsiye', 'en iyi', 'popüler', 'trend', 'yeni', 'güncel', 'öneri', 'tavsiye et', 'ne alayım'];
        $isRecommendationRequest = false;
        
        foreach ($recommendationKeywords as $keyword) {
            if (mb_strpos($message, $keyword) !== false) {
                $isRecommendationRequest = true;
                break;
            }
        }
        
        if ($isRecommendationRequest) {
            // Handle as product recommendation
            return $this->handleRecommendation($message);
        } else {
            // Check if this is an order tracking request
            $orderTrackingKeywords = ['siparişim nerede', 'sipariş durumu', 'sipariş takibi', 'sipariş nerede', 'siparişim', 'sipariş'];
            $isOrderTrackingRequest = false;
            
            foreach ($orderTrackingKeywords as $keyword) {
                if (mb_strpos($message, $keyword) !== false) {
                    $isOrderTrackingRequest = true;
                    break;
                }
            }
            
            if ($isOrderTrackingRequest) {
                // Return order tracking response
                return [
                    'intent' => 'order_tracking',
                    'order_id' => 'ORD-998877',
                    'status' => 'shipped',
                    'order_date' => '2025-08-15',
                    'items' => [
                        [
                            'product_id' => 101,
                            'name' => 'Sneaker X 42 Numara',
                            'quantity' => 1,
                            'price' => 999
                        ],
                        [
                            'product_id' => 102,
                            'name' => 'Siyah Hoodie',
                            'quantity' => 1,
                            'price' => 499
                        ]
                    ],
                    'shipping' => [
                        'courier' => 'Yurtiçi Kargo',
                        'tracking_number' => 'YT123456789TR',
                        'last_update' => '2025-08-18T14:30:00Z',
                        'location' => 'İstanbul Aktarma Merkezi',
                        'estimated_delivery' => '2025-08-20'
                    ],
                    'message' => 'Siparişiniz kargoya verildi. Takip numaranız: YT123456789TR. Tahmini teslim tarihi 20 Ağustos.',
                    'suggestions' => ['Kargo takip', 'Sipariş geçmişi', 'İletişim', 'Yardım al']
                ];
            } else {
                // Handle as general order inquiry
                return [
                    'intent' => 'order_inquiry',
                    'confidence' => 0.9,
                    'response' => 'Sipariş durumunuzu kontrol ediyorum. Sipariş numaranızı veya müşteri bilgilerinizi girebilir misiniz?',
                    'suggestions' => ['Sipariş numarası gir', 'Müşteri bilgileri göster', 'Sipariş geçmişi göster', 'Yardım al']
                ];
            }
        }
    }
    
    private function handleOrderTracking($message) {
        // Return order tracking response
        return [
            'intent' => 'order_tracking',
            'order_id' => 'ORD-998877',
            'status' => 'shipped',
            'order_date' => '2025-08-15',
            'items' => [
                [
                    'product_id' => 101,
                    'name' => 'Sneaker X 42 Numara',
                    'quantity' => 1,
                    'price' => 999
                ],
                [
                    'product_id' => 102,
                    'name' => 'Siyah Hoodie',
                    'quantity' => 1,
                    'price' => 499
                ]
            ],
            'shipping' => [
                'courier' => 'Yurtiçi Kargo',
                'tracking_number' => 'YT123456789TR',
                'last_update' => '2025-08-18T14:30:00Z',
                'location' => 'İstanbul Aktarma Merkezi',
                'estimated_delivery' => '2025-08-20'
            ],
            'message' => 'Siparişiniz kargoya verildi. Takip numaranız: YT123456789TR. Tahmini teslim tarihi 20 Ağustos.',
            'suggestions' => ['Kargo takip', 'Sipariş geçmişi', 'İletişim', 'Yardım al']
        ];
    }
    
    private function extractProductFromMessage($message) {
        // Ürün numarası ara (örn: "9. ürün", "9 numaralı ürün", "9. ürünü")
        if (preg_match('/(\d+)\s*[\.\s]?\s*(?:numaralı\s+)?ürün(?:ü)?/i', $message, $matches)) {
            $productNumber = (int)$matches[1];
            $product = $this->findProductByNumber($productNumber);
            if ($product) {
                return [
                    'type' => 'by_number',
                    'value' => $productNumber,
                    'product' => $product
                ];
            }
        }
        
        // Ürün adı ara
        foreach ($this->productData->getAllProducts() as $product) {
            if (stripos($message, $product['name']) !== false) {
                return [
                    'type' => 'by_name',
                    'value' => $product['name'],
                    'product' => $product
                ];
            }
        }
        
        return null;
    }
    
    private function findProductByNumber($number) {
        $products = $this->productData->getAllProducts();
        
        // Ürün numarası 1'den başlar
        if ($number > 0 && $number <= count($products)) {
            return $products[$number - 1];
        }
        
        return null;
    }
    
    private function addToCart($productInfo) {
        // Burada gerçek sepet işlemi yapılacak
        // Şimdilik simüle ediyoruz
        
        $product = $productInfo['product'];
        
        return [
            'status' => 'success',
            'message' => "✅ {$product['name']} başarıyla sepete eklendi! Fiyat: ₺{$product['price']}",
            'added_product' => $product,
            'cart_total' => $product['price'], // Gerçek sepet toplamı hesaplanacak
            'cart_count' => 1 // Gerçek sepet ürün sayısı hesaplanacak
        ];
    }
    
    private function removeFromCart($productInfo) {
        // Burada gerçek sepet işlemi yapılacak
        // Şimdilik simüle ediyoruz
        
        $product = $productInfo['product'];
        
        return [
            'status' => 'success',
            'message' => "❌ {$product['name']} sepetten çıkarıldı.",
            'removed_product' => $product,
            'cart_total' => 0, // Gerçek sepet toplamı hesaplanacak
            'cart_count' => 0 // Gerçek sepet ürün sayısı hesaplanacak
        ];
    }
    
    private function getCartContent() {
        // Burada gerçek sepet işlemi yapılacak
        // Şimdilik simüle ediyoruz
        
        return [
            'message' => '🛒 Sepetinizde şu anda ürün bulunmuyor.',
            'items' => [],
            'total' => 0,
            'count' => 0
        ];
    }
    
    private function extractCategoryFromMessage($message, $categories) {
        $message = mb_strtolower($message, 'UTF-8');
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($categories as $category) {
            $categoryLower = mb_strtolower($category, 'UTF-8');
            
            // Tam eşleşme kontrolü
            if (mb_strpos($message, $categoryLower) !== false) {
                $score = 10; // Tam eşleşme için yüksek skor
                
                // Mesajda kategori kelimesi varsa ekstra puan
                if (mb_strpos($message, 'kategori') !== false) {
                    $score += 5;
                }
                
                // Mesajda "göster" kelimesi varsa ekstra puan
                if (mb_strpos($message, 'göster') !== false) {
                    $score += 3;
                }
                
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $category;
                }
            }
        }
        
        return $bestMatch;
    }
    
    private function extractBrandFromMessage($message, $brands) {
        foreach ($brands as $brand) {
            if (mb_strpos($message, mb_strtolower($brand, 'UTF-8')) !== false) {
                return $brand;
            }
        }
        return null;
    }
    
    private function formatCategoryList($categories) {
        $formatted = '';
        
        foreach ($categories as $index => $category) {
            $emoji = $this->getCategoryEmoji($category['category']);
            $formatted .= ($index + 1) . ". {$emoji} **{$category['category']}** ";
            $formatted .= "({$category['product_count']} ürün) ";
            $formatted .= "• Ort. ₺{$category['avg_price']} ";
            $formatted .= "• ⭐ {$category['avg_rating']}/5\n";
        }
        
        return $formatted;
    }
    
    private function getCategoryEmoji($category) {
        $emojis = [
            'Telefon' => '📱',
            'Bilgisayar' => '💻',
            'Tablet' => '📱',
            'Kulaklık' => '🎧',
            'Televizyon' => '📺',
            'Oyun Konsolu' => '🎮',
            'Spor Ayakkabı' => '👟',
            'Kot Pantolon' => '👖',
            'Polo Yaka' => '👕',
            'Ceket' => '🧥',
            'Elbise' => '👗',
            'Gömlek' => '👔',
            'Sweatshirt' => '🧥',
            'Etek' => '👗',
            'Çanta' => '👜',
            'Mobilya' => '🪑',
            'Aydınlatma' => '💡',
            'Elektrikli Süpürge' => '🧹',
            'Beyaz Eşya' => '🏠',
            'Mutfak' => '🍳',
            'Bisiklet' => '🚲',
            'Mont' => '🧥',
            'Hırka' => '🧥',
            'Spor Çanta' => '🎒',
            'Spor Çorap' => '🧦',
            'Şort' => '🩳',
            'Spor Tshirt' => '👕',
            'Spor Pantolon' => '👖',
            'Şampuan' => '🧴',
            'Yüz Bakımı' => '🧴',
            'Nemlendirici' => '🧴',
            'Makyaj' => '💄',
            'Güneş Bakımı' => '☀️',
            'Serum' => '🧴',
            'Kitap' => '📚',
            'Oyuncak' => '🧸',
            'Oyun' => '🎲',
            'Lastik' => '🚗',
            'Akü' => '🔋',
            'Motor Yağı' => '🛢️',
            'Ağrı Kesici' => '💊',
            'Vitamin' => '💊',
            'Mineral' => '💊',
            'Bahçe Aleti' => '🌱',
            'Bahçe Makinesi' => '🪚',
            'El Aleti' => '🔧',
            'Kedi Maması' => '🐱',
            'Köpek Maması' => '🐕'
        ];
        
        return $emojis[$category] ?? '📦';
    }
    
    private function handleContextualProductSearch($message, $context) {
        $category = $context['current_category'];
        $brand = $context['current_brand'];
        
        $searchQuery = $message;
        if ($brand) {
            $searchQuery .= " " . $brand;
        }
        
        $searchResults = $this->productData->searchProducts($searchQuery);
        
        // Kategoriye göre filtrele
        if ($category) {
            $searchResults = array_filter($searchResults, function($product) use ($category) {
                return strtolower($product['category']) === strtolower($category);
            });
        }
        
        $limitedResults = array_slice($searchResults, 0, 5);
        
        return [
            'intent' => 'contextual_product_search',
            'confidence' => 0.9,
            'response' => $category . ' kategorisinde ' . $searchQuery . ' için ' . count($searchResults) . ' ürün bulundu. İşte en uygun sonuçlar:',
            'products' => $limitedResults,
            'total_found' => count($searchResults),
            'context_used' => [
                'category' => $category,
                'brand' => $brand,
                'search_query' => $searchQuery
            ],
            'suggestions' => ['Fiyata göre sırala', 'Markaya göre filtrele', 'Başka kategori ara']
        ];
    }
    
    private function handleContextualRecommendation($message, $context) {
        $category = $context['current_category'];
        $brand = $context['current_brand'];
        
        if ($category) {
            $products = $this->productData->getProductsByCategory($category);
            if ($brand) {
                $products = array_filter($products, function($product) use ($brand) {
                    return strtolower($product['brand']) === strtolower($brand);
                });
            }
            
            // En yüksek puanlı ürünleri seç
            usort($products, function($a, $b) {
                return $b['rating'] <=> $a['rating'];
            });
            
            $topProducts = array_slice($products, 0, 5);
            
            return [
                'intent' => 'contextual_recommendation',
                'confidence' => 0.9,
                'response' => $category . ($brand ? ' kategorisinde ' . $brand . ' markasından ' : ' kategorisinde ') . 'en iyi ürünleri öneriyorum:',
                'products' => $topProducts,
                'context_used' => [
                    'category' => $category,
                    'brand' => $brand
                ],
                'suggestions' => ['Farklı marka öner', 'Fiyat aralığı belirle', 'Başka kategori öner']
            ];
        }
        
        // Context yoksa normal öneri
        return $this->handleRecommendation($message);
    }
    
    private function handleContextualComparison($message, $context, $lastProducts) {
        if (count($lastProducts) >= 2) {
            $comparison = [];
            foreach (array_slice($lastProducts, 0, 2) as $product) {
                $comparison[] = $product['name'] . ' - ' . number_format($product['price'], 2) . ' TL - Puan: ' . $product['rating'] . '/5';
            }
            
            return [
                'intent' => 'contextual_comparison',
                'confidence' => 0.9,
                'response' => 'Son konuştuğumuz ürünleri karşılaştırıyorum: ' . implode(' vs ', $comparison),
                'products' => array_slice($lastProducts, 0, 2),
                'context_used' => [
                    'last_products' => count($lastProducts),
                    'category' => $context['current_category'],
                    'brand' => $context['current_brand']
                ],
                'suggestions' => ['Detaylı karşılaştır', 'Başka ürünler karşılaştır', 'Fiyat analizi']
            ];
        }
        
        return $this->handleComparison($message);
    }
}
