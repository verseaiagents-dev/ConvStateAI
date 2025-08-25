<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AIFieldMappingService
{
    /**
     * AI ile field mapping yap
     */
    public function performFieldMapping($apiUrl, $apiType)
    {
        try {
            // API'yi test et
            $response = Http::timeout(10)->get($apiUrl);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'API erişilemiyor veya hata döndürüyor',
                    'error' => 'HTTP ' . $response->status()
                ];
            }

            $apiResponse = $response->json() ?: $response->body();
            
            // AI field mapping işlemi simüle et (gerçek AI entegrasyonu için OpenAI/Claude API kullanılabilir)
            $mapping = $this->analyzeAndMapFields($apiResponse, $apiType);
            
            // Mapping sonucunu cache'le
            $cacheKey = "field_mapping_{$apiType}_" . md5($apiUrl);
            Cache::put($cacheKey, $mapping, now()->addDays(30));
            
            return [
                'success' => true,
                'message' => 'Field mapping başarıyla tamamlandı',
                'mapping' => $mapping,
                'api_response_sample' => $apiResponse
            ];
            
        } catch (\Exception $e) {
            Log::error('AI Field Mapping Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Field mapping sırasında hata oluştu',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * API response'u analiz edip field mapping yap
     */
    private function analyzeAndMapFields($apiResponse, $apiType)
    {
        // Gerçek AI entegrasyonu için bu kısım OpenAI/Claude API ile değiştirilebilir
        $mapping = [
            'api_type' => $apiType,
            'mapped_at' => now()->toISOString(),
            'confidence_score' => 0.95,
            'fields' => []
        ];

        if ($apiType === 'order_status') {
            $mapping['fields'] = $this->mapOrderStatusFields($apiResponse);
        } elseif ($apiType === 'cargo_tracking') {
            $mapping['fields'] = $this->mapCargoTrackingFields($apiResponse);
        }

        return $mapping;
    }

    /**
     * Sipariş durumu API field mapping
     */
    private function mapOrderStatusFields($apiResponse)
    {
        $fields = [];
        
        // Ana alanları eşle
        $fieldMappings = [
            'order_id' => ['order_id', 'id', 'order_number', 'orderNumber'],
            'status' => ['status', 'order_status', 'orderStatus', 'state'],
            'customer_name' => ['customer_name', 'customerName', 'name', 'full_name'],
            'order_date' => ['order_date', 'orderDate', 'created_at', 'createdAt'],
            'estimated_delivery' => ['estimated_delivery', 'estimatedDelivery', 'delivery_date', 'deliveryDate'],
            'total_amount' => ['total_amount', 'totalAmount', 'total', 'price', 'amount'],
            'tracking_number' => ['tracking_number', 'trackingNumber', 'tracking_id', 'trackingId'],
            'carrier' => ['carrier', 'shipping_carrier', 'shippingCarrier', 'courier']
        ];

        foreach ($fieldMappings as $standardField => $possibleNames) {
            $value = $this->findFieldValue($apiResponse, $possibleNames);
            if ($value !== null) {
                $fields[$standardField] = [
                    'value' => $value,
                    'confidence' => 0.9,
                    'source_path' => $this->findFieldPath($apiResponse, $possibleNames)
                ];
            }
        }

        // Ürün listesi eşle
        if (isset($apiResponse['data']['items']) || isset($apiResponse['items'])) {
            $items = $apiResponse['data']['items'] ?? $apiResponse['items'];
            $fields['items'] = [
                'value' => $items,
                'confidence' => 0.85,
                'source_path' => 'data.items veya items'
            ];
        }

        return $fields;
    }

    /**
     * Kargo takip API field mapping
     */
    private function mapCargoTrackingFields($apiResponse)
    {
        $fields = [];
        
        // Ana alanları eşle
        $fieldMappings = [
            'tracking_number' => ['tracking_number', 'trackingNumber', 'tracking_id', 'trackingId', 'code'],
            'status' => ['status', 'tracking_status', 'trackingStatus', 'state', 'current_status'],
            'current_location' => ['current_location', 'currentLocation', 'location', 'current_city', 'currentCity'],
            'estimated_delivery' => ['estimated_delivery', 'estimatedDelivery', 'delivery_date', 'deliveryDate', 'eta'],
            'carrier' => ['carrier', 'shipping_carrier', 'shippingCarrier', 'courier', 'company'],
            'timeline' => ['timeline', 'history', 'events', 'tracking_history', 'trackingHistory']
        ];

        foreach ($fieldMappings as $standardField => $possibleNames) {
            $value = $this->findFieldValue($apiResponse, $possibleNames);
            if ($value !== null) {
                $fields[$standardField] = [
                    'value' => $value,
                    'confidence' => 0.9,
                    'source_path' => $this->findFieldPath($apiResponse, $possibleNames)
                ];
            }
        }

        return $fields;
    }

    /**
     * API response'da field değerini bul
     */
    private function findFieldValue($data, $possibleNames, $path = '')
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $currentPath = $path ? $path . '.' . $key : $key;
                
                // Doğrudan eşleşme kontrol et
                if (in_array($key, $possibleNames)) {
                    return $value;
                }
                
                // Alt seviyelerde ara
                if (is_array($value)) {
                    $result = $this->findFieldValue($value, $possibleNames, $currentPath);
                    if ($result !== null) {
                        return $result;
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * Field'ın API response'daki yolunu bul
     */
    private function findFieldPath($data, $possibleNames, $path = '')
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $currentPath = $path ? $path . '.' . $key : $key;
                
                if (in_array($key, $possibleNames)) {
                    return $currentPath;
                }
                
                if (is_array($value)) {
                    $result = $this->findFieldPath($value, $possibleNames, $currentPath);
                    if ($result !== null) {
                        return $result;
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * Field mapping sonuçlarını getir
     */
    public function getFieldMapping($apiType, $apiUrl)
    {
        $cacheKey = "field_mapping_{$apiType}_" . md5($apiUrl);
        return Cache::get($cacheKey);
    }

    /**
     * Field mapping kalitesini değerlendir
     */
    public function evaluateMappingQuality($mapping)
    {
        if (!$mapping || !isset($mapping['fields'])) {
            return [
                'score' => 0,
                'quality' => 'poor',
                'missing_fields' => [],
                'suggestions' => []
            ];
        }

        $requiredFields = $this->getRequiredFields($mapping['api_type']);
        $foundFields = array_keys($mapping['fields']);
        $missingFields = array_diff($requiredFields, $foundFields);
        
        $score = count($foundFields) / count($requiredFields) * 100;
        
        $quality = 'excellent';
        if ($score < 50) $quality = 'poor';
        elseif ($score < 75) $quality = 'fair';
        elseif ($score < 90) $quality = 'good';

        return [
            'score' => round($score, 1),
            'quality' => $quality,
            'missing_fields' => $missingFields,
            'suggestions' => $this->getSuggestions($missingFields, $mapping['api_type'])
        ];
    }

    /**
     * API tipine göre gerekli alanları getir
     */
    private function getRequiredFields($apiType)
    {
        if ($apiType === 'order_status') {
            return ['order_id', 'status', 'estimated_delivery'];
        } elseif ($apiType === 'cargo_tracking') {
            return ['tracking_number', 'status', 'current_location'];
        }
        
        return [];
    }

    /**
     * Eksik alanlar için öneriler getir
     */
    private function getSuggestions($missingFields, $apiType)
    {
        $suggestions = [];
        
        foreach ($missingFields as $field) {
            if ($apiType === 'order_status') {
                $suggestions[$field] = $this->getOrderStatusFieldSuggestions($field);
            } elseif ($apiType === 'cargo_tracking') {
                $suggestions[$field] = $this->getCargoTrackingFieldSuggestions($field);
            }
        }
        
        return $suggestions;
    }

    private function getOrderStatusFieldSuggestions($field)
    {
        $suggestions = [
            'order_id' => 'API response\'da "id", "order_number", "orderNumber" gibi alanlar olmalı',
            'status' => 'API response\'da "status", "order_status", "state" gibi alanlar olmalı',
            'estimated_delivery' => 'API response\'da "estimated_delivery", "delivery_date", "eta" gibi alanlar olmalı'
        ];
        
        return $suggestions[$field] ?? 'Bu alan için uygun mapping bulunamadı';
    }

    private function getCargoTrackingFieldSuggestions($field)
    {
        $suggestions = [
            'tracking_number' => 'API response\'da "tracking_number", "code", "tracking_id" gibi alanlar olmalı',
            'status' => 'API response\'da "status", "tracking_status", "state" gibi alanlar olmalı',
            'current_location' => 'API response\'da "current_location", "location", "city" gibi alanlar olmalı'
        ];
        
        return $suggestions[$field] ?? 'Bu alan için uygun mapping bulunamadı';
    }
}
