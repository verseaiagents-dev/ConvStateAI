import React, { useState, useEffect, useRef } from 'react';
import { Message, Product, FeedbackData } from '../../../types';

interface ProductRecommendationMessageProps {
  message: Message;
  onProductClick: (product: Product) => void;
  onFeedback: (feedback: FeedbackData) => void;
}

const ProductRecommendationMessage: React.FC<ProductRecommendationMessageProps> = ({ 
  message, 
  onProductClick, 
  onFeedback 
}) => {

  // Debug: Products field'ını kontrol et
  console.log('ProductRecommendationMessage: message object:', message);
  console.log('ProductRecommendationMessage: message.products:', message.products);
  console.log('ProductRecommendationMessage: message.data?.products:', message.data?.products);
  
  // Products'ı doğru şekilde extract et
  const products = message.products || message.data?.products || [];
  console.log('ProductRecommendationMessage: Final products array:', products);
  console.log('ProductRecommendationMessage: Products length:', products.length);
  
  // Feedback state'i - localStorage'da saklanır
  const [feedbackGiven, setFeedbackGiven] = useState<boolean>(false);
  
  // Scroll container için ref
  const scrollContainerRef = useRef<HTMLDivElement>(null);
  
  // Component mount olduğunda localStorage'dan feedback durumunu kontrol et
  useEffect(() => {
    const feedbackKey = `feedback_${message.id}`;
    const hasFeedback = localStorage.getItem(feedbackKey);
    if (hasFeedback) {
      setFeedbackGiven(true);
    }
  }, [message.id]);

  // Scroll functionality için useEffect
  useEffect(() => {
    const scrollContainer = scrollContainerRef.current;
    if (!scrollContainer) return;

    let isDown = false;
    let startX: number;
    let scrollLeft: number;

    // Mouse events for drag scrolling
    const handleMouseDown = (e: MouseEvent) => {
      isDown = true;
      scrollContainer.style.cursor = 'grabbing';
      startX = e.pageX - scrollContainer.offsetLeft;
      scrollLeft = scrollContainer.scrollLeft;
    };

    const handleMouseLeave = () => {
      isDown = false;
      scrollContainer.style.cursor = 'grab';
    };

    const handleMouseUp = () => {
      isDown = false;
      scrollContainer.style.cursor = 'grab';
    };

    const handleMouseMove = (e: MouseEvent) => {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - scrollContainer.offsetLeft;
      const walk = (x - startX) * 2;
      scrollContainer.scrollLeft = scrollLeft - walk;
    };

    // Touch events for mobile
    const handleTouchStart = (e: TouchEvent) => {
      startX = e.touches[0].pageX - scrollContainer.offsetLeft;
      scrollLeft = scrollContainer.scrollLeft;
    };

    const handleTouchMove = (e: TouchEvent) => {
      if (!startX) return;
      e.preventDefault();
      const x = e.touches[0].pageX - scrollContainer.offsetLeft;
      const walk = (x - startX) * 2;
      scrollContainer.scrollLeft = scrollLeft - walk;
    };

    const handleTouchEnd = () => {
      startX = 0;
    };

    // Event listeners ekle
    scrollContainer.addEventListener('mousedown', handleMouseDown);
    scrollContainer.addEventListener('mouseleave', handleMouseLeave);
    scrollContainer.addEventListener('mouseup', handleMouseUp);
    scrollContainer.addEventListener('mousemove', handleMouseMove);
    scrollContainer.addEventListener('touchstart', handleTouchStart);
    scrollContainer.addEventListener('touchmove', handleTouchMove);
    scrollContainer.addEventListener('touchend', handleTouchEnd);

    // Cleanup function
    return () => {
      scrollContainer.removeEventListener('mousedown', handleMouseDown);
      scrollContainer.removeEventListener('mouseleave', handleMouseLeave);
      scrollContainer.removeEventListener('mouseup', handleMouseUp);
      scrollContainer.removeEventListener('mousemove', handleMouseMove);
      scrollContainer.removeEventListener('touchstart', handleTouchStart);
      scrollContainer.removeEventListener('touchmove', handleTouchMove);
      scrollContainer.removeEventListener('touchend', handleTouchEnd);
    };
  }, []);
  
  /**
   * Product action handler - view, compare actions
   */
  const handleProductAction = async (product: Product, action: 'view' | 'compare') => {
    try {
      const sessionId = localStorage.getItem('chat_session_id') || 'unknown';
      
      // Product interaction'ı API'ye gönder
      const response = await fetch('/api/product-interaction', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          session_id: sessionId,
          product_id: product.id,
          action: action,
          timestamp: new Date().toISOString(),
          source: 'chat_widget',
          metadata: {
            product_name: product.name,
            product_category: product.category,
            product_brand: product.brand,
            action_source: 'chat_widget'
          }
        })
      });

      if (response.ok) {
        const result = await response.json();

        
        // Action'a göre işlem yap
        if (action === 'view') {
          // Ürün detayına yönlendir
          if (product.url) {
            // Chat session bilgisini URL'e ekle
            const redirectUrl = new URL(product.url);
            redirectUrl.searchParams.set('ref', 'chat');
            redirectUrl.searchParams.set('session', sessionId);
            redirectUrl.searchParams.set('action', 'view');
            redirectUrl.searchParams.set('product_id', product.id.toString());
            
            window.open(redirectUrl.toString(), '_blank');
          } else {
            // URL yoksa onProductClick'i çağır
            onProductClick(product);
          }
        } else if (action === 'compare') {
          // Karşılaştırma modal'ı veya sayfası aç
  
          // TODO: Implement comparison functionality
        }
        
      } else {
        // Product interaction logging failed
      }
      
    } catch (error) {
      // Product action error
    }
  };

  const handleFeedback = (isHelpful: boolean) => {
    // Feedback'i localStorage'a kaydet
    const feedbackKey = `feedback_${message.id}`;
    localStorage.setItem(feedbackKey, JSON.stringify({
      isHelpful,
      timestamp: new Date().toISOString()
    }));
    
    // State'i güncelle
    setFeedbackGiven(true);
    
    // Parent component'e feedback'i gönder
    onFeedback({
      messageId: message.id,
      isHelpful,
      comment: ''
    });
  };

  return (
    <div className="message agent-message">
      {/* Message Content Wrapper */}
      <div className="message-content-wrapper">
        {/* Ürün önerisi başlığı */}
        <h4 style={{ fontSize: '14px', fontWeight: '600', marginBottom: '8px' }}>
          {message.data?.title || 'Senin için önerdiğim ürünler:'}
        </h4>
        
        {/* Ürün kartları */}
        {products && products.length > 0 ? (
          <div className="products-scroll-container" ref={scrollContainerRef}>
            <div className="products-wrapper">
              {products.map((product: Product) => (
                <div key={product.id} className="product-card">
                  <div className="product-image">
                    {product.image ? (
                      <img src={product.image} alt={product.name} />
                    ) : (
                      <div style={{
                        width: '100%',
                        height: '100%',
                        backgroundColor: '#F3F4F6',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontSize: '12px',
                        color: '#6B7280'
                      }}>
                        Resim Yok
                      </div>
                    )}
                  </div>
                  <div className="product-info">
                    <h4>{product.name}</h4>
                    <p style={{ fontSize: '12px', color: '#6B7280', marginBottom: '4px' }}>
                      {product.brand}
                    </p>
                    <p className="product-price">${product.price}</p>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '4px', marginBottom: '8px' }}>
                      <span style={{ fontSize: '10px', color: '#6B7280' }}>Rating:</span>
                      <span style={{ fontSize: '10px', color: '#F59E0B' }}>★ {product.rating}</span>
                    </div>
                    
                    {/* Enhanced Product Buttons */}
                    <div className="product-buttons-container" style={{ display: 'flex', gap: '6px' }}>
                      <button 
                        className="product-button primary"
                        onClick={(e) => {
                          e.stopPropagation();
                          handleProductAction(product, 'view');
                        }}
                        style={{
                          flex: 1,
                          padding: '6px 8px',
                          fontSize: '11px',
                          backgroundColor: '#3B82F6',
                          color: 'white',
                          border: 'none',
                          borderRadius: '4px',
                          cursor: 'pointer'
                        }}
                      >
                        Detayları gör
                      </button>
                      <button 
                        className="product-button secondary"
                        onClick={(e) => {
                          e.stopPropagation();
                          handleProductAction(product, 'compare');
                        }}
                        style={{
                          flex: 1,
                          padding: '6px 8px',
                          fontSize: '11px',
                          backgroundColor: '#F59E0B',
                          color: 'white',
                          border: 'none',
                          borderRadius: '4px',
                          cursor: 'pointer'
                        }}
                      >
                        Karşılaştır
                      </button>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        ) : (
          <p>Ürün bulunamadı.</p>
        )}
        
        {/* AI notu */}
        <div className="ai-note">
          <span className="ai-icon">AI</span>
          {message.data?.ai_note || 'Tercihlerinize göre önerilen ürünler.'}
        </div>
      </div>
      
      {/* Feedback Buttons - Sadece feedback verilmemişse göster */}
      {!feedbackGiven && (
        <div className="feedback-container">
          <button 
            className="feedback-button"
            onClick={() => handleFeedback(true)}
          >
            👍 Yararlı
          </button>
          <button 
            className="feedback-button negative"
            onClick={() => handleFeedback(false)}
          >
            👎 Yararsız
          </button>
        </div>
      )}
      
      {/* Feedback verildiyse teşekkür mesajı göster */}
      {feedbackGiven && (
        <div className="feedback-thanks">
          <span style={{ fontSize: '12px', color: '#10B981', fontStyle: 'italic' }}>
            ✓ Feedback'iniz için teşekkürler!
          </span>
        </div>
      )}
    </div>
  );
};

export default ProductRecommendationMessage;
