// Widget Configuration
window.WIDGET_CONFIG = {
    // API Endpoints
    API_BASE_URL: 'http://127.0.0.1:8000',
    CHAT_ENDPOINT: '/api/chat',
    PRODUCTS_ENDPOINT: '/api/products',
    WIDGET_CUSTOMIZATION_ENDPOINT: '/api/widget-customization',
    
    // Default AI Settings
    DEFAULT_AI_NAME: 'Kadir AI',
    DEFAULT_WELCOME_MESSAGE: 'Merhaba ben Kadir, senin dijital asistanınım. Sana nasıl yardımcı olabilirim?',
    
    // Session Management
    SESSION_STORAGE_KEY: 'chat_session_id',
    
    // Product Display Settings
    MAX_PRODUCTS_TO_SHOW: 6,
    PRODUCT_IMAGE_PATH: '/imgs/',
    
    // Chat Settings
    TYPING_DELAY: 1000,
    MAX_MESSAGE_LENGTH: 1000,
    
    // Feature Flags
    ENABLE_TTS: true,
    ENABLE_PRODUCT_RECOMMENDATIONS: true,
    ENABLE_CAMPAIGN_TAB: true,
    ENABLE_FAQ_TAB: true
};

// Helper function to get config values
window.getWidgetConfig = function(key) {
    return window.WIDGET_CONFIG[key] || null;
};

// Helper function to build API URL
window.buildApiUrl = function(endpoint) {
    return window.WIDGET_CONFIG.API_BASE_URL + endpoint;
};
