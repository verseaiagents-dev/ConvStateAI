// Widget Configuration Types
export interface WidgetConfig {
  siteId: string;
  colors: {
    primary: string;
    secondary: string;
    background: string;
    text: string;
    accent: string;
  };
  branding: {
    logo: string;
    name: string;
    welcomeMessage: string;
  };
  features: {
    templates: string[];
    aiEnabled: boolean;
    eventTracking: boolean;
  };
  styling: {
    position: 'bottom-right' | 'bottom-left' | 'center';
    size: 'small' | 'medium' | 'large';
    theme: 'light' | 'dark' | 'auto';
  };
}

// Message Types
export interface Message {
  id: string;
  role: 'user' | 'agent';
  content: string;
  timestamp: Date;
  template?: string;
  products?: Product[];
  intent?: string;
  type?: string;
  message?: string;
  suggestions?: string[];
  data?: any;
  shimmerType?: 'general' | 'product' | 'cargo' | 'order' | 'feedback';
  isShimmer?: boolean;
}



// Product Types
export interface Product {
  id: string;
  name: string;
  brand: string;
  price: number;
  image: string;
  category: string;
  rating: number;
  description?: string;
  url?: string; // Ürün detay sayfası URL'i
}

// Event Types
export interface WidgetEvent {
  type: 'message_sent' | 'product_clicked' | 'template_viewed' | 'checkout_started' | 'feedback_received';
  timestamp: Date;
  sessionId: string;
  data: Record<string, any>;
}

// Feedback Types
export interface FeedbackData {
  messageId: string;
  isHelpful: boolean;
  comment?: string;
}

// Template Types
export interface CatalogTemplateProps {
  products: Product[];
  title: string;
  onProductClick: (product: Product) => void;
  onViewAll: () => void;
}

export interface CheckoutTemplateProps {
  abandonedProducts: Product[];
  discountCode?: string;
  onContinueCheckout: () => void;
  onViewCart: () => void;
}

export interface WheelSpinTemplateProps {
  prizes: Prize[];
  onSpin: () => void;
  isSpinning: boolean;
}

export interface Prize {
  id: string;
  name: string;
  value: string;
  probability: number;
}

// Chat Container Props
export interface ChatContainerProps {
  config: WidgetConfig;
  onEvent: (event: WidgetEvent) => void;
  onFeedback: (feedback: FeedbackData) => void;
  onToggleChat?: () => void;
}

// Input Area Props
export interface InputAreaProps {
  onSendMessage: (message: string) => void;
  disabled?: boolean;
  placeholder?: string;
  setMessage?: (message: string) => void;
}

// Message List Props
export interface MessageListProps {
  messages: Message[];
  onProductClick: (product: Product) => void;
  onFeedback: (feedback: FeedbackData) => void;
  welcomeMessage?: string;
}
