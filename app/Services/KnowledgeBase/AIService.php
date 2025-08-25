<?php

namespace App\Services\KnowledgeBase;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AIService
{
    private $apiKey;
    private $baseUrl = 'https://api.openai.com/v1';
    private $model = 'gpt-3.5-turbo';
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
            
            return [
                'intent' => $intent['intent'],
                'confidence' => $intent['confidence'],
                'entities' => $intent['entities'] ?? [],
                'category' => $intent['category'] ?? 'general'
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI intent detection error: ' . $e->getMessage());
            return [
                'intent' => 'unknown',
                'confidence' => 0.0,
                'entities' => [],
                'category' => 'general'
            ];
        }
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
          Örnekler: 'saat varmı', 'telefon bul', 'kırmızı elbise', 'bana göre ürün', 'ne önerirsin'
        
        - product_info: Ürün bilgisi, detay, özellik
          Örnekler: 'bu ürünün özellikleri', 'fiyatı ne kadar', 'garanti süresi'
        
        - category_browse: Kategori keşfi, filtreleme
          Örnekler: 'elektronik kategorisi', 'giyim ürünleri', 'ev dekorasyonu'
        
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

        ÖNEMLİ: 'Bana göre saat varmı?' gibi sorgular product_search intent'ine aittir çünkü kullanıcı ürün arıyor.
        
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
        try {
            $parsed = json_decode($response, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }
        } catch (\Exception $e) {
            // JSON parse edilemezse fallback
        }

        // Fallback parsing - daha gelişmiş pattern matching
        $intent = 'unknown';
        $confidence = 0.5;
        
        // Saat, telefon, ürün arama gibi sorgular
        if (preg_match('/(saat|telefon|ürün|elbise|ayakkabı|bilgisayar|kitap|mobilya|elektronik|giyim|aksesuar|kozmetik|spor|ev|bahçe|oyuncak|kitap|müzik|film|oyun)/i', $response)) {
            $intent = 'product_search';
            $confidence = 0.85;
        }
        // "Bana göre", "ne önerirsin", "bul" gibi ifadeler
        elseif (preg_match('/(bana göre|ne önerirsin|bul|ara|göster|listele|var mı|mevcut)/i', $response)) {
            $intent = 'product_search';
            $confidence = 0.9;
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
}
