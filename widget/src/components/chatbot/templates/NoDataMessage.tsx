import React from 'react';
import { Message, FeedbackData } from '../../../types';

interface NoDataMessageProps {
  message: Message;
  onFeedback: (feedback: FeedbackData) => void;
}

const NoDataMessage: React.FC<NoDataMessageProps> = ({ 
  message, 
  onFeedback 
}) => {
  
  // Feedback state'i
  const [feedbackGiven, setFeedbackGiven] = React.useState<boolean>(false);
  
  // Feedback gönder
  const handleFeedback = (isHelpful: boolean) => {
    if (feedbackGiven) return;
    
    const feedbackData: FeedbackData = {
      messageId: message.id,
      isHelpful,
      comment: `Search: ${message.data?.search_query || ''}, Intent: ${message.intent || 'product_search'}, Results: ${message.data?.total_found || 0}`
    };
    
    onFeedback(feedbackData);
    setFeedbackGiven(true);
    
    // localStorage'a kaydet
    localStorage.setItem(`feedback_${message.id}`, JSON.stringify(feedbackData));
  };

  // Suggestions'ları al
  const suggestions = message.suggestions || [
    "Farklı kelimelerle ara",
    "Kategori seç",
    "Fiyat aralığı belirle",
    "Marka seç"
  ];

  return (
    <div className="message bot-message">
      <div className="message-content">
        {/* Ana Mesaj */}
        <div className="no-data-message">
          <div className="no-data-icon">
            <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.47-.881-6.08-2.33"></path>
            </svg>
          </div>
          
          <div className="no-data-content">
            <h4 className="no-data-title">
              {message.message || "Şu anda bilgi tabanında bilgi olmadığı için görüntüleyemiyoruz"}
            </h4>
            
            <p className="no-data-description">
              Lütfen bilgi tabanınızı güncelleyiniz
            </p>
            
            {/* Arama Detayları */}
            {message.data?.search_query && (
              <div className="search-details">
                <span className="search-label">Arama:</span>
                <span className="search-query">"{message.data.search_query}"</span>
                <span className="search-results">({message.data.total_found || 0} sonuç)</span>
              </div>
            )}
          </div>
        </div>

        {/* Öneriler */}
        {suggestions.length > 0 && (
          <div className="suggestions-section">
            <h5 className="suggestions-title">Öneriler:</h5>
            <div className="suggestions-list">
              {suggestions.map((suggestion, index) => (
                <div key={index} className="suggestion-item">
                  <span className="suggestion-bullet">•</span>
                  <span className="suggestion-text">{suggestion}</span>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Feedback Butonları */}
        <div className="feedback-section">
          <p className="feedback-question">Bu bilgi faydalı mıydı?</p>
          <div className="feedback-buttons">
            <button
              onClick={() => handleFeedback(true)}
              disabled={feedbackGiven}
              className={`feedback-btn helpful ${feedbackGiven ? 'disabled' : 'hover:bg-green-600'}`}
            >
              👍 Faydalı
            </button>
            <button
              onClick={() => handleFeedback(false)}
              disabled={feedbackGiven}
              className={`feedback-btn not-helpful ${feedbackGiven ? 'disabled' : 'hover:bg-red-600'}`}
            >
              👎 Faydalı Değil
            </button>
          </div>
          {feedbackGiven && (
            <p className="feedback-thanks">Teşekkürler! Geri bildiriminiz kaydedildi.</p>
          )}
        </div>
      </div>
    </div>
  );
};

export default NoDataMessage;
