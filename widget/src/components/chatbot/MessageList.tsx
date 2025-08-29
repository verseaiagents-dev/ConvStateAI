import React from 'react';
import { MessageListProps } from '../../types';
import MessageItem from './MessageItem';
import TTSButton from './TTSButton';
import { useChat } from '../../hooks/useChat';
import ShimmerMessage from './ShimmerMessage';

const MessageList: React.FC<MessageListProps> = ({ messages, onProductClick, onFeedback, welcomeMessage: customWelcomeMessage }) => {
  const { isTyping, chatContentRef } = useChat();

  // Default welcome message
  const defaultWelcomeMessage = "Merhaba ben Kadir, senin dijital asistanınım. Sana nasıl yardımcı olabilirim?";
  const welcomeText = customWelcomeMessage || defaultWelcomeMessage;

  // Welcome message
  const welcomeMessage = (
    <div className="message-group">
      <div className="agent-avatar large">
        <img src="/imgs/ai-conversion-logo-small.svg" alt="AI Conversion Agent" />
      </div>
      <div className="message agent-message">
        <p>{welcomeText}</p>
        <TTSButton text={welcomeText} />
      </div>
    </div>
  );



  // Typing indicator
  const typingIndicator = isTyping ? (
    <div className="message-group">
      <div className="agent-avatar">
        <img src="/imgs/ai-conversion-logo-small.svg" alt="AI Conversion Agent" />
      </div>
      <ShimmerMessage type="general" />
    </div>
  ) : null;

  return (
    <div className="chat-content" ref={chatContentRef}>
      {/* Welcome Message */}
      {welcomeMessage}
      
      {/* Messages from props */}
      {messages.map((message) => (
        <MessageItem
          key={message.id}
          message={message}
          onProductClick={onProductClick}
          onFeedback={onFeedback}
        />
      ))}
      
      {/* Typing Indicator */}
      {typingIndicator}
    </div>
  );
};

export default MessageList;
