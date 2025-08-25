import React from 'react';
import { useTTS } from '../../hooks/useTTS';

interface TTSButtonProps {
  text: string;
  isUser?: boolean;
  className?: string;
}

const TTSButton: React.FC<TTSButtonProps> = ({ text, isUser = false, className = '' }) => {
  const { speak, stop, isCurrentlyPlaying } = useTTS();

  const handleClick = (e: React.MouseEvent) => {
    e.stopPropagation();
    
    if (isCurrentlyPlaying(text)) {
      // If currently playing this text, stop it
      stop();
    } else {
      // Otherwise, speak the text
      speak(text);
    }
  };

  const isPlaying = isCurrentlyPlaying(text);

  return (
    <button 
      className={`tts-button ${isUser ? 'user' : ''} ${isPlaying ? 'playing' : ''} ${className}`}
      onClick={handleClick}
      title={isPlaying ? "Sesi Durdur" : "Metni Seslendir"}
    >
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
        <path d="M12 5L8 9H4V15H8L12 19V5Z" stroke={isUser ? "white" : "#2563EB"} strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
        <path d="M15.54 8.46C16.4774 9.39764 17.0039 10.6692 17.0039 12C17.0039 13.3308 16.4774 14.6024 15.54 15.54" stroke={isUser ? "white" : "#2563EB"} strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
      </svg>
    </button>
  );
};

export default TTSButton;
