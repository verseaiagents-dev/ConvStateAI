import React from 'react';

interface ConnectionStatusProps {
  aiLoading: boolean;
  aiError?: string | null;
}

const ConnectionStatus: React.FC<ConnectionStatusProps> = ({ 
  aiLoading, 
  aiError 
}) => {
  return (
    <div className="connection-status">
      {/* AI Service Status */}
      <div className="status-item">
        <span className={`status-dot ${aiLoading ? 'loading' : aiError ? 'error' : 'ready'}`}></span>
        <span className="status-text">
          {aiLoading ? 'AI İşleniyor...' : aiError ? 'AI Hatası' : 'AI Hazır'}
        </span>
      </div>

      {/* Error Display */}
      {aiError && (
        <div className="error-message">
          <span className="error-icon">⚠️</span>
          <span className="status-text">{aiError}</span>
        </div>
      )}
    </div>
  );
};

export default ConnectionStatus;
