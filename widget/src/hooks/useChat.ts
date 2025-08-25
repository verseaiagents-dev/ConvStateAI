import { useState, useCallback, useRef, useEffect } from 'react';
import { Message, Product, FeedbackData } from '../types';
import { useAIService } from './useAIService';

export interface ChatState {
  messages: Message[];
  isTyping: boolean;
  scrollToBottom: boolean;
}

export const useChat = (aiApiKey?: string) => {
  const [messages, setMessages] = useState<Message[]>([]);
  const [isTyping, setIsTyping] = useState(false);
  const [scrollToBottom, setScrollToBottom] = useState(false);
  const [sessionId, setSessionId] = useState<string>('');
  const [sessionExpiry, setSessionExpiry] = useState<Date | null>(null);
  const chatContentRef = useRef<HTMLDivElement>(null);

  // AI Service
  const { generateResponse, sendFeedback, isLoading: aiLoading } = useAIService({ apiKey: aiApiKey });

  // Session Management
  useEffect(() => {
    // LocalStorage'dan session bilgilerini al
    const storedSessionId = localStorage.getItem('chat_session_id');
    const storedSessionExpiry = localStorage.getItem('chat_session_expiry');
    
    if (storedSessionId && storedSessionExpiry) {
      const expiryDate = new Date(storedSessionExpiry);
      
      // Session süresi dolmuş mu kontrol et
      if (expiryDate > new Date()) {
        setSessionId(storedSessionId);
        setSessionExpiry(expiryDate);
      } else {
        // Session süresi dolmuş, yeni session oluştur
        createNewSession();
      }
    } else {
      // Session yok, yeni oluştur
      createNewSession();
    }
  }, []);

  // Session expiration check
  useEffect(() => {
    if (sessionExpiry) {
      const checkExpiry = setInterval(() => {
        if (new Date() >= sessionExpiry) {
          createNewSession();
        }
      }, 60000); // Her dakika kontrol et

      return () => clearInterval(checkExpiry);
    }
  }, [sessionExpiry]);

  /**
   * Yeni session oluştur
   */
  const createNewSession = useCallback(() => {
    const newSessionId = `session_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    const newExpiry = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000); // 7 gün
    
    setSessionId(newSessionId);
    setSessionExpiry(newExpiry);
    
    // LocalStorage'a kaydet
    localStorage.setItem('chat_session_id', newSessionId);
    localStorage.setItem('chat_session_expiry', newExpiry.toISOString());
    

  }, []);

  /**
   * Session'ı yenile
   */
  const refreshSession = useCallback(() => {
    if (sessionId) {
      const newExpiry = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000); // 7 gün
      setSessionExpiry(newExpiry);
      localStorage.setItem('chat_session_expiry', newExpiry.toISOString());

    }
  }, [sessionId]);

  /**
   * Session'ı temizle
   */
  const clearSession = useCallback(() => {
    setSessionId('');
    setSessionExpiry(null);
    localStorage.removeItem('chat_session_id');
    localStorage.removeItem('chat_session_expiry');

  }, []);

  // Add message function
  const addMessage = useCallback((text: string, role: 'user' | 'agent', products?: Product[], additionalData?: any) => {
    const newMessage: Message = {
      id: Date.now().toString(),
      content: text,
      role,
      timestamp: new Date(),
      products: products || [],
      ...additionalData // type, data, suggestions, intent gibi ek alanları ekle
    };

    setMessages(prev => [...prev, newMessage]);
    setScrollToBottom(true);
  }, []);

  // Enhanced AI response with suggestions and shimmer loading
  const generateAIResponse = useCallback(async (userMessage: string) => {
    setIsTyping(true);
    
    // Session'ı refresh et (her AI interaction'da)
    refreshSession();
    
    // Add shimmer loading message first
    const shimmerId = Date.now().toString();
    const shimmerType = getShimmerType(userMessage);
    addMessage('', 'agent', [], { 
      id: shimmerId,
      type: 'shimmer',
      shimmerType,
      isShimmer: true 
    });
    
    try {
      // Random delay between 750ms and 1500ms
      const delay = Math.random() * (1500 - 750) + 750;
      await new Promise(resolve => setTimeout(resolve, delay));
      
      // Session ID ile AI response al
      const aiResponse = await generateResponse(userMessage, sessionId);
      
      // Remove shimmer message
      setMessages(prev => prev.filter(msg => msg.id !== shimmerId));
      
      // Add AI response with all data from Laravel
      const additionalData = {
        type: aiResponse.type,
        intent: aiResponse.intent,
        suggestions: aiResponse.suggestions,
        data: aiResponse.data,
        session_id: sessionId // Mevcut session ID'yi kullan
      };

      addMessage(aiResponse.message || aiResponse.type, 'agent', aiResponse.products, additionalData);
      
      // Add suggestions if available
      if (aiResponse.suggestions && aiResponse.suggestions.length > 0) {
        // You can add suggestion buttons here
      }
      
    } catch (error) {
      // AI response error
      
      // Remove shimmer message
      setMessages(prev => prev.filter(msg => msg.id !== shimmerId));
      
      // Fallback response
      addMessage('Üzgünüm, şu anda yanıt üretemiyorum. Lütfen daha sonra tekrar deneyin.', 'agent');
    } finally {
      setIsTyping(false);
    }
  }, [generateResponse, addMessage, sessionId, refreshSession]);



  // Helper function to determine shimmer type based on user message
  const getShimmerType = useCallback((userMessage: string): string => {
    const message = userMessage.toLowerCase();
    
    if (message.includes('ürün') || message.includes('product') || message.includes('satın al')) {
      return 'product';
    } else if (message.includes('kargo') || message.includes('cargo') || message.includes('kargom') || message.includes('takip') || message.includes('tracking')) {
      return 'cargo';
    } else if (message.includes('sipariş') || message.includes('order') || message.includes('siparişim')) {
      return 'order';
    } else if (message.includes('feedback') || message.includes('geri bildirim')) {
      return 'feedback';
    } else {
      return 'general';
    }
  }, []);

  // Send message function
  const sendMessage = useCallback(async (text: string) => {
    if (!text.trim()) return;
    
    // Add user message
    addMessage(text, 'user');
    
    
    
    // Generate AI response
    await generateAIResponse(text);
  }, [addMessage, generateAIResponse]);

  // Handle product click
  const handleProductClick = useCallback((product: Product) => {
    // Add product selection message
    addMessage(`"${product.name}" ürününü seçtiniz. Bu ürün hakkında daha fazla bilgi almak ister misiniz?`, 'agent');
  }, [addMessage]);



  // Handle feedback
  const handleFeedback = useCallback((feedback: FeedbackData) => {
    // Feedback'i API'ye gönder (session'a post at)
    sendFeedback(feedback);
    
    // Ama kullanıcıya ekstra mesaj gösterme
    // addMessage(response, 'agent'); // Bu satır kaldırıldı
  }, [sendFeedback]);

  // Typing indicator - HTTP API ile çalışıyor
  const sendTypingIndicator = useCallback((isTyping: boolean) => {
    // HTTP API ile typing indicator gönderilebilir (gerekirse)
  }, []);

  // Scroll to bottom effect
  useEffect(() => {
    if (scrollToBottom && chatContentRef.current) {
      chatContentRef.current.scrollTop = chatContentRef.current.scrollHeight;
      setScrollToBottom(false);
    }
  }, [scrollToBottom, messages]);

  // Auto-scroll when new messages arrive
  useEffect(() => {
    if (chatContentRef.current) {
      chatContentRef.current.scrollTop = chatContentRef.current.scrollHeight;
    }
  }, [messages]);

  // Send typing indicator when AI is processing
  useEffect(() => {
    sendTypingIndicator(isTyping);
  }, [isTyping, sendTypingIndicator]);

  return {
    messages,
    isTyping,
    aiLoading,
    chatContentRef,
    sendMessage,
    handleProductClick,
    handleFeedback,
    addMessage,
    generateAIResponse,
    // Session Management
    sessionId,
    sessionExpiry,
    createNewSession,
    refreshSession,
    clearSession
  };
};
