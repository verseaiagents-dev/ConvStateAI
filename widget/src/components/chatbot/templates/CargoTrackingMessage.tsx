import React, { useState } from 'react';
import { Message, FeedbackData } from '../../../types';

interface CargoTrackingMessageProps {
  message: Message;
  onFeedback: (feedback: FeedbackData) => void;
}

const CargoTrackingMessage: React.FC<CargoTrackingMessageProps> = ({ 
  message, 
  onFeedback 
}) => {
  const [cargoNumber, setCargoNumber] = useState('');
  const [isTracking, setIsTracking] = useState(false);
  const [trackingResult, setTrackingResult] = useState<any>(null);
  const [feedbackGiven, setFeedbackGiven] = useState<boolean>(false);

  // Component mount olduğunda localStorage'dan feedback durumunu kontrol et
  React.useEffect(() => {
    const feedbackKey = `feedback_${message.id}`;
    const hasFeedback = localStorage.getItem(feedbackKey);
    if (hasFeedback) {
      setFeedbackGiven(true);
    }
  }, [message.id]);

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

  const handleCargoTracking = async () => {
    if (!cargoNumber.trim()) return;
    
    setIsTracking(true);
    try {
      // Widget ayarlarından API endpoint'i al
      const widgetConfig = (window as any).widgetConfig;
      const cargoTrackingEndpoint = widgetConfig?.apiEndpoints?.cargoTracking;
      
      if (!cargoTrackingEndpoint) {
        // API endpoint tanımlanmamışsa "yakında açılacak" mesajı göster
        setTrackingResult({
          error: 'Bu özellik yakında açılacak',
          feature_disabled: true
        });
        setIsTracking(false);
        return;
      }
      
      // API endpoint varsa normal sorgu yap
      const response = await fetch(`${cargoTrackingEndpoint}?cargo_number=${encodeURIComponent(cargoNumber)}`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        }
      });

      if (response.ok) {
        const result = await response.json();
        setTrackingResult(result.data);
      } else {
        setTrackingResult({ error: 'Kargo takip bilgisi alınamadı' });
      }
    } catch (error) {
      setTrackingResult({ error: 'Bağlantı hatası' });
    } finally {
      setIsTracking(false);
    }
  };

  return (
    <div className="message agent-message">
      {/* Message Content Wrapper */}
      <div className="message-content-wrapper">
        {/* Kargo takip mesajı */}
        <p style={{ marginBottom: '16px' }}>
          {message.message || 'Kargo takip numaranızı girerek kargo durumunuzu öğrenebilirsiniz.'}
        </p>
        
        {/* Kargo takip input alanı */}
        <div className="cargo-tracking-input" style={{ marginBottom: '16px' }}>
          <input
            id="cargo-number"
            type="text"
            value={cargoNumber}
            onChange={(e) => setCargoNumber(e.target.value)}
            placeholder="Kargo takip numarası giriniz..."
            style={{
              width: '100%',
              padding: '8px 12px',
              border: '1px solid #D1D5DB',
              borderRadius: '6px',
              fontSize: '14px',
              outline: 'none',
              marginBottom: '8px'
            }}
            onKeyPress={(e) => e.key === 'Enter' && handleCargoTracking()}
          />
          <button
            onClick={handleCargoTracking}
            disabled={isTracking || !cargoNumber.trim()}
            style={{
              width: '100%',
              padding: '8px 10px',
              backgroundColor: '#3B82F6',
              color: 'white',
              border: 'none',
              borderRadius: '6px',
              cursor: 'pointer',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              gap: '6px',
              fontSize: '14px',
              fontWeight: '500',
              opacity: isTracking || !cargoNumber.trim() ? 0.6 : 1
            }}
          >
            {isTracking ? 'Takip Ediliyor...' : 'Takip Et'}
            <span style={{ fontSize: '14px' }}>→</span>
          </button>
        </div>

        {/* Kargo takip sonucu */}
        {trackingResult && (
          <div className="cargo-tracking-result" style={{ 
            backgroundColor: trackingResult.feature_disabled ? '#FEF3C7' : '#F9FAFB', 
            padding: '16px', 
            borderRadius: '8px',
            border: `1px solid ${trackingResult.feature_disabled ? '#F59E0B' : '#E5E7EB'}`,
            marginBottom: '16px'
          }}>
            {trackingResult.error ? (
              <div style={{ textAlign: 'center' }}>
                {trackingResult.feature_disabled ? (
                  <>
                    <div style={{ fontSize: '48px', marginBottom: '8px' }}>🚧</div>
                    <p style={{ color: '#92400E', margin: '0 0 8px 0', fontWeight: '600' }}>
                      Bu özellik yakında açılacak
                    </p>
                    <p style={{ color: '#92400E', margin: 0, fontSize: '14px' }}>
                      Kargo takip özelliği geliştirme aşamasında
                    </p>
                  </>
                ) : (
                  <p style={{ color: '#DC2626', margin: 0 }}>{trackingResult.error}</p>
                )}
              </div>
            ) : (
              <div>
                <h4 style={{ 
                  fontSize: '16px', 
                  fontWeight: '600', 
                  margin: '0 0 12px 0',
                  color: '#111827'
                }}>
                  📦 Kargo Takip Bilgisi
                </h4>
                
                <div style={{ display: 'grid', gap: '8px' }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <span style={{ fontSize: '14px', color: '#6B7280' }}>Takip No:</span>
                    <span style={{ fontSize: '14px', fontWeight: '500' }}>{trackingResult.cargo_number}</span>
                  </div>
                  
                  <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <span style={{ fontSize: '14px', color: '#6B7280' }}>Durum:</span>
                    <span style={{ 
                      fontSize: '14px', 
                      fontWeight: '500',
                      color: '#059669'
                    }}>
                      {trackingResult.status}
                    </span>
                  </div>
                  
                  <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <span style={{ fontSize: '14px', color: '#6B7280' }}>Konum:</span>
                    <span style={{ fontSize: '14px', fontWeight: '500' }}>{trackingResult.current_location}</span>
                  </div>
                  
                  <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <span style={{ fontSize: '14px', color: '#6B7280' }}>Tahmini Teslimat:</span>
                    <span style={{ fontSize: '14px', fontWeight: '500' }}>{trackingResult.estimated_delivery}</span>
                  </div>
                  
                  <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <span style={{ fontSize: '14px', color: '#6B7280' }}>Son Güncelleme:</span>
                    <span style={{ fontSize: '14px', fontWeight: '500' }}>{trackingResult.last_update}</span>
                  </div>
                </div>
                
                {trackingResult.tracking_url && (
                  <div style={{ marginTop: '12px', textAlign: 'center' }}>
                    <a 
                      href={trackingResult.tracking_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      style={{
                        color: '#3B82F6',
                        textDecoration: 'none',
                        fontSize: '14px',
                        fontWeight: '500'
                      }}
                    >
                      📍 Detaylı Takip Sayfası
                    </a>
                  </div>
                )}
              </div>
            )}
          </div>
        )}
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

export default CargoTrackingMessage;
