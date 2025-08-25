import React from 'react';
import { Message, FeedbackData } from '../../../types';

interface FeedbackMessageProps {
  message: Message;
  onFeedback: (feedback: FeedbackData) => void;
}

const FeedbackMessage: React.FC<FeedbackMessageProps> = ({ message, onFeedback }) => {
  
  const handleFeedback = (isHelpful: boolean) => {
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
        {/* Feedback mesajı */}
        <p>{message.content || message.message || 'Feedback mesajı bulunamadı.'}</p>
      </div>
      
      {/* Feedback butonları */}
      <div className="feedback-container">
        <button 
          className="feedback-button"
          onClick={() => handleFeedback(true)}
        >
          👍 Evet, yardımcı oldu
        </button>
        <button 
          className="feedback-button negative"
          onClick={() => handleFeedback(false)}
        >
          👎 Hayır, yardımcı olmadı
        </button>
      </div>
    </div>
  );
};

export default FeedbackMessage;
