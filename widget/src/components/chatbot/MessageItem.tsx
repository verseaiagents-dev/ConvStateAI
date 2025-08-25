import React from 'react';
import { Message, Product, FeedbackData } from '../../types';
import TTSButton from './TTSButton';
import ProductRecommendationMessage from './templates/ProductRecommendationMessage';
import FeedbackMessage from './templates/FeedbackMessage';
import GeneralMessage from './templates/GeneralMessage';
import OrderMessage from './templates/OrderMessage';
import CargoTrackingMessage from './templates/CargoTrackingMessage';
import ShimmerMessage from './ShimmerMessage';

interface MessageItemProps {
  message: Message;
  onProductClick: (product: Product) => void;
  onFeedback: (feedback: FeedbackData) => void;
}

const MessageItem: React.FC<MessageItemProps> = ({ message, onProductClick, onFeedback }) => {
  const isUser = message.role === 'user';

  // Intent bazlı component mapping sistemi
  const renderIntentBasedComponent = () => {
    if (isUser) return null; // User mesajları için component render etme

    const messageType = message.type || message.intent || 'general';
  
    
    switch (messageType) {
      case 'product_recommendation':
      case 'product_inquiry':
      case 'product_search': // Eklendi
      case 'order_inquiry': // Now handles both order inquiry and recommendations
      case 'smart_recommendation':
      case 'trend_products':
      case 'contextual_recommendation':
      case 'category_recommendation':
        return (
          <ProductRecommendationMessage 
            message={message}
            onProductClick={onProductClick}
            onFeedback={onFeedback}
          />
        );
        
      case 'feedback':
        return (
          <FeedbackMessage 
            message={message}
            onFeedback={onFeedback}
          />
        );
        
              case 'order_inquiry':
        case 'order_tracking':
          return (
            <OrderMessage 
              message={message}
              onFeedback={onFeedback}
            />
          );
        
      case 'cargo_tracking':
        return (
          <CargoTrackingMessage 
            message={message}
            onFeedback={onFeedback}
          />
        );
        
      case 'campaign_inquiry':
        return (
          <GeneralMessage 
            message={message}
            onFeedback={onFeedback}
          />
        );
        
      case 'shimmer':
        return (
          <ShimmerMessage 
            type={message.shimmerType}
          />
        );
        
      case 'greeting':
      case 'help_request':
      case 'price_inquiry':
      case 'general':
      default:
        return (
          <GeneralMessage 
            message={message}
            onFeedback={onFeedback}
          />
        );
    }
  };

  // User mesajları için basit render
  if (isUser) {
    return (
      <div className="message-group user">
        <div className="message user-message">
          {/* Message Content Wrapper */}
          <div className="message-content-wrapper">
            {message.content}
          </div>
        </div>
      </div>
    );
  }

  // Bot mesajları için intent bazlı component render
  return (
    <div className="message-group">
      <div className="agent-avatar">
        <img src="/imgs/ai-conversion-logo-small.svg" alt="AI Conversion Agent" />
      </div>
      {renderIntentBasedComponent()}
    </div>
  );
};

export default MessageItem;
