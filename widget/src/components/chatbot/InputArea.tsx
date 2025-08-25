import React, { useState, KeyboardEvent } from 'react';
import { InputAreaProps } from '../../types';
import { useSpeechToText } from '../../hooks/useSpeechToText';

const InputArea: React.FC<InputAreaProps> = ({ onSendMessage, disabled = false, placeholder = "Message...", setMessage: externalSetMessage }) => {
  const [message, setMessage] = useState('');
  const { isListening, startListening, stopListening, error } = useSpeechToText();

  // Use external setMessage if provided
  const updateMessage = (text: string) => {
    setMessage(text);
    if (externalSetMessage) {
      externalSetMessage(text);
    }
  };

  const handleSend = () => {
    if (message.trim() && !disabled) {
      onSendMessage(message.trim());
      setMessage('');
    }
  };

  const handleKeyPress = (e: KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    }
  };

  const handleVoiceClick = () => {
    if (isListening) {
      // Stop listening and process the result
      stopListening();
    } else {
      // Start listening for speech input
      startListening((text) => {
        // When speech is recognized, append to existing message
        const currentText = message.trim();
        const newText = currentText ? `${currentText} ${text}` : text;
        updateMessage(newText);
      });
    }
  };

  return (
    <div className="input-area">
      <div className="input-wrapper">
        <input
          type="text"
          value={message}
          onChange={(e) => updateMessage(e.target.value)}
          onKeyPress={handleKeyPress}
          placeholder={placeholder}
          disabled={disabled}
          id="messageInput"
        />
        <button
          onClick={handleSend}
          disabled={disabled || !message.trim()}
          className="send-button"
          title="Mesaj Gönder"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <path d="M22 2L11 13" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
        </button>
      </div>
      
      {error && (
        <div className="voice-error" style={{ color: '#ef4444', fontSize: '0.75rem', marginBottom: '4px' }}>
          {error}
        </div>
      )}
      <button
        className={`voice-button ${isListening ? 'recording' : ''}`}
        onClick={handleVoiceClick}
        title={isListening ? "Ses Kaydını Durdur" : "Sesli Mesaj Kaydet"}
      >
        {isListening ? (
          // Animated voice waves when recording
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <rect x="2" y="12" width="2" height="2" fill="currentColor" className="voice-wave wave-1">
              <animate attributeName="height" values="2;8;2" dur="0.6s" repeatCount="indefinite"/>
              <animate attributeName="y" values="12;8;12" dur="0.6s" repeatCount="indefinite"/>
            </rect>
            <rect x="6" y="10" width="2" height="6" fill="currentColor" className="voice-wave wave-2">
              <animate attributeName="height" values="6;12;6" dur="0.8s" repeatCount="indefinite"/>
              <animate attributeName="y" values="10;6;10" dur="0.8s" repeatCount="indefinite"/>
            </rect>
            <rect x="10" y="8" width="2" height="8" fill="currentColor" className="voice-wave wave-3">
              <animate attributeName="height" values="8;16;8" dur="0.7s" repeatCount="indefinite"/>
              <animate attributeName="y" values="8;4;8" dur="0.7s" repeatCount="indefinite"/>
            </rect>
            <rect x="14" y="10" width="2" height="6" fill="currentColor" className="voice-wave wave-4">
              <animate attributeName="height" values="6;12;6" dur="0.9s" repeatCount="indefinite"/>
              <animate attributeName="y" values="10;6;10" dur="0.9s" repeatCount="indefinite"/>
            </rect>
            <rect x="18" y="12" width="2" height="2" fill="currentColor" className="voice-wave wave-5">
              <animate attributeName="height" values="2;8;2" dur="0.65s" repeatCount="indefinite"/>
              <animate attributeName="y" values="12;8;12" dur="0.65s" repeatCount="indefinite"/>
            </rect>
          </svg>
        ) : (
          // Static microphone icon when not recording
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 1C11.2044 1 10.4413 1.31607 9.87868 1.87868C9.31607 2.44129 9 3.20435 9 4V12C9 12.7956 9.31607 13.5587 9.87868 14.1213C10.4413 14.6839 11.2044 15 12 15C12.7956 15 13.5587 14.6839 14.1213 14.1213C14.6839 13.5587 15 12.7956 15 12V4C15 3.20435 14.6839 2.44129 14.1213 1.87868C13.5587 1.31607 12.7956 1 12 1Z" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            <path d="M19 10V12C19 13.8565 18.2625 15.637 16.9497 16.9497C15.637 18.2625 13.8565 19 12 19C10.1435 19 8.36301 18.2625 7.05025 16.9497C5.7375 15.637 5 13.8565 5 12V10" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            <path d="M12 19V23" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            <path d="M8 23H16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
        )}
      </button>
    </div>
  );
};

export default InputArea;
