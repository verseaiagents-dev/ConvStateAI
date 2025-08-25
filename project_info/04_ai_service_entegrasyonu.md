# AI Service Entegrasyonu Planı

## Mevcut Durum Analizi

### TestAPI.php'deki AI Entegrasyonu
- **IntentDetectionService**: AI tabanlı intent tespiti
- **SmartProductRecommender**: Akıllı ürün önerisi
- **OpenAI API**: Temel AI entegrasyonu
- **Context-Aware Responses**: Session context ile yanıt üretimi

### Mevcut Özellikler
- Intent detection with confidence scoring
- AI-generated intents ve keywords
- Color-based product recommendations
- Category matching algorithms
- Brand preference detection

## Geliştirilecek AI Özellikleri

### 1. Advanced Intent Detection

#### Intent Classification System
```php
<?php

namespace App\Services\AI;

use OpenAI\Laravel\Facades\OpenAI;
use App\Services\IntentDetectionService;

class AdvancedIntentService
{
    private array $intentCategories = [
        'product_search' => [
            'keywords' => ['arama', 'bul', 'göster', 'ürün', 'kategori'],
            'confidence_threshold' => 0.7
        ],
        'product_comparison' => [
            'keywords' => ['karşılaştır', 'vs', 'hangisi', 'fark'],
            'confidence_threshold' => 0.8
        ],
        'price_inquiry' => [
            'keywords' => ['fiyat', 'kaç para', 'ne kadar', 'maliyet'],
            'confidence_threshold' => 0.75
        ],
        'technical_support' => [
            'keywords' => ['yardım', 'destek', 'sorun', 'hata', 'nasıl'],
            'confidence_threshold' => 0.8
        ],
        'general_inquiry' => [
            'keywords' => ['merhaba', 'selam', 'nasılsın', 'teşekkür'],
            'confidence_threshold' => 0.6
        ]
    ];

    public function detectIntentWithContext(string $message, array $conversationContext): array
    {
        // 1. Basic keyword matching
        $keywordScore = $this->calculateKeywordScore($message);
        
        // 2. AI-powered intent detection
        $aiIntent = $this->getAIIntent($message, $conversationContext);
        
        // 3. Context-aware scoring
        $contextScore = $this->calculateContextScore($aiIntent, $conversationContext);
        
        // 4. Final intent determination
        $finalIntent = $this->determineFinalIntent($keywordScore, $aiIntent, $contextScore);
        
        return $finalIntent;
    }

    private function getAIIntent(string $message, array $context): array
    {
        $prompt = $this->buildContextualPrompt($message, $context);
        
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Sen bir e-ticaret AI asistanısın. Kullanıcı mesajlarının intent\'ini tespit et.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.3,
            'max_tokens' => 150
        ]);

        return $this->parseAIResponse($response->choices[0]->message->content);
    }

    private function buildContextualPrompt(string $message, array $context): string
    {
        $contextInfo = '';
        
        if (!empty($context['current_category'])) {
            $contextInfo .= "Kullanıcı şu anda {$context['current_category']} kategorisinde. ";
        }
        
        if (!empty($context['last_products'])) {
            $contextInfo .= "Son görülen ürünler: " . implode(', ', array_column($context['last_products'], 'name')) . ". ";
        }
        
        if (!empty($context['user_preferences'])) {
            $contextInfo .= "Kullanıcı tercihleri: " . implode(', ', $context['user_preferences']) . ". ";
        }

        return "Context: {$contextInfo}\n\nKullanıcı mesajı: {$message}\n\nBu mesajın intent'ini belirle ve şu formatta yanıtla:\n{\n  \"intent\": \"intent_name\",\n  \"confidence\": 0.95,\n  \"entities\": {\n    \"category\": \"value\",\n    \"brand\": \"value\",\n    \"price_range\": \"value\"\n  },\n  \"reasoning\": \"Açıklama\"\n}";
    }
}
```

### 2. Smart Response Generation

#### AI Response Service
```php
<?php

namespace App\Services\AI;

use OpenAI\Laravel\Facades\OpenAI;
use App\Services\ProductData;
use App\Services\TemplateService;

class AIResponseService
{
    public function generateContextualResponse(
        string $message,
        array $intent,
        array $conversationContext,
        array $availableProducts
    ): array {
        // 1. Response template selection
        $template = $this->selectResponseTemplate($intent, $conversationContext);
        
        // 2. Product recommendations
        $recommendations = $this->generateProductRecommendations($intent, $conversationContext, $availableProducts);
        
        // 3. AI-generated response text
        $responseText = $this->generateResponseText($message, $intent, $recommendations, $template);
        
        // 4. Response enhancement
        $enhancedResponse = $this->enhanceResponse($responseText, $recommendations, $conversationContext);
        
        return $enhancedResponse;
    }

    private function generateResponseText(
        string $message,
        array $intent,
        array $recommendations,
        string $template
    ): string {
        $prompt = $this->buildResponsePrompt($message, $intent, $recommendations, $template);
        
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Sen bir e-ticaret AI asistanısın. Doğal, yardımcı ve satış odaklı yanıtlar ver.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 300
        ]);

        return $response->choices[0]->message->content;
    }

    private function buildResponsePrompt(
        string $message,
        array $intent,
        array $recommendations,
        string $template
    ): string {
        $productsInfo = '';
        if (!empty($recommendations)) {
            $productsInfo = "\nÖnerilen ürünler:\n";
            foreach ($recommendations as $product) {
                $productsInfo .= "- {$product['name']} ({$product['brand']}) - {$product['price']} TL\n";
            }
        }

        return "Kullanıcı mesajı: {$message}\nIntent: {$intent['intent']}\nTemplate: {$template}{$productsInfo}\n\nBu bilgilere göre doğal, yardımcı ve satış odaklı bir yanıt oluştur. Yanıt Türkçe olmalı ve ürün önerilerini içermeli.";
    }
}
```

### 3. Advanced Product Recommendations

#### Smart Recommendation Engine
```php
<?php

namespace App\Services\AI;

use App\Services\ProductData;
use App\Services\AnalyticsService;

class SmartRecommendationEngine
{
    private array $recommendationStrategies = [
        'collaborative_filtering' => 0.3,
        'content_based' => 0.4,
        'context_aware' => 0.2,
        'trending' => 0.1
    ];

    public function getPersonalizedRecommendations(
        string $sessionId,
        array $userPreferences,
        array $conversationContext,
        int $limit = 10
    ): array {
        // 1. Collaborative filtering
        $collaborativeRecs = $this->getCollaborativeRecommendations($sessionId, $limit);
        
        // 2. Content-based filtering
        $contentRecs = $this->getContentBasedRecommendations($userPreferences, $limit);
        
        // 3. Context-aware recommendations
        $contextRecs = $this->getContextAwareRecommendations($conversationContext, $limit);
        
        // 4. Trending products
        $trendingRecs = $this->getTrendingProducts($limit);
        
        // 5. Hybrid scoring and ranking
        $finalRecommendations = $this->hybridRanking([
            'collaborative' => $collaborativeRecs,
            'content' => $contentRecs,
            'context' => $contextRecs,
            'trending' => $trendingRecs
        ]);

        return array_slice($finalRecommendations, 0, $limit);
    }

    private function getCollaborativeRecommendations(string $sessionId, int $limit): array
    {
        // Similar user behavior analysis
        $similarUsers = $this->findSimilarUsers($sessionId);
        $recommendations = [];
        
        foreach ($similarUsers as $user) {
            $userProducts = $this->getUserViewedProducts($user['session_id']);
            foreach ($userProducts as $product) {
                if (!isset($recommendations[$product['id']])) {
                    $recommendations[$product['id']] = [
                        'product' => $product,
                        'score' => 0
                    ];
                }
                $recommendations[$product['id']]['score'] += $user['similarity'];
            }
        }
        
        // Sort by score and return top products
        uasort($recommendations, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return array_column(array_slice($recommendations, 0, $limit), 'product');
    }

    private function getContextAwareRecommendations(array $context, int $limit): array
    {
        $recommendations = [];
        
        // Category-based recommendations
        if (!empty($context['current_category'])) {
            $categoryProducts = $this->getProductsByCategory($context['current_category']);
            $recommendations = array_merge($recommendations, $categoryProducts);
        }
        
        // Brand-based recommendations
        if (!empty($context['current_brand'])) {
            $brandProducts = $this->getProductsByBrand($context['current_brand']);
            $recommendations = array_merge($recommendations, $brandProducts);
        }
        
        // Price range recommendations
        if (!empty($context['current_price_range'])) {
            $priceProducts = $this->getProductsByPriceRange($context['current_price_range']);
            $recommendations = array_merge($recommendations, $priceProducts);
        }
        
        // Remove duplicates and sort by relevance
        $recommendations = $this->deduplicateAndSort($recommendations, $context);
        
        return array_slice($recommendations, 0, $limit);
    }
}
```

### 4. Sentiment Analysis & Emotional Intelligence

#### Sentiment Analysis Service
```php
<?php

namespace App\Services\AI;

use OpenAI\Laravel\Facades\OpenAI;

class SentimentAnalysisService
{
    public function analyzeSentiment(string $message): array
    {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Kullanıcı mesajlarının duygu durumunu analiz et.'
                ],
                [
                    'role' => 'user',
                    'content' => "Bu mesajın duygu durumunu analiz et: {$message}"
                ]
            ],
            'temperature' => 0.1,
            'max_tokens' => 100
        ]);

        return $this->parseSentimentResponse($response->choices[0]->message->content);
    }

    public function adjustResponseTone(array $sentiment, string $response): string
    {
        $toneAdjustments = [
            'positive' => 'Daha olumlu ve motive edici',
            'negative' => 'Daha destekleyici ve yardımcı',
            'neutral' => 'Profesyonel ve bilgilendirici',
            'frustrated' => 'Daha sabırlı ve anlayışlı'
        ];

        $targetTone = $toneAdjustments[$sentiment['primary_emotion']] ?? 'profesyonel';
        
        $prompt = "Bu yanıtı {$targetTone} bir tonda yeniden yaz:\n\n{$response}";
        
        $adjustedResponse = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Yanıtları belirtilen tonda yeniden yaz.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 200
        ]);

        return $adjustedResponse->choices[0]->message->content;
    }
}
```

### 5. Conversational Memory & Learning

#### Conversation Memory Service
```php
<?php

namespace App\Services\AI;

use App\Repositories\ConversationRepository;
use App\Services\VectorService;

class ConversationMemoryService
{
    public function storeConversationMemory(
        string $sessionId,
        array $conversation,
        array $intents,
        array $outcomes
    ): void {
        // 1. Extract key information
        $keyInfo = $this->extractKeyInformation($conversation, $intents);
        
        // 2. Generate embeddings for semantic search
        $embeddings = $this->generateEmbeddings($keyInfo);
        
        // 3. Store in vector database
        $this->vectorService->storeEmbeddings($sessionId, $embeddings);
        
        // 4. Update conversation patterns
        $this->updateConversationPatterns($sessionId, $intents, $outcomes);
    }

    public function retrieveRelevantContext(
        string $sessionId,
        string $currentMessage,
        int $limit = 5
    ): array {
        // 1. Generate current message embedding
        $currentEmbedding = $this->generateEmbeddings($currentMessage);
        
        // 2. Find similar past conversations
        $similarConversations = $this->vectorService->findSimilar(
            $currentEmbedding,
            $limit
        );
        
        // 3. Extract relevant context
        $relevantContext = $this->extractRelevantContext($similarConversations);
        
        return $relevantContext;
    }

    private function generateEmbeddings(string $text): array
    {
        $response = OpenAI::embeddings()->create([
            'model' => 'text-embedding-ada-002',
            'input' => $text
        ]);

        return $response->embeddings[0]->embedding;
    }
}
```

## AI Model Configuration

### 1. Model Selection Strategy
```php
<?php

namespace App\Services\AI;

class ModelSelectionService
{
    private array $modelConfigs = [
        'gpt-4' => [
            'use_case' => 'complex_reasoning',
            'max_tokens' => 1000,
            'temperature' => 0.3,
            'cost_per_1k_tokens' => 0.03
        ],
        'gpt-3.5-turbo' => [
            'use_case' => 'general_conversation',
            'max_tokens' => 500,
            'temperature' => 0.7,
            'cost_per_1k_tokens' => 0.002
        ],
        'claude-3-sonnet' => [
            'use_case' => 'creative_writing',
            'max_tokens' => 800,
            'temperature' => 0.5,
            'cost_per_1k_tokens' => 0.015
        ]
    ];

    public function selectOptimalModel(string $useCase, float $budget): string
    {
        $suitableModels = array_filter($this->modelConfigs, function($config) use ($useCase) {
            return $config['use_case'] === $useCase;
        });

        // Select based on budget and performance
        return $this->selectModelByBudget($suitableModels, $budget);
    }
}
```

### 2. Prompt Engineering & Management
```php
<?php

namespace App\Services\AI;

class PromptManagementService
{
    private array $promptTemplates = [
        'intent_detection' => [
            'system' => 'Sen bir e-ticaret AI asistanısın. Kullanıcı mesajlarının intent\'ini tespit et.',
            'examples' => [
                'iPhone fiyatı' => 'product_search',
                'Hangisi daha iyi?' => 'product_comparison',
                'Yardım almak istiyorum' => 'technical_support'
            ]
        ],
        'product_recommendation' => [
            'system' => 'Kullanıcı tercihlerine göre en uygun ürünleri öner.',
            'examples' => [
                'Spor ayakkabı' => 'Nike, Adidas, Puma spor ayakkabıları',
                'Ucuz telefon' => 'Bütçe dostu smartphone seçenekleri'
            ]
        ]
    ];

    public function getOptimizedPrompt(string $templateName, array $variables): string
    {
        $template = $this->promptTemplates[$templateName] ?? '';
        
        if (empty($template)) {
            throw new \InvalidArgumentException("Template not found: {$templateName}");
        }

        return $this->interpolateTemplate($template, $variables);
    }
}
```

## Performance & Cost Optimization

### 1. Response Caching
```php
<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;

class AICacheService
{
    public function getCachedResponse(string $cacheKey): ?array
    {
        return Cache::get("ai_response:{$cacheKey}");
    }

    public function cacheResponse(string $cacheKey, array $response, int $ttl = 3600): void
    {
        Cache::put("ai_response:{$cacheKey}", $response, $ttl);
    }

    public function generateCacheKey(string $message, array $context): string
    {
        $contextHash = md5(json_encode($context));
        return md5($message . $contextHash);
    }
}
```

### 2. Token Usage Optimization
```php
<?php

namespace App\Services\AI;

class TokenOptimizationService
{
    public function optimizePrompt(string $prompt, int $maxTokens = 1000): string
    {
        // 1. Remove unnecessary whitespace
        $prompt = trim($prompt);
        
        // 2. Truncate if too long
        if (strlen($prompt) > $maxTokens * 4) { // Rough estimation
            $prompt = substr($prompt, 0, $maxTokens * 4);
        }
        
        // 3. Remove redundant information
        $prompt = $this->removeRedundantInfo($prompt);
        
        return $prompt;
    }

    public function estimateTokenCount(string $text): int
    {
        // Rough estimation: 1 token ≈ 4 characters
        return ceil(strlen($text) / 4);
    }
}
```

## Monitoring & Analytics

### 1. AI Performance Metrics
```php
<?php

namespace App\Services\AI;

class AIPerformanceMonitor
{
    public function trackResponseMetrics(array $metrics): void
    {
        // 1. Response time
        $this->trackResponseTime($metrics['response_time']);
        
        // 2. Token usage
        $this->trackTokenUsage($metrics['tokens_used']);
        
        // 3. Cost tracking
        $this->trackCost($metrics['cost']);
        
        // 4. User satisfaction
        $this->trackUserSatisfaction($metrics['feedback']);
    }

    public function generatePerformanceReport(): array
    {
        return [
            'average_response_time' => $this->getAverageResponseTime(),
            'total_tokens_used' => $this->getTotalTokenUsage(),
            'total_cost' => $this->getTotalCost(),
            'user_satisfaction_rate' => $this->getUserSatisfactionRate(),
            'intent_accuracy' => $this->getIntentAccuracy()
        ];
    }
}
```

## Geliştirme Timeline

### Phase 1: Advanced Intent Detection (1-2 hafta)
1. Enhanced intent classification
2. Context-aware intent detection
3. Confidence scoring improvements

### Phase 2: Smart Response Generation (2-3 hafta)
1. AI-powered response generation
2. Template-based responses
3. Contextual response enhancement

### Phase 3: Recommendation Engine (2-3 hafta)
1. Collaborative filtering
2. Content-based filtering
3. Hybrid recommendation system

### Phase 4: Sentiment Analysis (1-2 hafta)
1. Emotion detection
2. Response tone adjustment
3. User satisfaction tracking

### Phase 5: Memory & Learning (2-3 hafta)
1. Conversation memory
2. Pattern learning
3. Continuous improvement

### Phase 6: Optimization & Monitoring (1-2 hafta)
1. Performance optimization
2. Cost optimization
3. Monitoring & analytics

## Sonraki Adımlar

1. **Advanced Intent Detection implement et**
2. **Smart Response Generation kur**
3. **Recommendation Engine geliştir**
4. **Sentiment Analysis ekle**
5. **Memory & Learning sistemi kur**
6. **Performance optimization yap**
7. **Monitoring & analytics implement et**
