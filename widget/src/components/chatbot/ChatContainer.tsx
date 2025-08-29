import React, { useState, useEffect } from 'react';
import { ChatContainerProps } from '../../types';
import MessageList from './MessageList';
import InputArea from './InputArea';
import ActionButtons from './ActionButtons';
import ChatFooter from './ChatFooter';
import CampaignTab from './CampaignTab';
import FAQTab from './FAQTab';

import { useChat } from '../../hooks/useChat';
import './chatbot.css';

const ChatContainer: React.FC<ChatContainerProps> = ({ 
  config, 
  onEvent, 
  onFeedback, 
  onToggleChat
}) => {
  const [isSoundEnabled, setIsSoundEnabled] = useState(true);
  const [showCampaignTab, setShowCampaignTab] = useState(false);
  const [showFAQTab, setShowFAQTab] = useState(false);
  const [isTabTransitioning, setIsTabTransitioning] = useState(false);
  const [isChatVisible, setIsChatVisible] = useState(false);
  const [widgetCustomization, setWidgetCustomization] = useState({
    ai_name: 'Kadir AI',
    welcome_message: 'Merhaba ben Kadir, senin dijital asistanınım. Sana nasıl yardımcı olabilirim?'
  });
  
  const { 
    messages, 
    sendMessage, 
    handleProductClick, 
    handleFeedback
  } = useChat();

  // Load widget customization on component mount
  useEffect(() => {
    loadWidgetCustomization();
  }, []);

  const loadWidgetCustomization = async () => {
    try {
      // Check if config is available
      if (typeof window !== 'undefined' && (window as any).WIDGET_CONFIG) {
        const config = (window as any).WIDGET_CONFIG;
        
        // Try to load from API first
        try {
          const response = await fetch((window as any).buildApiUrl('/api/widget-customization'), {
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          });
          
          if (response.ok) {
            const data = await response.json();
            
            if (data.success && data.data) {
              setWidgetCustomization({
                ai_name: data.data.ai_name || config.DEFAULT_AI_NAME,
                welcome_message: data.data.welcome_message || config.DEFAULT_WELCOME_MESSAGE
              });
              return;
            }
          }
        } catch (apiError) {
          console.log('API not available, using default values');
        }
        
        // Use default values from config
        setWidgetCustomization({
          ai_name: config.DEFAULT_AI_NAME,
          welcome_message: config.DEFAULT_WELCOME_MESSAGE
        });
      } else {
        // Fallback to hardcoded values
        setWidgetCustomization({
          ai_name: 'Kadir AI',
          welcome_message: 'Merhaba ben Kadir, senin dijital asistanınım. Sana nasıl yardımcı olabilirim?'
        });
      }
    } catch (error) {
      console.error('Widget customization error:', error);
      // Use default values on error
      setWidgetCustomization({
        ai_name: 'Kadir AI',
        welcome_message: 'Merhaba ben Kadir, senin dijital asistanınım. Sana nasıl yardımcı olabilirim?'
      });
    }
  };

  // Kampanya ile ilgili mesajları tespit et ve direkt tab 2'ye geç
  useEffect(() => {
    const lastMessage = messages[messages.length - 1];
    if (lastMessage && lastMessage.role === 'user') {
      const message = lastMessage.content.toLowerCase();
      const campaignKeywords = ['kampanya', 'kampanyalar', 'kampanyalarda', 'indirim', 'fırsat', 'bedava', 'ücretsiz', 'taksit', 'promosyon', 'teklif', 'özel', 'avantaj'];
      
      // Özel durum: "kampanyalarda neler var" sorusu için tab navigation göster ama otomatik geçiş yapma
      if (message.includes('kampanyalarda neler var')) {
        onEvent({
          type: 'message_sent',
          timestamp: new Date(),
          sessionId: 'session_id',
          data: { action: 'campaign_inquiry', message }
        });
        return; // Otomatik tab geçişi yapma
      }
      
      const hasCampaignKeyword = campaignKeywords.some(keyword => 
        message.includes(keyword)
      );
      
      if (hasCampaignKeyword) {
        setShowCampaignTab(true);
        
        // Kampanya mesajını ekleme - direkt tab değişimi
        onEvent({
          type: 'message_sent',
          timestamp: new Date(),
          sessionId: 'session_id',
          data: { action: 'campaign_tab_opened', message }
        });
      }
    }
  }, [messages, onEvent]);

  const handleSoundToggle = () => {
    setIsSoundEnabled(!isSoundEnabled);
    onEvent({
      type: 'message_sent',
      timestamp: new Date(),
      sessionId: 'session_id',
      data: { action: 'sound_toggle', enabled: !isSoundEnabled }
    });
  };

  const handleSendMessage = (message: string) => {
    sendMessage(message);
    onEvent({
      type: 'message_sent',
      timestamp: new Date(),
      sessionId: 'session_id',
      data: { message }
    });
  };

  const handleProductClickEvent = (product: any) => {
    handleProductClick(product);
    onEvent({
      type: 'product_clicked',
      timestamp: new Date(),
      sessionId: 'session_id',
      data: { product }
    });
  };

  const handleFeedbackEvent = (feedback: any) => {
    handleFeedback(feedback);
    onEvent({
      type: 'feedback_received',
      timestamp: new Date(),
      sessionId: 'session_id',
      data: { feedback }
    });
  };

  const handleActionClick = (action: string) => {
    // Send action as a message
    sendMessage(action);
    onEvent({
      type: 'message_sent',
      timestamp: new Date(),
      sessionId: 'session_id',
      data: { action }
    });
  };

  const handleCloseCampaignTab = () => {
    setShowCampaignTab(false);
  };

  const handleBottomAvatarClick = () => {
    setIsChatVisible(!isChatVisible);
    onEvent({
      type: 'message_sent',
      timestamp: new Date(),
      sessionId: 'session_id',
      data: { action: 'chat_toggle', visible: !isChatVisible }
    });
  };



  const handleTabChange = (tabName: string) => {
    setIsTabTransitioning(true);
    
    // Smooth transition için kısa delay
    setTimeout(() => {
      // Reset all tabs first
      setShowCampaignTab(false);
      setShowFAQTab(false);
      
      // Show selected tab
      if (tabName === 'campaign') {
        setShowCampaignTab(true);
      } else if (tabName === 'faq') {
        setShowFAQTab(true);
      }
      // chat tab is default (both false)
      
      // Transition tamamlandıktan sonra state'i sıfırla
      setTimeout(() => {
        setIsTabTransitioning(false);
      }, 400); // CSS transition süresi ile eşleştir
    }, 100);
  };

  return (
    <>
      {/* Main Chat Container */}
      <div className={`chat-container ${!isChatVisible ? 'hidden' : ''}`}>
        {/* Main Header Container - Combined Header and Tabs */}
        <div className="main-header">
          {/* Header */}
          <div className="chat-header">
            <div className="header-left">
              <div className="agent-avatar">
                <img src="/imgs/ai-conversion-logo-small.svg" alt="AI Conversion Agent" />
              </div>
              <span className="agent-name">{widgetCustomization.ai_name}</span>
            </div>
            <div className="header-right">
              {/* Sound Toggle Button */}
              <button 
                className={`icon-button ${isSoundEnabled ? 'sound-enabled' : ''}`}
                onClick={handleSoundToggle}
                title={isSoundEnabled ? "Sound On" : "Sound Off"}
              >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path d="M12 5L8 9H4V15H8L12 19V5Z" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                  <path d="M15.54 8.46C16.4774 9.39764 17.0039 10.6692 17.0039 12C17.0039 13.3308 16.4774 14.6024 15.54 15.54" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
              </button>
            </div>
          </div>

          {/* Tabs Section - Now inside main-header */}
          <div className="tabs-section">
            <div className="tab-navigation">
              <button 
                className={`tab-button ${!showCampaignTab && !showFAQTab ? 'active' : ''}`}
                onClick={() => handleTabChange('chat')}
                title="Chat"
              >
                💬 Chat
              </button>
              <button 
                className={`tab-button ${showCampaignTab ? 'active' : ''}`}
                onClick={() => handleTabChange('campaign')}
                title="Kampanyalar"
              >
                🎯 Kampanyalar
              </button>
              <button 
                className={`tab-button ${showFAQTab ? 'active' : ''}`}
                onClick={() => handleTabChange('faq')}
                title="Sık Sorulan Sorular"
              >
                ❓ SSS
              </button>
            </div>
          </div>
        </div>

        {/* Tab Content */}
        <div className={`tab-content ${isTabTransitioning ? 'transitioning' : ''}`}>
          {/* Chat Tab */}
          <div className={`tab-panel ${!showCampaignTab ? 'active' : ''}`}>
            <div className="aiagent-container">
              {/* Content */}
              <div className="chat-content">
                <MessageList 
                  messages={messages}
                  onProductClick={handleProductClickEvent} 
                  onFeedback={handleFeedbackEvent}
                  welcomeMessage={widgetCustomization.welcome_message}
                />
              </div>
              
              {/* Action Buttons */}
              <ActionButtons 
                onAction={handleActionClick} 
              />

              {/* Input Area */}
              <InputArea 
                onSendMessage={handleSendMessage} 
              />

              {/* Footer */}
              <ChatFooter />
            </div>
          </div>

          {/* Campaign Tab */}
          <div className={`tab-panel ${showCampaignTab ? 'active' : ''}`}>
            <CampaignTab 
              isVisible={true}
              onClose={handleCloseCampaignTab}
            />
          </div>

          {/* FAQ Tab */}
          <div className={`tab-panel ${showFAQTab ? 'active' : ''}`}>
            <FAQTab 
              isVisible={true}
              onClose={() => handleTabChange('chat')}
            />
          </div>
        </div>
      </div>

      {/* Bottom Right Avatar */}
      <div className="bottom-avatar" onClick={handleBottomAvatarClick}>
        <img 
          src="/imgs/ai-conversion-logo.svg" 
          alt="AI Conversion Assistant" 
        />
      </div>
    </>
  );
};

export default ChatContainer;
