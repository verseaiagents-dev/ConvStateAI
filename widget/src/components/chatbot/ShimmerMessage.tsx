import React from 'react';

interface ShimmerMessageProps {
  type?: 'general' | 'product' | 'cargo' | 'order' | 'feedback';
}

const ShimmerMessage: React.FC<ShimmerMessageProps> = ({ type = 'general' }) => {
  const renderShimmerContent = () => {
    switch (type) {
      case 'product':
        return (
          <>
            {/* Başlık shimmer */}
            <div className="shimmer shimmer-title" style={{ width: '60%', height: '16px', marginBottom: '12px' }}></div>
            
            {/* Ürün kartları shimmer */}
            <div className="products-scroll-container">
              <div className="products-wrapper">
                {[1, 2, 3].map((i) => (
                  <div key={i} className="product-card shimmer-card">
                    <div className="shimmer shimmer-image" style={{ width: '100%', height: '80px' }}></div>
                    <div className="product-info">
                      <div className="shimmer shimmer-text" style={{ width: '80%', height: '14px', marginBottom: '8px' }}></div>
                      <div className="shimmer shimmer-text" style={{ width: '60%', height: '12px', marginBottom: '4px' }}></div>
                      <div className="shimmer shimmer-price" style={{ width: '40%', height: '16px', marginBottom: '8px' }}></div>
                      <div className="shimmer shimmer-button" style={{ width: '100%', height: '24px' }}></div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </>
        );
        
      case 'cargo':
        return (
          <>
            {/* Mesaj shimmer */}
            <div className="shimmer shimmer-text" style={{ width: '90%', height: '16px', marginBottom: '16px' }}></div>
            
            {/* Input shimmer */}
            <div className="shimmer shimmer-input" style={{ width: '100%', height: '36px', marginBottom: '8px' }}></div>
            
            {/* Button shimmer */}
            <div className="shimmer shimmer-button" style={{ width: '100%', height: '36px' }}></div>
          </>
        );
        
      case 'order':
        return (
          <>
            {/* Mesaj shimmer */}
            <div className="shimmer shimmer-text" style={{ width: '85%', height: '16px', marginBottom: '16px' }}></div>
            
            {/* Input shimmer */}
            <div className="shimmer shimmer-input" style={{ width: '100%', height: '36px', marginBottom: '8px' }}></div>
            
            {/* Button shimmer */}
            <div className="shimmer shimmer-button" style={{ width: '100%', height: '36px' }}></div>
          </>
        );
        
      case 'feedback':
        return (
          <>
            {/* Mesaj shimmer */}
            <div className="shimmer shimmer-text" style={{ width: '80%', height: '16px', marginBottom: '16px' }}></div>
            
            {/* Feedback buttons shimmer */}
            <div style={{ display: 'flex', gap: '8px' }}>
              <div className="shimmer shimmer-button" style={{ width: '80px', height: '32px' }}></div>
              <div className="shimmer shimmer-button" style={{ width: '80px', height: '32px' }}></div>
            </div>
          </>
        );
        
      default: // general
        return (
          <>
            {/* Mesaj shimmer */}
            <div className="shimmer shimmer-text" style={{ width: '90%', height: '16px', marginBottom: '12px' }}></div>
            
            {/* Öneriler shimmer */}
            {Math.random() > 0.5 && (
              <div className="action-buttons">
                <div className="action-buttons-scroll-container">
                  <div className="action-buttons-wrapper">
                    {[1, 2, 3].map((i) => (
                      <div key={i} className="shimmer shimmer-suggestion" style={{ width: '80px', height: '28px' }}></div>
                    ))}
                  </div>
                </div>
              </div>
            )}
          </>
        );
    }
  };

  return (
    <div className="message agent-message shimmer-message">
      <div className="message-content-wrapper">
        {renderShimmerContent()}
      </div>
      
      {/* Feedback shimmer */}
      <div className="feedback-container">
        <div className="shimmer shimmer-feedback" style={{ width: '70px', height: '28px' }}></div>
        <div className="shimmer shimmer-feedback" style={{ width: '70px', height: '28px' }}></div>
      </div>
    </div>
  );
};

export default ShimmerMessage;
