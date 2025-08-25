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

  // defaultOptions artık kullanılmıyor, kaldırıldı

  // Local AI logic (fallback) - Önce tanımlanmalı
  const generateLocalResponse = useCallback((userMessage: string, context?: any): AIResponse => {
    const message = userMessage.toLowerCase();
    
    if (message.includes('ürün') || message.includes('product') || message.includes('satın al')) {
      return {
        type: 'product_recommendation',
        message: '',
        products: [
          {
            id: '1',
            name: 'Smart Watch Pro',
            brand: 'TechBrand',
            price: 299.99,
            image: '/imgs/smartwatch.jpeg',
            category: 'Electronics',
            rating: 4.5
          },
          {
            id: '2',
            name: 'Wireless Earbuds',
            brand: 'AudioTech',
            price: 149.99,
            image: '/imgs/earbuds.jpeg',
            category: 'Electronics',
            rating: 4.3
          },
          {
            id: '3',
            name: 'Smart Speaker',
            brand: 'HomeTech',
            price: 199.99,
            image: '/imgs/smartspeaker.jpeg',
            category: 'Electronics',
            rating: 4.7
          }
        ],
        intent: 'product_inquiry',
        confidence: 0.9,
        suggestions: ['Daha fazla ürün göster', 'Fiyat bilgisi', 'Teknik özellikler']
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

  // Laravel API call
  const generateResponse = useCallback(async (userMessage: string, sessionId?: string): Promise<AIResponse> => {
    setIsLoading(true);
    setError(null);

    try {
      // Session ID'yi kullan veya yeni oluştur
      const currentSessionId = sessionId || getOrCreateSession();
      
      // Laravel API'ye POST request gönder - Doğrudan Laravel sunucusuna
      const apiUrl = 'http://127.0.0.1:8000/api/chat';
  
      const response = await fetch(apiUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          message: userMessage,
          session_id: currentSessionId
        })
      });

      if (!response.ok) {
        throw new Error(`API Error: ${response.status}`);
      }

      const data = await response.json();
      
      
      // API response'unu AIResponse formatına dönüştür
      const aiResponse: AIResponse = {
        type: data.type || 'general',
        message: data.message || '',
        products: data.data?.products || data.products || [], // data.products öncelikli
        intent: data.type || data.intent || 'general', // type field'ını intent olarak da kullan
        confidence: data.confidence || 0.8,
        suggestions: data.suggestions || [],
        data: data.data || {},
        session_id: currentSessionId // Mevcut session ID'yi kullan
      };
      


      setIsLoading(false);
      return aiResponse;

    } catch (err) {
      setError(err instanceof Error ? err.message : 'API bağlantı hatası');
      setIsLoading(false);
      
      // Fallback to local AI logic
      return generateLocalResponse(userMessage);
    }
  }, [generateLocalResponse, getOrCreateSession]);

  // Event handling için yeni fonksiyonlar
  const sendFeedback = useCallback(async (feedbackData: any) => {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/feedback', {
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
      const response = await fetch('http://127.0.0.1:8000/api/product-click', {
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
      const response = await fetch('http://127.0.0.1:8000/api/cargo-tracking', {
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
    sendFeedback,
    sendProductClick,
    sendCargoTracking,
    isLoading,
    error,
    clearError: () => setError(null)
  };
};
