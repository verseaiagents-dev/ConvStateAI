# 2. Widget Geliştirme Planı

## 2.1 Genel Bakış

Mevcut ui.1/index.html tasarımını baz alarak React + TypeScript tabanlı modern widget sistemi geliştirilecek.

## 2.2 Teknoloji Stack

### 2.2.1 Frontend Framework
- **React 18** + **TypeScript 5**
- **Create React App** veya **Parcel** - Basit build tool
- **Hybrid Styling Approach**:
  - **Chatbot UI**: Inline CSS (mevcut tasarım korunacak)
  - **Admin Panels & Templates**: Tailwind CSS
- **CSS Animations** - Native CSS transitions

### 2.2.2 State Management
- **useState + useContext** - Basit state management
- **Custom hooks** - Reusable logic

### 2.2.3 UI Components
- **Custom components** - Basit, lightweight components
- **SVG icons** - Inline SVG icons
- **Native form handling** - HTML5 form validation

## 2.3 Widget Mimarisi

### 2.3.1 Core Components

#### 2.3.1.1 ChatContainer (Inline CSS - Mevcut Tasarım)
```typescript
interface ChatContainerProps {
  config: WidgetConfig;
  onEvent: (event: WidgetEvent) => void;
  onFeedback: (feedback: FeedbackData) => void;
}

// Mevcut ui.1 tasarımı korunarak inline CSS kullanılacak
// style.css'deki .chat-container, .chat-header, .chat-content stilleri
```

#### Template Components (Tailwind CSS)
```typescript
interface TemplateContainerProps {
  template: string;
  data: any;
  onAction: (action: string) => void;
}

// Template components Tailwind CSS ile stillendirilecek
```

#### MessageList
```typescript
interface Message {
  id: string;
  role: 'user' | 'bot';
  content: string;
  timestamp: Date;
  template?: string;
  products?: Product[];
  intent?: string;
}
```

#### InputArea
```typescript
interface InputAreaProps {
  onSendMessage: (message: string) => void;
  disabled?: boolean;
  placeholder?: string;
}
```

### 2.3.2 Template System (Tailwind CSS)

#### 2.3.2.1 CatalogTemplate
```typescript
interface CatalogTemplateProps {
  products: Product[];
  title: string;
  onProductClick: (product: Product) => void;
  onViewAll: () => void;
}

// Tailwind CSS ile modern, responsive design
// Mevcut ürün kartı tasarımı korunarak Tailwind ile enhance edilecek
```

#### 2.3.2.2 CheckoutTemplate
```typescript
interface CheckoutTemplateProps {
  abandonedProducts: Product[];
  discountCode?: string;
  onContinueCheckout: () => void;
  onViewCart: () => void;
}
```

#### 2.3.2.3 WheelSpinTemplate
```typescript
interface WheelSpinTemplateProps {
  prizes: Prize[];
  onSpin: () => void;
  isSpinning: boolean;
}
```

### 2.3.3 Configuration System

#### 2.3.3.1 WidgetConfig
```typescript
interface WidgetConfig {
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
```

## 2.4 Widget Özellikleri

### 2.4.1 Hybrid Styling System
- **Chatbot UI**: Mevcut ui.1 tasarımı korunarak inline CSS
- **Template Components**: Tailwind CSS ile modern, responsive design
- **Admin Panels**: Tailwind CSS ile professional interface

### 2.4.2 Responsive Design
- Mobile-first approach
- Breakpoint-based layout adjustments
- Touch-friendly interactions

### 2.4.3 Accessibility
- ARIA labels ve roles
- Keyboard navigation support
- Screen reader compatibility
- High contrast mode support

### 2.4.4 Animation & Transitions
- Smooth message animations
- Loading states
- Hover effects
- Micro-interactions

### 2.4.5 Localization
- Multi-language support
- RTL language support
- Date/time formatting
- Number formatting

## 2.5 Event Tracking System

### 2.5.1 User Interaction Events
```typescript
interface WidgetEvent {
  type: 'message_sent' | 'product_clicked' | 'template_viewed' | 'checkout_started';
  timestamp: Date;
  sessionId: string;
  data: Record<string, any>;
}
```

### 2.5.2 Analytics Events
- Widget load time
- Message response time
- Template engagement rates
- Conversion tracking

### 2.5.3 Performance Monitoring
- Bundle size optimization
- Lazy loading
- Memory usage tracking
- Error boundary implementation

## 2.6 Build & Distribution

### 2.6.1 Development Setup
```bash
# Widget development
cd widget
npm install
npm start

# Build for production
npm run build
```

### 2.6.2 Styling Setup
```bash
# Tailwind CSS kurulumu (sadece template ve admin için)
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p

# Tailwind config - sadece template ve admin dosyalarını hedefle
# tailwind.config.js
module.exports = {
  content: [
    "./src/templates/**/*.{js,ts,jsx,tsx}",
    "./src/admin/**/*.{js,ts,jsx,tsx}",
    "./src/components/templates/**/*.{js,ts,jsx,tsx}"
  ],
  // Chatbot UI'ı exclude et
  exclude: [
    "./src/components/chatbot/**/*"
  ]
}
```

### 2.6.3 Build Output
- `dist/widget.js` - Main widget bundle
- `dist/widget.css` - Tailwind CSS (template ve admin için)
- `dist/chatbot.css` - Chatbot inline styles (mevcut tasarım)
- `dist/embed.js` - Embed script

### 2.6.4 Embed Script
```html
<script>
  (function() {
    var script = document.createElement('script');
    script.src = 'https://cdn.example.com/widget.js';
    script.async = true;
    script.onload = function() {
      window.VersAIWidget.init({
        siteId: 'your-site-id',
        config: {
          // widget configuration
        }
      });
    };
    document.head.appendChild(script);
  })();
</script>
```

## 2.7 Testing Strategy

### 2.7.1 Unit Tests
- Jest + React Testing Library
- Component behavior testing
- Hook testing
- Utility function testing

### 2.7.2 Integration Tests
- API integration testing
- Event handling testing
- Configuration loading testing

### 2.7.3 E2E Tests
- Playwright
- User flow testing
- Cross-browser testing
- Mobile testing

## 2.8 Performance Optimization

### 2.8.1 Bundle Optimization
- Code splitting (chatbot ve template ayrı)
- Tree shaking
- Dynamic imports
- Bundle analysis
- **CSS Separation**: Chatbot inline CSS, Template Tailwind CSS ayrı bundle'lar

### 2.8.2 Styling Performance
- **Chatbot**: Inline CSS ile hızlı render
- **Templates**: Tailwind CSS ile utility-first approach
- **Conditional Loading**: Sadece gerekli CSS yükle

### 2.8.3 Runtime Optimization
- Memoization
- Debouncing
- Virtual scrolling
- Image optimization

### 2.8.4 Caching Strategy
- Service worker
- Local storage
- Memory caching
- API response caching

## 2.9 Security Considerations

### 2.9.1 XSS Prevention
- Content sanitization
- Safe HTML rendering
- CSP headers

### 2.9.2 Data Validation
- Input validation
- Type checking
- Schema validation

### 2.9.3 Privacy
- GDPR compliance
- Data anonymization
- Consent management

## 2.10 Deployment & Monitoring

### 2.10.1 CDN Distribution
- Global CDN setup
- Version management
- Rollback strategy

### 2.10.2 Monitoring
- Error tracking (Sentry)
- Performance monitoring
- Usage analytics
- Health checks

### 2.10.3 A/B Testing
- Feature flags
- Configuration variants
- User segmentation
- Impact measurement

## 2.11 Geliştirme Timeline

### 2.11.1 Phase 1: Core Widget (2-3 hafta)
- Basic chat functionality
- Message handling
- **Chatbot UI**: Mevcut ui.1 tasarımı ile inline CSS
- **Basic UI components**: ChatContainer, MessageList, InputArea

### 2.11.2 Phase 2: Template System (2-3 hafta)
- **Template components**: Tailwind CSS ile modern design
- **Dynamic rendering**: Template switching
- **Product integration**: Ürün kartları ve öneriler
- **CSS Setup**: Tailwind kurulumu ve configuration

### 2.11.3 Phase 3: Configuration & Events (1-2 hafta)
- Widget configuration
- Event tracking
- Analytics integration

### 2.11.4 Phase 4: Testing & Optimization (1-2 hafta)
- Testing implementation
- Performance optimization
- Security hardening

### 2.11.5 Phase 5: Deployment & Monitoring (1 hafta)
- Production deployment
- Monitoring setup
- Documentation

## 2.12 Sonraki Adımlar

### 2.12.1 Widget Proje Yapısı
1. **Widget proje yapısını oluştur**
2. **Core components geliştir**
3. **Template system implement et**

### 2.12.2 Sistem Entegrasyonu
4. **Configuration system kur**
5. **Event tracking ekle**
6. **Testing implement et**
7. **Build ve deployment hazırla**
