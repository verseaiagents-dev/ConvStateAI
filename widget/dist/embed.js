/**
 * VersAI Widget Embed Script
 * Bu script'i sitenizin <head> veya </body> tag'inden önce ekleyin
 */

(function() {
  'use strict';

  // Widget configuration
  var defaultConfig = {
    siteId: 'default-site',
    colors: {
      primary: '#3B82F6',
      secondary: '#6B7280',
      background: '#FFFFFF',
      text: '#1F2937',
      accent: '#F59E0B'
    },
    branding: {
      logo: '',
      name: 'VersAI',
      welcomeMessage: 'Merhaba! Size nasıl yardımcı olabilirim?'
    },
    features: {
      templates: ['catalog', 'checkout', 'wheelspin'],
      aiEnabled: true,
      eventTracking: true
    },
    styling: {
      position: 'bottom-right',
      size: 'medium',
      theme: 'light'
    }
  };

  // Widget state
  var widgetState = {
    isLoaded: false,
    isVisible: false,
    config: null,
    sessionId: null
  };

  // Generate unique session ID
  function generateSessionId() {
    return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
  }

  // Create widget container
  function createWidgetContainer() {
    var container = document.createElement('div');
    container.id = 'versai-widget-container';
    container.style.cssText = `
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 350px;
      height: 500px;
      z-index: 10000;
      display: none;
      font-family: 'Inter', system-ui, sans-serif;
    `;
    
    // Set position based on config
    if (widgetState.config.styling.position === 'bottom-left') {
      container.style.left = '20px';
      container.style.right = 'auto';
    } else if (widgetState.config.styling.position === 'center') {
      container.style.left = '50%';
      container.style.top = '50%';
      container.style.transform = 'translate(-50%, -50%)';
      container.style.bottom = 'auto';
      container.style.right = 'auto';
    }

    return container;
  }

  // Create toggle button
  function createToggleButton() {
    var button = document.createElement('button');
    button.id = 'versai-widget-toggle';
    button.innerHTML = `
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
      </svg>
    `;
    
    button.style.cssText = `
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: ${widgetState.config.colors.primary};
      color: white;
      border: none;
      cursor: pointer;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    `;

    // Set position based on config
    if (widgetState.config.styling.position === 'bottom-left') {
      button.style.left = '20px';
      button.style.right = 'auto';
    }

    button.addEventListener('click', toggleWidget);
    button.addEventListener('mouseenter', function() {
      this.style.transform = 'scale(1.1)';
    });
    button.addEventListener('mouseleave', function() {
      this.style.transform = 'scale(1)';
    });

    return button;
  }

  // Toggle widget visibility
  function toggleWidget() {
    var container = document.getElementById('versai-widget-container');
    var toggle = document.getElementById('versai-widget-toggle');
    
    if (widgetState.isVisible) {
      container.style.display = 'none';
      toggle.style.display = 'flex';
      widgetState.isVisible = false;
    } else {
      container.style.display = 'block';
      toggle.style.display = 'none';
      widgetState.isVisible = true;
      
      // Track widget open event
      trackEvent('widget_opened', {
        timestamp: new Date().toISOString(),
        sessionId: widgetState.sessionId
      });
    }
  }

  // Track events
  function trackEvent(eventType, data) {
    if (!widgetState.config.features.eventTracking) return;
    
    try {
      // Send to analytics or backend
      console.log('VersAI Event:', eventType, data);
      
      // You can implement your own tracking logic here
      if (typeof gtag !== 'undefined') {
        gtag('event', 'versai_' + eventType, data);
      }
    } catch (error) {
      console.warn('VersAI: Event tracking failed:', error);
    }
  }

  // Initialize widget
  function initWidget(config) {
    // Merge config with defaults
    widgetState.config = Object.assign({}, defaultConfig, config);
    widgetState.sessionId = generateSessionId();
    
    // Create and append elements
    var container = createWidgetContainer();
    var toggle = createToggleButton();
    
    document.body.appendChild(container);
    document.body.appendChild(toggle);
    
    // Load widget content
    loadWidgetContent(container);
    
    widgetState.isLoaded = true;
    
    // Track initialization
    trackEvent('widget_initialized', {
      timestamp: new Date().toISOString(),
      sessionId: widgetState.sessionId,
      config: widgetState.config
    });
    
    console.log('VersAI Widget initialized successfully');
  }

  // Load widget content (placeholder for now)
  function loadWidgetContent(container) {
    container.innerHTML = `
      <div style="
        width: 100%;
        height: 100%;
        background: ${widgetState.config.colors.background};
        border: 1px solid ${widgetState.config.colors.secondary};
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
        overflow: hidden;
      ">
        <!-- Header -->
        <div style="
          padding: 16px;
          background: ${widgetState.config.colors.primary};
          color: white;
          border-top-left-radius: 12px;
          border-top-right-radius: 12px;
          display: flex;
          align-items: center;
          gap: 12px;
        ">
          <div style="
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: ${widgetState.config.colors.accent};
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
          ">
            ${widgetState.config.branding.name.charAt(0).toUpperCase()}
          </div>
          <span style="font-size: 16px; font-weight: 600;">
            ${widgetState.config.branding.name}
          </span>
        </div>
        
        <!-- Content -->
        <div style="
          flex: 1;
          padding: 16px;
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
          text-align: center;
          color: ${widgetState.config.colors.text};
        ">
          <div style="
            width: 64px;
            height: 64px;
            background: ${widgetState.config.colors.primary}20;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
          ">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
          </div>
          <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">
            ${widgetState.config.branding.welcomeMessage}
          </h3>
          <p style="font-size: 14px; color: ${widgetState.config.colors.secondary};">
            Widget yükleniyor...
          </p>
        </div>
      </div>
    `;
  }

  // Public API
  window.VersAIWidget = {
    init: function(config) {
      if (widgetState.isLoaded) {
        console.warn('VersAI Widget already initialized');
        return;
      }
      
      if (typeof config === 'object') {
        initWidget(config);
      } else {
        initWidget({});
      }
    },
    
    show: function() {
      if (!widgetState.isLoaded) {
        console.warn('VersAI Widget not initialized');
        return;
      }
      
      var container = document.getElementById('versai-widget-container');
      var toggle = document.getElementById('versai-widget-toggle');
      
      if (container && toggle) {
        container.style.display = 'block';
        toggle.style.display = 'none';
        widgetState.isVisible = true;
      }
    },
    
    hide: function() {
      if (!widgetState.isLoaded) {
        console.warn('VersAI Widget not initialized');
        return;
      }
      
      var container = document.getElementById('versai-widget-container');
      var toggle = document.getElementById('versai-widget-toggle');
      
      if (container && toggle) {
        container.style.display = 'none';
        toggle.style.display = 'flex';
        widgetState.isVisible = false;
      }
    },
    
    track: function(eventType, data) {
      trackEvent(eventType, data);
    },
    
    getConfig: function() {
      return widgetState.config;
    },
    
    getSessionId: function() {
      return widgetState.sessionId;
    }
  };

  // Auto-initialize if config is available
  if (window.VersAIWidgetConfig) {
    window.VersAIWidget.init(window.VersAIWidgetConfig);
  }

  console.log('VersAI Widget embed script loaded');
})();
