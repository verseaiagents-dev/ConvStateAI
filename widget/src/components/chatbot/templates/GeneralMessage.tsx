import React from 'react';
import { Message, FeedbackData } from '../../../types';

interface GeneralMessageProps {
  message: Message;
  onFeedback: (feedback: FeedbackData) => void;
}

const GeneralMessage: React.FC<GeneralMessageProps> = ({ message, onFeedback }) => {

  
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
        {/* Mesaj içeriği */}
        <p>{message.content || message.message || 'Mesaj içeriği bulunamadı.'}</p>
        
        {/* Öneriler */}
        {message.suggestions && message.suggestions.length > 0 && (
          <div className="action-buttons">
            <div className="action-buttons-scroll-container">
              <div className="action-buttons-wrapper">
                {message.suggestions.map((suggestion, index) => (
                  <button key={index} className="secondary-button">
                    {suggestion}
                  </button>
                ))}
              </div>
            </div>
          </div>
        )}
      </div>
      
      {/* Feedback Buttons */}
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
    </div>
  );
};

export default GeneralMessage;
