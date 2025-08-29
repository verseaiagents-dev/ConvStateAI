import React, { useState } from 'react';
import './App.css';
import ChatContainer from './components/chatbot/ChatContainer';
import TestCampaignTab from './components/TestCampaignTab';
import { WidgetConfig } from './types';

function App() {
  const [showTestTab, setShowTestTab] = useState(false);
  
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
      {/* Test Tab Toggle Button */}
      <div style={{ 
        position: 'fixed', 
        top: '20px', 
        left: '20px', 
        zIndex: 1000,
        backgroundColor: '#007bff',
        color: 'white',
        padding: '10px 15px',
        borderRadius: '5px',
        cursor: 'pointer',
        border: 'none',
        fontSize: '14px'
      }}>
        <button 
          onClick={() => setShowTestTab(!showTestTab)}
          style={{
            backgroundColor: 'transparent',
            color: 'white',
            border: 'none',
            cursor: 'pointer',
            fontSize: '14px'
          }}
        >
          {showTestTab ? '❌ Hide Test' : '🧪 Show Test'}
        </button>
      </div>

      {/* Test Tab */}
      {showTestTab && (
        <div style={{ 
          position: 'fixed', 
          top: '0', 
          left: '0', 
          width: '100%', 
          height: '100%', 
          backgroundColor: 'rgba(0,0,0,0.8)', 
          zIndex: 999,
          overflow: 'auto'
        }}>
          <TestCampaignTab />
        </div>
      )}

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
