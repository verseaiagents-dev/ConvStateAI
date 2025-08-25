import React, { useState } from 'react';
import './App.css';
import ChatContainer from './components/chatbot/ChatContainer';
import { WidgetConfig } from './types';

function App() {
  
  // Sample configuration
  const config: WidgetConfig = {
    siteId: 'demo-site',
    colors: {
      primary: '#2563EB',
      secondary: '#1D4ED8',
      background: '#FFFFFF',
      text: '#1F2937',
      accent: '#10B981'
    },
    branding: {
      logo: '/imgs/kadirai.jpeg',
      name: 'Kadir AI',
      welcomeMessage: 'Merhaba! Size nasıl yardımcı olabilirim?'
    },
    features: {
      templates: ['catalog', 'checkout', 'wheel-spin'],
      aiEnabled: true,
      eventTracking: true
    },
    styling: {
      position: 'bottom-right',
      size: 'medium',
      theme: 'light'
    }
  };

  // Event handlers
  const handleEvent = (event: any) => {
    // Handle widget events here
  };

  const handleFeedback = (feedback: any) => {
    // Handle feedback here
  };



  return (
    <>
      {/* Widget - Always render ChatContainer, let it handle its own visibility */}
      <ChatContainer 
        config={config}
        onEvent={handleEvent}
        onFeedback={handleFeedback}
        onToggleChat={() => {}}
      />
    </>
  );
}

export default App;
