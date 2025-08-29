import { useState, useCallback } from 'react';

interface AIResponse {
  type: string;
  message: string;
  products?: any[];
  intent?: string;
  confidence?: number;
  suggestions?: string[];
  data?: any;
  session_id?: string;
}

interface AIServiceOptions {
  apiKey?: string;
  model?: string;
  temperature?: number;
  maxTokens?: number;
}

export const useAIService = (options: AIServiceOptions = {}) => {
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // Session management sistemi
  const getOrCreateSession = useCallback(() => {
    const existingSession = localStorage.getItem('chat_session_id');
    if (existingSession) {
      return existingSession;
    }
    
    // Yeni session oluştur
    const newSessionId = `chat_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    localStorage.setItem('chat_session_id', newSessionId);
    return newSessionId;
  }, []);

  // AI Service with API integration - Ana fonksiyon
  const generateAIResponse = useCallback(async (userMessage: string, context?: any): Promise<AIResponse> => {
    try {
      setIsLoading(true);
      setError(null);
      
      const sessionId = getOrCreateSession();
      
      // Doğrudan Laravel backend'e istek at
      const apiUrl = 'http://127.0.0.1:8000/api/chat';
      
      console.log('Sending request to:', apiUrl);
      console.log('Request payload:', {
        message: userMessage,
        session_id: sessionId,
        context: context
      });
      
      const response = await fetch(apiUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Origin': 'http://localhost:3000'
        },
        body: JSON.stringify({
          message: userMessage,
          session_id: sessionId,
          context: context
        })
      });

      console.log('Response status:', response.status);
      console.log('Response headers:', response.headers);

      if (!response.ok) {
        const errorText = await response.text();
        console.error('API Error Response:', errorText);
        throw new Error(`API Error: ${response.status} - ${errorText}`);
      }

      const data = await response.json();
      console.log('API Response Data:', data);
      
      if (data.error) {
        throw new Error(data.error);
      }

      // Transform API response to AIResponse format
      const aiResponse: AIResponse = {
        type: data.type || 'general',
        message: data.message || 'Üzgünüm, şu anda yanıt veremiyorum.',
        products: data.data?.products || data.products || [],
        intent: data.intent || data.type || 'general',
        confidence: data.confidence || 0.8,
        suggestions: data.suggestions || [],
        session_id: data.session_id || sessionId,
        data: data.data || {}
      };

      console.log('Transformed AI Response:', aiResponse);
      
      // Products field'ını kontrol et
      if (aiResponse.products && aiResponse.products.length > 0) {
        console.log('Products found:', aiResponse.products);
        console.log('Products count:', aiResponse.products.length);
        console.log('First product:', aiResponse.products[0]);
        
        // Products varsa ve type product_search veya category_browse ise product_recommendation yap
        if (aiResponse.type === 'product_search' || aiResponse.type === 'category_browse') {
          aiResponse.type = 'product_recommendation';
          console.log(`Updated type from ${aiResponse.type} to product_recommendation`);
        }
      } else {
        console.log('No products in response');
        console.log('data.data?.products:', data.data?.products);
        console.log('data.products:', data.products);
      }
      
      // Response validation - products varsa type'ı product_recommendation yap
      if (aiResponse.products && aiResponse.products.length > 0 && 
          (aiResponse.type === 'product_search' || aiResponse.type === 'category_browse')) {
        aiResponse.type = 'product_recommendation';
        console.log('Updated type to product_recommendation');
      }
      
      return aiResponse;
      
    } catch (error) {
      console.error('AI Service Error:', error);
      setError(error instanceof Error ? error.message : 'Bilinmeyen hata oluştu');
      
      // Fallback to local response if API fails
      return generateLocalResponse(userMessage, context);
    } finally {
      setIsLoading(false);
    }
  }, [getOrCreateSession]);

  // Local AI logic (fallback) - Sadece API başarısız olduğunda kullanılır
  const generateLocalResponse = useCallback((userMessage: string, context?: any): AIResponse => {
    const message = userMessage.toLowerCase();
    
    if (message.includes('ürün') || message.includes('product') || message.includes('satın al')) {
      return {
        type: 'product_recommendation',
        message: 'Ürün önerileri için API bağlantısı gerekli. Lütfen daha sonra tekrar deneyin.',
        products: [],
        intent: 'product_inquiry',
        confidence: 0.5,
        suggestions: ['Tekrar dene', 'Yardım al']
      };
    } else if (message.includes('merhaba') || message.includes('hello') || message.includes('selam')) {
      return {
        type: 'greeting',
        message: 'Merhaba! Ben Kadir, senin dijital asistanınım. Size nasıl yardımcı olabilirim?',
        intent: 'greeting',
        confidence: 0.95,
        suggestions: ['Ürünleri göster', 'Yardım al', 'SSS']
      };
    } else if (message.includes('yardım') || message.includes('help') || message.includes('destek')) {
      return {
        type: 'help',
        message: 'Size yardımcı olmak için buradayım! Ürünler hakkında bilgi almak, sipariş vermek veya herhangi bir sorunuzu çözmek için bana yazabilirsiniz.',
        intent: 'help_request',
        confidence: 0.9,
        suggestions: ['Ürün katalogu', 'Sipariş takibi', 'İade işlemleri']
      };
    } else if (message.includes('fiyat') || message.includes('price') || message.includes('maliyet')) {
      return {
        type: 'price_inquiry',
        message: 'Ürün fiyatları hakkında bilgi almak istiyorsunuz. Hangi ürünün fiyatını öğrenmek istiyorsunuz?',
        intent: 'price_inquiry',
        confidence: 0.85,
        suggestions: ['Smart Watch Pro', 'Wireless Earbuds', 'Smart Speaker']
      };
    } else if (message.includes('kargo') || message.includes('cargo') || message.includes('kargom') || message.includes('takip') || message.includes('tracking')) {
      return {
        type: 'cargo_tracking',
        message: 'Kargo takip numaranızı girerek kargo durumunuzu öğrenebilirsiniz.',
        intent: 'cargo_tracking',
        confidence: 0.9,
        suggestions: ['Kargo takip', 'Sipariş durumu', 'Teslimat bilgisi']
      };
    } else if (message.includes('sipariş') || message.includes('order') || message.includes('siparişim')) {
      return {
        type: 'order_tracking',
        message: 'Sipariş numaranızı girerek sipariş durumunuzu ve kargo bilgilerinizi öğrenebilirsiniz.',
        intent: 'order_tracking',
        confidence: 0.9,
        suggestions: ['Sipariş takip', 'Kargo durumu', 'Teslimat bilgisi']
      };
    } else {
      return {
        type: 'general',
        message: 'Anlıyorum. Size daha iyi yardımcı olabilmem için biraz daha detay verebilir misiniz?',
        intent: 'general_inquiry',
        confidence: 0.7,
        suggestions: ['Ürünler hakkında bilgi', 'Teknik destek', 'Sipariş yardımı']
      };
    }
  }, []);

  // parseAIResponse fonksiyonu artık kullanılmıyor, kaldırıldı

  // Laravel API call - generateAIResponse ile aynı mantık
  const generateResponse = useCallback(async (userMessage: string, sessionId?: string): Promise<AIResponse> => {
    return generateAIResponse(userMessage, { sessionId });
  }, [generateAIResponse]);

  // Event handling için yeni fonksiyonlar
  const sendFeedback = useCallback(async (feedbackData: any) => {
    try {
      const response = await fetch((window as any).buildApiUrl('/api/feedback'), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(feedbackData)
      });

      if (!response.ok) {
        throw new Error(`Feedback API Error: ${response.status}`);
      }

      return await response.json();
    } catch (err) {
      setError('Feedback gönderilemedi');
    }
  }, []);

  const sendProductClick = useCallback(async (productData: any) => {
    try {
      const response = await fetch((window as any).buildApiUrl('/api/product-click'), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(productData)
      });

      if (!response.ok) {
        throw new Error(`Product Click API Error: ${response.status}`);
      }

      return await response.json();
    } catch (err) {
      setError('Ürün tıklama kaydedilemedi');
    }
  }, []);

  const sendCargoTracking = useCallback(async (cargoData: any) => {
    try {
      const response = await fetch((window as any).buildApiUrl('/api/cargo-tracking'), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(cargoData)
      });

      if (!response.ok) {
        throw new Error(`Cargo Tracking API Error: ${response.status}`);
      }

      return await response.json();
    } catch (err) {
      setError('Kargo takip bilgisi alınamadı');
    }
  }, []);

  return {
    generateResponse,
    generateAIResponse, // New API-based function
    sendFeedback,
    sendProductClick,
    sendCargoTracking,
    isLoading,
    error,
    clearError: () => setError(null)
  };
};
