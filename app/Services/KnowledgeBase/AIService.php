<?php

namespace App\Services\KnowledgeBase;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AIService
{
    private $apiKey;
    private $baseUrl = 'https://api.openai.com/v1';
    private $model = 'gpt-4o-mini';
    private $embeddingModel = 'text-embedding-ada-002';

    public function __construct()
    {
        $this->apiKey = config('openai.api_key');
        
        if (!$this->apiKey) {
            throw new \Exception('OpenAI API key bulunamadı. Lütfen .env dosyasında OPENAI_API_KEY değerini kontrol edin.');
        }
    }

    /**
     * Text için embedding oluşturur
     */
    public function createEmbedding(string $text): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/embeddings', [
                'model' => $this->embeddingModel,
                'input' => $text,
            ]);

            if (!$response->successful()) {
                throw new \Exception('OpenAI API Error: ' . $response->body());
            }

            $data = $response->json();
            return $data['data'][0]['embedding'];
        } catch (\Exception $e) {
            Log::error('OpenAI embedding error: ' . $e->getMessage());
            throw new \Exception('Embedding oluşturulamadı: ' . $e->getMessage());
        }
    }

    /**
     * Birden fazla text için embedding oluşturur
     */
    public function createEmbeddings(array $texts): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/embeddings', [
                'model' => $this->embeddingModel,
                'input' => $texts,
            ]);

            if (!$response->successful()) {
                throw new \Exception('OpenAI API Error: ' . $response->body());
            }

            $data = $response->json();
            return array_map(function($item) {
                return $item['embedding'];
            }, $data['data']);
        } catch (\Exception $e) {
            Log::error('OpenAI embeddings error: ' . $e->getMessage());
            throw new \Exception('Embeddings oluşturulamadı: ' . $e->getMessage());
        }
    }

    /**
     * Intent detection yapar
     */
    public function detectIntent(string $query, array $context = []): array
    {
        try {
            Log::info('detectIntent called with:', ['query' => $query, 'context' => $context]);
            
            $systemPrompt = $this->buildIntentDetectionPrompt($context);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $query]
                ],
                'temperature' => 0.1,
                'max_tokens' => 150
            ]);

            if (!$response->successful()) {
                throw new \Exception('OpenAI API Error: ' . $response->body());
            }

            $data = $response->json();
            $intent = $this->parseIntentResponse($data['choices'][0]['message']['content']);
            
            Log::info('OpenAI intent detection result:', $intent);
            
            return [
                'intent' => $intent['intent'],
                'confidence' => $intent['confidence'],
                'entities' => $intent['entities'] ?? [],
                'category' => $intent['category'] ?? 'general'
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI intent detection error: ' . $e->getMessage());
            Log::info('Falling back to pattern-based intent detection');
            
            // Fallback to local AI logic
            return $this->fallbackIntentDetection($query);
        }
    }

    /**
     * Fallback intent detection using pattern matching
     */
    private function fallbackIntentDetection(string $query): array
    {
        Log::info('Fallback intent detection called with query:', ['query' => $query]);
        
        $intent = 'unknown';
        $confidence = 0.5;
        
        // "Ürün öner", "ürün tavsiye" gibi spesifik öneri ifadeleri - ÖNCELİKLİ
        if (preg_match('/(ürün öner|ürün tavsiye|ürün önerisi|ürün tavsiyesi|ne önerirsin|bana öner|bana tavsiye|öneri|tavsiye|öner|tavsiye)/i', $query)) {
            $intent = 'product_recommendation';
            $confidence = 0.95;
            Log::info('Intent detected as product_recommendation from pattern 1');
        }
        // Spesifik ürün arama (renk + ürün, marka + ürün gibi)
        elseif (preg_match('/(kırmızı|mavi|yeşil|sarı|siyah|beyaz|pembe|mor|turuncu|gri|kahverengi)\s+(kazak|gömlek|pantolon|etek|ceket|ayakkabı|çanta|şapka|saat|telefon|bilgisayar|oyuncak|kitap)/i', $query)) {
            $intent = 'product_search';
            $confidence = 0.9;
            Log::info('Intent detected as product_search from color+product pattern');
        }
        // Marka + ürün arama
        elseif (preg_match('/(nike|adidas|apple|samsung|sony|canon|hp|dell|lenovo|asus|acer|lg|philips|bosch|siemens)\s+(saat|telefon|bilgisayar|ayakkabı|çanta|giyim)/i', $query)) {
            $intent = 'product_search';
            $confidence = 0.9;
            Log::info('Intent detected as product_search from brand+product pattern');
        }
        // Saat, telefon, ürün arama gibi spesifik ürün sorguları
        elseif (preg_match('/(saat|telefon|ürün|elbise|ayakkabı|bilgisayar|kitap|mobilya|elektronik|giyim|aksesuar|kozmetik|spor|ev|bahçe|oyuncak|kitap|müzik|film|oyun)/i', $query)) {
            $intent = 'product_search';
            $confidence = 0.85;
            Log::info('Intent detected as product_search from specific product pattern');
        }
        // "Bana göre", "bul", "ara" gibi genel arama ifadeleri
        elseif (preg_match('/(bana göre|bul|ara|göster|listele|var mı|mevcut)/i', $query)) {
            $intent = 'product_search';
            $confidence = 0.8;
            Log::info('Intent detected as product_search from general search pattern');
        }
        
        Log::info('Final fallback intent detection result:', ['intent' => $intent, 'confidence' => $confidence]);
        
        return [
            'intent' => $intent,
            'confidence' => $confidence,
            'entities' => [],
            'category' => 'general'
        ];
    }

    /**
     * Query için response üretir
     */
    public function generateResponse(string $query, array $chunks, array $context = []): string
    {
        try {
            $systemPrompt = $this->buildResponseGenerationPrompt($context);
            $userPrompt = $this->buildUserPrompt($query, $chunks);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500
            ]);

            if (!$response->successful()) {
                throw new \Exception('OpenAI API Error: ' . $response->body());
            }

            $data = $response->json();
            return $data['choices'][0]['message']['content'];
        } catch (\Exception $e) {
            Log::error('OpenAI response generation error: ' . $e->getMessage());
            return 'Üzgünüm, şu anda yanıt üretemiyorum. Lütfen daha sonra tekrar deneyin.';
        }
    }

    /**
     * Content'i analiz eder
     */
    public function analyzeContent(string $content): array
    {
        try {
            $systemPrompt = "Sen bir content analiz uzmanısın. Verilen content'i analiz et ve aşağıdaki bilgileri JSON formatında döndür:
            - content_type: content tipi (product, faq, blog, review, category, general)
            - language: dil (tr, en, mixed)
            - sentiment: duygu analizi (positive, negative, neutral)
            - key_topics: ana konular (array)
            - entities: varlıklar (array)
            - summary: özet (string)";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $content]
                ],
                'temperature' => 0.3,
                'max_tokens' => 300
            ]);

            if (!$response->successful()) {
                throw new \Exception('OpenAI API Error: ' . $response->body());
            }

            $data = $response->json();
            $analysis = json_decode($data['choices'][0]['message']['content'], true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return $analysis;
            }

            return [
                'content_type' => 'general',
                'language' => 'tr',
                'sentiment' => 'neutral',
                'key_topics' => [],
                'entities' => [],
                'summary' => 'Content analiz edilemedi'
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI content analysis error: ' . $e->getMessage());
            return [
                'content_type' => 'general',
                'language' => 'tr',
                'sentiment' => 'neutral',
                'key_topics' => [],
                'entities' => [],
                'summary' => 'Content analiz edilemedi'
            ];
        }
    }

    /**
     * Intent detection prompt'u oluşturur
     */
    private function buildIntentDetectionPrompt(array $context): string
    {
        $prompt = "Sen bir e-ticaret intent detection uzmanısın. Kullanıcının sorgusunu analiz et ve aşağıdaki intent'lerden birini belirle.

        Intent Categories ve Örnekler:
        
        - product_search: Ürün arama, bulma, listeleme
          Örnekler: 'saat varmı', 'telefon bul', 'kırmızı elbise', 'mavi kazak', 'nike ayakkabı'
        
        - product_recommendation: Ürün önerisi, tavsiye
          Örnekler: 'ürün öner', 'ürün tavsiye', 'ne önerirsin', 'bana öner', 'bana tavsiye'
        
        - product_search: Ürün arama, bulma, listeleme
          Örnekler: 'saat varmı', 'telefon bul', 'kırmızı elbise', 'mavi kazak', 'nike ayakkabı', 'oyuncak bul', 'kitap ara'
        
        - product_info: Ürün bilgisi, detay, özellik
          Örnekler: 'bu ürünün özellikleri', 'fiyatı ne kadar', 'garanti süresi'
        
        - category_browse: Kategori keşfi, filtreleme
          Örnekler: 'elektronik kategorisi', 'giyim ürünleri', 'ev dekorasyonu', 'elektronik listele', 'giyim listele', 'oyuncak listele', 'kitap listele', 'saat listele', 'telefon listele'
        
        - brand_search: Marka arama, marka bilgisi
          Örnekler: 'Apple ürünleri', 'Nike markası', 'Samsung telefonlar'
        
        - faq_search: Sık sorulan sorular, yardım
          Örnekler: 'nasıl sipariş veririm', 'iade nasıl yapılır', 'kargo ücreti'
        
        - order_status: Sipariş durumu, takip
          Örnekler: 'siparişim nerede', 'kargo takip', 'sipariş durumu'
        
        - cart_management: Sepet işlemleri
          Örnekler: 'sepete ekle', 'sepeti temizle', 'sepetimde ne var'
        
        - general_help: Genel yardım, destek
          Örnekler: 'yardım', 'destek', 'nasıl kullanırım'
        
        - unknown: Belirlenemeyen intent

        ÖNEMLİ: 
        - 'Bana göre saat varmı?' gibi sorgular product_search intent'ine aittir çünkü kullanıcı ürün arıyor.
        - 'ürün öner', 'ne önerirsin' gibi sorgular product_recommendation intent'ine aittir çünkü kullanıcı öneri istiyor.
        - 'bana oyuncak öner', 'kitap bul' gibi spesifik ürün aramaları product_search intent'ine aittir çünkü kullanıcı belirli bir ürün arıyor.
        - 'elektronik listele', 'giyim listele' gibi kategori listeleme istekleri category_browse intent'ine aittir çünkü kullanıcı kategoriye göre ürün listesi istiyor.
        
        Context: " . json_encode($context, JSON_UNESCAPED_UNICODE) . "

        Yanıtı şu formatta ver:
        {
            \"intent\": \"intent_name\",
            \"confidence\": 0.95,
            \"category\": \"intent_category\",
            \"entities\": [\"entity1\", \"entity2\"]
        }";

        return $prompt;
    }

    /**
     * Response generation prompt'u oluşturur
     */
    private function buildResponseGenerationPrompt(array $context): string
    {
        return "Sen bir e-ticaret asistanısın. Kullanıcının sorusuna, verilen bilgileri kullanarak yardımcı ol.

        Context: " . json_encode($context, JSON_UNESCAPED_UNICODE) . "

        Kurallar:
        1. Sadece verilen bilgileri kullan
        2. Türkçe yanıt ver
        3. Kısa ve net ol
        4. Eğer bilgi yoksa, bilgi olmadığını belirt
        5. Ürün önerilerinde fiyat ve özellik bilgisi ver";
    }

    /**
     * User prompt'u oluşturur
     */
    private function buildUserPrompt(string $query, array $chunks): string
    {
        $chunkTexts = array_map(function($chunk) {
            return "Chunk " . $chunk['chunk_index'] . ":\n" . $chunk['content'];
        }, $chunks);

        return "Kullanıcı Sorusu: {$query}\n\nMevcut Bilgiler:\n" . implode("\n\n", $chunkTexts);
    }

    /**
     * Intent response'unu parse eder
     */
    private function parseIntentResponse(string $response): array
    {
        Log::info('parseIntentResponse called with:', ['response' => $response]);
        
        // JSON formatında response gelirse parse et
        if (preg_match('/\{.*\}/s', $response)) {
            try {
                $jsonData = json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['intent'])) {
                    Log::info('JSON response parsed successfully:', $jsonData);
                    return $jsonData;
                }
            } catch (\Exception $e) {
                Log::warning('JSON parsing failed:', ['error' => $e->getMessage()]);
            }
        }
        
        // Structured text parsing
        $intent = 'unknown';
        $confidence = 0.5;
        
        // "Ürün öner", "ürün tavsiye" gibi ifadeler - ÖNCELİKLİ
        if (preg_match('/(ürün öner|ürün tavsiye|ne önerirsin|bana göre|öner|tavsiye)/i', $response)) {
            $intent = 'product_recommendation';
            $confidence = 0.95;
            Log::info('Intent detected as product_recommendation from parseIntentResponse');
        }
        // "Bana göre", "ne önerirsin", "bul" gibi ifadeler
        elseif (preg_match('/(bana göre|ne önerirsin|öner|tavsiye|bul|ara|göster|listele|var mı|mevcut)/i', $response)) {
            $intent = 'product_recommendation';
            $confidence = 0.9;
            Log::info('Intent detected as product_recommendation from parseIntentResponse pattern 2');
        }
        // Ürün bilgisi
        elseif (preg_match('/(özellik|fiyat|garanti|teknik|detay|açıklama)/i', $response)) {
            $intent = 'product_info';
            $confidence = 0.8;
        }
        // Kategori
        elseif (preg_match('/(kategori|tür|çeşit|sınıf)/i', $response)) {
            $intent = 'category_browse';
            $confidence = 0.8;
        }
        // Marka
        elseif (preg_match('/(marka|brand|firma|şirket)/i', $response)) {
            $intent = 'brand_search';
            $confidence = 0.8;
        }
        // FAQ
        elseif (preg_match('/(nasıl|soru|cevap|yardım|destek|iade|kargo|ödeme)/i', $response)) {
            $intent = 'faq_search';
            $confidence = 0.8;
        }
        // Sipariş
        elseif (preg_match('/(sipariş|kargo|takip|durum|order)/i', $response)) {
            $intent = 'order_status';
            $confidence = 0.8;
        }
        // Sepet
        elseif (preg_match('/(sepet|cart|basket)/i', $response)) {
            $intent = 'cart_management';
            $confidence = 0.8;
        }
        
        Log::info('parseIntentResponse final result:', ['intent' => $intent, 'confidence' => $confidence]);
        
        return [
            'intent' => $intent,
            'confidence' => $confidence,
            'entities' => [],
            'category' => 'general'
        ];
    }

    /**
     * Vector similarity hesaplar
     */
    public function calculateSimilarity(array $vector1, array $vector2): float
    {
        if (count($vector1) !== count($vector2)) {
            return 0.0;
        }

        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        for ($i = 0; $i < count($vector1); $i++) {
            $dotProduct += $vector1[$i] * $vector2[$i];
            $magnitude1 += $vector1[$i] * $vector1[$i];
            $magnitude2 += $vector2[$i] * $vector2[$i];
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    /**
     * Cache'den embedding alır veya oluşturur
     */
    public function getOrCreateEmbedding(string $text, string $cacheKey = null): array
    {
        $cacheKey = $cacheKey ?? 'embedding_' . md5($text);
        
        return Cache::remember($cacheKey, 3600 * 24 * 7, function() use ($text) {
            return $this->createEmbedding($text);
        });
    }

    /**
     * OpenAI API'yi test eder
     */
    public function testConnection(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/models');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'OpenAI API bağlantısı başarılı',
                    'models' => $response->json()['data'] ?? []
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'OpenAI API Error: ' . $response->body(),
                    'status' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Bağlantı hatası: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Query expansion yapar - GPT ile benzer kelimeler bulur
     */
    public function expandQuery(string $query, array $context = []): array
    {
        try {
            if (config('app.debug')) {
                Log::info('Starting query expansion for:', ['query' => $query]);
            }
            
            $systemPrompt = "Sen bir arama uzmanısın. Kullanıcının arama terimini analiz et ve benzer/ilgili kelimeleri bul.
            
            Örnek: 'kıpkırmızı renkli turuncu sallı tshirt' için:
            - Ana terimler: kırmızı, turuncu, şal, tshirt
            - Benzer kelimeler: kızıl, al, portakal rengi, atkı, gömlek, üst giyim
            - İlgili kategoriler: giyim, üst giyim, aksesuar
            
            Yanıtı JSON formatında ver:
            {
                \"original_query\": \"orijinal arama\",
                \"expanded_terms\": [\"ana\", \"terimler\"],
                \"similar_words\": [\"benzer\", \"kelimeler\"],
                \"related_categories\": [\"ilgili\", \"kategoriler\"],
                \"search_strategy\": \"arama stratejisi\"
            }";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $query]
                ],
                'temperature' => 0.3,
                'max_tokens' => 300
            ]);

            if (!$response->successful()) {
                throw new \Exception('OpenAI API Error: ' . $response->body());
            }

            $data = $response->json();
            
            $expansion = json_decode($data['choices'][0]['message']['content'], true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                if (config('app.debug')) {
                    Log::info('Query expansion successful:', $expansion);
                }
                return $expansion;
            }

            if (config('app.debug')) {
                Log::warning('JSON parse failed, using fallback');
            }
            // Fallback expansion
            return $this->fallbackQueryExpansion($query);
            
        } catch (\Exception $e) {
            Log::error('OpenAI query expansion error: ' . $e->getMessage());
            return $this->fallbackQueryExpansion($query);
        }
    }

    /**
     * Fallback query expansion - basit keyword extraction
     */
    private function fallbackQueryExpansion(string $query): array
    {
        $words = explode(' ', mb_strtolower($query));
        $stopWords = ['ve', 'ile', 'için', 'bu', 'şu', 'o', 'bir', 'da', 'de'];
        
        $filteredWords = array_filter($words, function($word) use ($stopWords) {
            return mb_strlen($word) > 2 && !in_array($word, $stopWords);
        });

        return [
            'original_query' => $query,
            'expanded_terms' => array_values($filteredWords),
            'similar_words' => array_values($filteredWords),
            'related_categories' => ['general'],
            'search_strategy' => 'keyword_matching'
        ];
    }

    /**
     * Semantic search yapar - chunk'larda benzer kelimelerle arama
     */
    public function semanticSearch(string $query, array $chunks, array $context = []): array
    {
        try {
            if (config('app.debug')) {
                Log::info('Starting semantic search for query:', ['query' => $query, 'chunks_count' => count($chunks)]);
            }
            
            // Query expansion yap
            $expandedQuery = $this->expandQuery($query, $context);
            
            // Debug: Log expanded query (production'da kapatılabilir)
            if (config('app.debug')) {
                Log::info('Query expansion result:', [
                    'original_query' => $query,
                    'expanded_terms' => $expandedQuery['expanded_terms'] ?? [],
                    'similar_words' => $expandedQuery['similar_words'] ?? [],
                    'related_categories' => $expandedQuery['related_categories'] ?? []
                ]);
            }
            
            // Her chunk için relevance score hesapla
            $scoredChunks = [];
            foreach ($chunks as $chunk) {
                $relevanceScore = $this->calculateRelevanceScore($chunk, $expandedQuery);
                
                // Debug: Log scores for first few chunks (production'da kapatılabilir)
                if (config('app.debug') && count($scoredChunks) < 3) {
                    Log::info('Chunk score:', [
                        'chunk_id' => $chunk['id'] ?? 'unknown',
                        'content_preview' => mb_substr($chunk['content'], 0, 100),
                        'relevance_score' => $relevanceScore
                    ]);
                }
                
                if ($relevanceScore >= 0.3) { // %30+ ilgili olanları al (daha düşük threshold)
                    $chunk['relevance_score'] = $relevanceScore;
                    $chunk['matched_terms'] = $this->findMatchedTerms($chunk, $expandedQuery);
                    $scoredChunks[] = $chunk;
                }
            }
            
            // Relevance score'a göre sırala
            usort($scoredChunks, function($a, $b) {
                return $b['relevance_score'] <=> $a['relevance_score'];
            });
            
            if (config('app.debug')) {
                Log::info('Final results:', [
                    'total_scored' => count($scoredChunks),
                    'threshold' => 0.3
                ]);
            }
            
            return [
                'query' => $query,
                'expanded_query' => $expandedQuery,
                'results' => $scoredChunks,
                'total_found' => count($scoredChunks),
                'search_strategy' => $expandedQuery['search_strategy']
            ];
            
        } catch (\Exception $e) {
            Log::error('Semantic search error: ' . $e->getMessage());
            return [
                'query' => $query,
                'expanded_query' => null,
                'results' => [],
                'total_found' => 0,
                'search_strategy' => 'fallback',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Chunk için relevance score hesaplar
     */
    private function calculateRelevanceScore(array $chunk, array $expandedQuery): float
    {
        $score = 0.0;
        $content = mb_strtolower($chunk['content']);
        
        // Ana terimler için yüksek puan
        foreach ($expandedQuery['expanded_terms'] as $term) {
            if (mb_strpos($content, $term) !== false) {
                $score += 0.4; // Daha yüksek puan
            }
        }
        
        // Benzer kelimeler için orta puan
        foreach ($expandedQuery['similar_words'] as $word) {
            if (mb_strpos($content, $word) !== false) {
                $score += 0.25; // Daha yüksek puan
            }
        }
        
        // İlgili kategoriler için bonus puan
        if (isset($chunk['content_type'])) {
            foreach ($expandedQuery['related_categories'] as $category) {
                if (mb_strpos(mb_strtolower($chunk['content_type']), $category) !== false) {
                    $score += 0.2; // Daha yüksek puan
                }
            }
        }
        
        // Metadata'dan bonus puan
        if (isset($chunk['metadata'])) {
            $metadata = json_encode($chunk['metadata']);
            foreach ($expandedQuery['expanded_terms'] as $term) {
                if (mb_strpos($metadata, $term) !== false) {
                    $score += 0.15; // Daha yüksek puan
                }
            }
        }
        
        // Ürün arama için özel bonus (saat, telefon, bilgisayar gibi)
        $productKeywords = [
            'saat' => ['saat', 'watch', 'clock', 'timepiece'],
            'telefon' => ['telefon', 'phone', 'iphone', 'samsung', 'mobile', 'smartphone'],
            'bilgisayar' => ['bilgisayar', 'computer', 'laptop', 'macbook', 'dell', 'hp'],
            'tablet' => ['tablet', 'ipad', 'samsung tablet'],
            'kulaklık' => ['kulaklık', 'headphone', 'airpods', 'sony'],
            'tv' => ['tv', 'televizyon', 'qled', 'oled'],
            'oyun' => ['oyun', 'game', 'playstation', 'xbox'],
            'giyim' => ['giyim', 'clothing', 'elbise', 'tshirt', 'polo'],
            'ayakkabı' => ['ayakkabı', 'shoe', 'nike', 'adidas'],
            'çanta' => ['çanta', 'bag', 'backpack', 'handbag'],
            'aksesuar' => ['aksesuar', 'accessory', 'jewelry', 'watch']
        ];
        
        foreach ($productKeywords as $category => $keywords) {
            // Kullanıcı sorgusunda bu kategori var mı?
            $queryHasCategory = false;
            foreach ($keywords as $keyword) {
                if (mb_strpos(mb_strtolower($expandedQuery['original_query']), $keyword) !== false) {
                    $queryHasCategory = true;
                    break;
                }
            }
            
            // Chunk'ta bu kategoriden ürün var mı?
            if ($queryHasCategory) {
                foreach ($keywords as $keyword) {
                    if (mb_strpos(mb_strtolower($content), $keyword) !== false) {
                        $score += 0.4; // Kategori eşleşmesi için yüksek bonus
                        break;
                    }
                }
            }
        }
        
        // Maksimum 1.0 puan
        return min($score, 1.0);
    }

    /**
     * Chunk'ta eşleşen terimleri bulur
     */
    private function findMatchedTerms(array $chunk, array $expandedQuery): array
    {
        $matchedTerms = [];
        $content = mb_strtolower($chunk['content']);
        
        // Ana terimler
        foreach ($expandedQuery['expanded_terms'] as $term) {
            if (mb_strpos($content, $term) !== false) {
                $matchedTerms[] = [
                    'term' => $term,
                    'type' => 'primary',
                    'score' => 0.3
                ];
            }
        }
        
        // Benzer kelimeler
        foreach ($expandedQuery['similar_words'] as $word) {
            if (mb_strpos($content, $word) !== false) {
                $matchedTerms[] = [
                    'term' => $word,
                    'type' => 'similar',
                    'score' => 0.2
                ];
            }
        }
        
        return $matchedTerms;
    }

    /**
     * Fuzzy matching ile ürün arama
     */
    public function fuzzyProductSearch(string $query, array $products, array $context = []): array
    {
        try {
            // Query expansion yap
            $expandedQuery = $this->expandQuery($query, $context);
            
            // Her ürün için fuzzy score hesapla
            $scoredProducts = [];
            foreach ($products as $product) {
                $fuzzyScore = $this->calculateFuzzyScore($product, $expandedQuery);
                
                if ($fuzzyScore >= 0.6) { // %60+ eşleşme
                    $product['fuzzy_score'] = $fuzzyScore;
                    $product['matched_attributes'] = $this->findMatchedAttributes($product, $expandedQuery);
                    $scoredProducts[] = $product;
                }
            }
            
            // Fuzzy score'a göre sırala
            usort($scoredProducts, function($a, $b) {
                return $b['fuzzy_score'] <=> $a['fuzzy_score'];
            });
            
            return [
                'query' => $query,
                'expanded_query' => $expandedQuery,
                'products' => $scoredProducts,
                'total_found' => count($scoredProducts),
                'search_type' => 'fuzzy_matching'
            ];
            
        } catch (\Exception $e) {
            Log::error('Fuzzy product search error: ' . $e->getMessage());
            return [
                'query' => $query,
                'expanded_query' => null,
                'products' => [],
                'total_found' => 0,
                'search_type' => 'fallback',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Ürün için fuzzy score hesaplar
     */
    private function calculateFuzzyScore(array $product, array $expandedQuery): float
    {
        $score = 0.0;
        
        // Ürün adı
        if (isset($product['name'])) {
            $productName = mb_strtolower($product['name']);
            foreach ($expandedQuery['expanded_terms'] as $term) {
                if (mb_strpos($productName, $term) !== false) {
                    $score += 0.4;
                }
            }
        }
        
        // Kategori
        if (isset($product['category'])) {
            $category = mb_strtolower($product['category']);
            foreach ($expandedQuery['related_categories'] as $relatedCategory) {
                if (mb_strpos($category, $relatedCategory) !== false) {
                    $score += 0.3;
                }
            }
        }
        
        // Marka
        if (isset($product['brand'])) {
            $brand = mb_strtolower($product['brand']);
            foreach ($expandedQuery['expanded_terms'] as $term) {
                if (mb_strpos($brand, $term) !== false) {
                    $score += 0.2;
                }
            }
        }
        
        // Açıklama
        if (isset($product['description'])) {
            $description = mb_strtolower($product['description']);
            foreach ($expandedQuery['expanded_terms'] as $term) {
                if (mb_strpos($description, $term) !== false) {
                    $score += 0.1;
                }
            }
        }
        
        return min($score, 1.0);
    }

    /**
     * Üründe eşleşen özellikleri bulur
     */
    private function findMatchedAttributes(array $product, array $expandedQuery): array
    {
        $matchedAttributes = [];
        
        if (isset($product['name'])) {
            $productName = mb_strtolower($product['name']);
            foreach ($expandedQuery['expanded_terms'] as $term) {
                if (mb_strpos($productName, $term) !== false) {
                    $matchedAttributes[] = [
                        'attribute' => 'name',
                        'term' => $term,
                        'value' => $product['name']
                    ];
                }
            }
        }
        
        if (isset($product['category'])) {
            $category = mb_strtolower($product['category']);
            foreach ($expandedQuery['related_categories'] as $relatedCategory) {
                if (mb_strpos($category, $relatedCategory) !== false) {
                    $matchedAttributes[] = [
                        'attribute' => 'category',
                        'term' => $relatedCategory,
                        'value' => $product['category']
                    ];
                }
            }
        }
        
        return $matchedAttributes;
    }

    /**
     * Resim içeriğini analiz eder ve ürün özelliklerini çıkarır
     */
    public function analyzeImageContent(string $imageUrl, string $context = ''): array
    {
        try {
            $systemPrompt = "Sen bir e-ticaret ürün analiz uzmanısın. Verilen ürün resmini detaylı olarak analiz et ve site ziyaretçilerine kapsamlı bilgi vermek üzere ürün özelliklerini belirle.

            Analiz kuralları:
            1. Ürünün türünü ve kategorisini net olarak belirle
            2. Görsel özellikleri detaylandır (renk, şekil, boyut, stil, kalıp)
            3. Malzeme ve kumaş özelliklerini belirle (varsa)
            4. Tasarım detaylarını açıkla (cepler, fermuarlar, dikişler, astar)
            5. Kullanım alanı ve sezon bilgisini belirle
            6. Bakım ve kullanım önerilerini ekle
            7. Hedef kitleyi belirle
            8. Türkçe yanıt ver
            9. Ürün türüne göre özel özellikleri vurgula

            Ürün türüne göre özel alanlar:
            - Giyim ürünleri: Kalıp, kumaş türü, cep detayları, fermuar, dikiş, astar
            - Ayakkabı: Taban, topuk, malzeme, bağcık, iç taban
            - Aksesuar: Malzeme, boyut, ayarlanabilir özellikler
            - Elektronik: Teknik özellikler, bağlantı türleri, güç
            - Ev eşyaları: Malzeme, boyut, montaj, kullanım alanı

            Yanıtı şu formatta ver:
            {
                \"product_type\": \"ürün türü\",
                \"category\": \"kategori\",
                \"visual_features\": {
                    \"color\": \"renk\",
                    \"style\": \"stil\",
                    \"fit\": \"kalıp\",
                    \"design\": \"tasarım özellikleri\"
                },
                \"material_features\": {
                    \"fabric\": \"kumaş türü\",
                    \"lining\": \"astar\",
                    \"hardware\": \"metal aksesuarlar\"
                },
                \"design_details\": {
                    \"pockets\": \"cep detayları\",
                    \"closures\": \"kapanma sistemi\",
                    \"stitching\": \"dikiş detayları\",
                    \"special_features\": \"özel özellikler\"
                },
                \"usage_info\": {
                    \"season\": \"sezon\",
                    \"occasion\": \"kullanım alanı\",
                    \"care_instructions\": \"bakım talimatları\"
                },
                \"target_audience\": \"hedef kitle\",
                \"summary\": \"kısa ürün özeti\"
            }";

            // OpenAI Vision API için gpt-4o kullan (resim analizi destekler)
            $visionModel = 'gpt-4o';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => $visionModel,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Bu bir e-ticaret sitesi ürünüdür. Bu ürünü site ziyaretçilerine doğru ve eksiksiz bilgi vermek üzere detaylı özelliklerini belirle. Ürün türüne göre özel özellikleri vurgula.'
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $imageUrl
                            ]
                        ]
                    ]]
                ],
                'max_tokens' => 800,
                'temperature' => 0.3
            ]);

            if (!$response->successful()) {
                throw new \Exception('OpenAI API Error: ' . $response->body());
            }

            $data = $response->json();
            $analysis = json_decode($data['choices'][0]['message']['content'], true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return $analysis;
            }

            // JSON parse hatası durumunda fallback
            return $this->fallbackImageAnalysis($imageUrl, $context);
            
        } catch (\Exception $e) {
            Log::error('OpenAI image analysis error: ' . $e->getMessage());
            return $this->fallbackImageAnalysis($imageUrl, $context);
        }
    }

    /**
     * Fallback image analysis - basit ürün türü tespiti
     */
    private function fallbackImageAnalysis(string $imageUrl, string $context): array
    {
        // URL'den basit ürün türü tespiti
        $url = strtolower($imageUrl);
        $productType = 'genel ürün';
        $category = 'genel';
        
        if (strpos($url, 'saat') !== false || strpos($url, 'watch') !== false) {
            $productType = 'akıllı saat';
            $category = 'elektronik';
        } elseif (strpos($url, 'telefon') !== false || strpos($url, 'phone') !== false) {
            $productType = 'telefon';
            $category = 'elektronik';
        } elseif (strpos($url, 'bilgisayar') !== false || strpos($url, 'computer') !== false || strpos($url, 'laptop') !== false) {
            $productType = 'bilgisayar';
            $category = 'elektronik';
        } elseif (strpos($url, 'giyim') !== false || strpos($url, 'clothing') !== false || strpos($url, 'tshirt') !== false) {
            $productType = 'giyim ürünü';
            $category = 'giyim';
        } elseif (strpos($url, 'ayakkabı') !== false || strpos($url, 'shoe') !== false) {
            $productType = 'ayakkabı';
            $category = 'giyim';
        }

        return [
            'product_type' => $productType,
            'category' => $category,
            'visual_features' => [
                'color' => 'belirlenemedi',
                'style' => 'belirlenemedi',
                'fit' => 'belirlenemedi',
                'design' => 'belirlenemedi'
            ],
            'material_features' => [
                'fabric' => 'belirlenemedi',
                'lining' => 'belirlenemedi',
                'hardware' => 'belirlenemedi'
            ],
            'design_details' => [
                'pockets' => 'belirlenemedi',
                'closures' => 'belirlenemedi',
                'stitching' => 'belirlenemedi',
                'special_features' => 'belirlenemedi'
            ],
            'usage_info' => [
                'season' => 'belirlenemedi',
                'occasion' => 'genel kullanım',
                'care_instructions' => 'belirlenemedi'
            ],
            'target_audience' => 'genel kullanıcı',
            'summary' => $context ?: 'Ürün resmi analiz edilemedi, fallback bilgi kullanıldı'
        ];
    }

    /**
     * Chunk içeriğindeki resim URL'lerini tespit eder ve analiz eder
     */
    public function processChunkImages(string $content, string $context = ''): array
    {
        try {
            // Resim URL'lerini tespit et
            $imageUrls = $this->extractImageUrls($content);
            
            if (empty($imageUrls)) {
                return [
                    'has_images' => false,
                    'image_vision' => null,
                    'processed_images' => 0
                ];
            }

            // İlk resmi analiz et (birden fazla resim varsa ilkini al)
            $firstImageUrl = $imageUrls[0];
            $imageAnalysis = $this->analyzeImageContent($firstImageUrl, $context);
            
            // image_vision field'ı için JSON formatında kaydet
            $imageVision = json_encode($imageAnalysis, JSON_UNESCAPED_UNICODE);
            
            Log::info('Image analysis completed for chunk', [
                'image_url' => $firstImageUrl,
                'analysis' => $imageAnalysis,
                'context' => $context
            ]);

            return [
                'has_images' => true,
                'image_vision' => $imageVision,
                'processed_images' => count($imageUrls),
                'image_urls' => $imageUrls,
                'analysis' => $imageAnalysis
            ];
            
        } catch (\Exception $e) {
            Log::error('Chunk image processing error: ' . $e->getMessage());
            return [
                'has_images' => false,
                'image_vision' => null,
                'processed_images' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Content'ten resim URL'lerini çıkarır
     */
    private function extractImageUrls(string $content): array
    {
        $imageUrls = [];
        
        // HTML img tag'lerini bul
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
        if (!empty($matches[1])) {
            $imageUrls = array_merge($imageUrls, $matches[1]);
        }
        
        // Markdown resim syntax'ını bul
        preg_match_all('/!\[([^\]]*)\]\(([^)]+)\)/i', $content, $matches);
        if (!empty($matches[2])) {
            $imageUrls = array_merge($imageUrls, $matches[2]);
        }
        
        // Plain text URL'leri bul (http/https ile başlayan)
        preg_match_all('/https?:\/\/[^\s<>"\']+\.(jpg|jpeg|png|gif|webp|svg)/i', $content, $matches);
        if (!empty($matches[0])) {
            $imageUrls = array_merge($imageUrls, $matches[0]);
        }
        
        // JSON içeriğindeki resim URL'lerini bul
        try {
            $jsonData = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                $this->extractImageUrlsFromJson($jsonData, $imageUrls);
            }
        } catch (\Exception $e) {
            // JSON parse hatası durumunda devam et
        }
        
        // Duplicate'leri kaldır ve boş olanları filtrele
        $imageUrls = array_filter(array_unique($imageUrls), function($url) {
            return !empty(trim($url)) && filter_var($url, FILTER_VALIDATE_URL);
        });
        
        return array_values($imageUrls);
    }

    /**
     * JSON verisinden resim URL'lerini çıkarır
     */
    private function extractImageUrlsFromJson(array $data, array &$imageUrls): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->extractImageUrlsFromJson($value, $imageUrls);
            } elseif (is_string($value) && $this->isImageUrl($value)) {
                $imageUrls[] = $value;
            } elseif (is_string($key) && strtolower($key) === 'image' && is_string($value) && $this->isImageUrl($value)) {
                $imageUrls[] = $value;
            }
        }
    }

    /**
     * String'in resim URL'i olup olmadığını kontrol eder
     */
    private function isImageUrl(string $url): bool
    {
        $url = trim($url);
        
        // Boş string kontrolü
        if (empty($url)) {
            return false;
        }
        
        // URL format kontrolü
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Resim uzantısı kontrolü
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
        $extension = strtolower($pathInfo['extension'] ?? '');
        
        return in_array($extension, $imageExtensions);
    }

    /**
     * Resimleri analiz eder ve image vision sonuçlarını döner
     */
    public function analyzeImages(array $imageUrls): ?string
    {
        try {
            if (empty($imageUrls)) {
                return null;
            }
            
            $analysisResults = [];
            
            foreach ($imageUrls as $imageUrl) {
                try {
                    $analysis = $this->analyzeSingleImage($imageUrl);
                    if ($analysis) {
                        $analysisResults[] = [
                            'url' => $imageUrl,
                            'analysis' => $analysis
                        ];
                    }
                } catch (\Exception $e) {
                    Log::warning("Image analysis failed for {$imageUrl}: " . $e->getMessage());
                    continue;
                }
            }
            
            if (empty($analysisResults)) {
                return null;
            }
            
            // Sonuçları JSON formatında döndür
            return json_encode([
                'total_images' => count($imageUrls),
                'analyzed_images' => count($analysisResults),
                'results' => $analysisResults,
                'analysis_timestamp' => now()->toISOString()
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            Log::error('Image analysis error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Tek bir resmi analiz eder
     */
    private function analyzeSingleImage(string $imageUrl): ?array
    {
        try {
            // OpenAI Vision API kullanarak resim analizi
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Bu resmi detaylı olarak analiz et ve şu bilgileri ver: 1) Resmin içeriği ve ne gösterdiği 2) Renkler ve stil 3) Varsa metin içeriği 4) Genel atmosfer ve duygu 5) Ticari kullanım için uygunluk. Sonucu JSON formatında döndür.'
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => $imageUrl
                                ]
                            ]
                        ]
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.1
            ]);
            
            if (!$response->successful()) {
                throw new \Exception('Vision API Error: ' . $response->body());
            }
            
            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            
            // JSON response'u parse etmeye çalış
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            
            // JSON parse edilemezse text olarak döndür
            return [
                'raw_analysis' => $content,
                'parsing_error' => 'Response JSON formatında değil'
            ];
            
        } catch (\Exception $e) {
            Log::error("Single image analysis error for {$imageUrl}: " . $e->getMessage());
            return null;
        }
    }
}
