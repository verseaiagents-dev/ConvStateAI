# VersAI Widget

React + TypeScript tabanlı modern chatbot widget sistemi. Mevcut ui.1 tasarımını koruyarak inline CSS kullanır, template sistemleri için Tailwind CSS kullanır.

## 🚀 Özellikler

### Chatbot UI (Inline CSS)
- ✅ Mevcut ui.1 tasarımı korundu
- ✅ Message handling ve feedback sistemi
- ✅ Product display ve interaction
- ✅ Responsive design
- ✅ Accessibility support

### Template System (Tailwind CSS)
- ✅ **CatalogTemplate**: Ürün kataloğu
- ✅ **CheckoutTemplate**: Sepet hatırlatma
- ✅ **WheelSpinTemplate**: Şans çarkı
- ✅ Modern ve responsive design
- ✅ Interactive components

### Hybrid Styling Approach
- **Chatbot UI**: Inline CSS (mevcut tasarım korundu)
- **Templates & Admin**: Tailwind CSS (modern design)

## 🛠️ Teknoloji Stack

- **Frontend**: React 18 + TypeScript 5
- **Build Tool**: Create React App
- **Styling**: Hybrid (Inline CSS + Tailwind CSS)
- **State Management**: useState + useContext + Custom Hooks
- **Components**: Custom components + SVG icons

## 📁 Proje Yapısı

```
src/
├── components/
│   ├── chatbot/           # Chatbot UI (Inline CSS)
│   │   ├── ChatContainer.tsx
│   │   ├── MessageList.tsx
│   │   ├── MessageItem.tsx
│   │   └── InputArea.tsx
│   └── templates/         # Template System (Tailwind CSS)
│       ├── CatalogTemplate.tsx
│       ├── CheckoutTemplate.tsx
│       └── WheelSpinTemplate.tsx
├── types/                 # TypeScript definitions
├── hooks/                 # Custom hooks
├── utils/                 # Utility functions
└── App.tsx               # Main application
```

## 🚀 Kurulum

### Gereksinimler
- Node.js 16+
- npm veya yarn

### Kurulum Adımları

1. **Bağımlılıkları yükle:**
```bash
npm install
```

2. **Development server'ı başlat:**
```bash
npm start
```

3. **Production build:**
```bash
npm run build
```

4. **Widget build (dist klasörüne kopyala):**
```bash
npm run build:widget
```

## 🎯 Kullanım

### Widget Entegrasyonu

1. **Embed script'i sitenize ekleyin:**
```html
<script src="https://cdn.example.com/embed.js"></script>
```

2. **Widget'ı initialize edin:**
```javascript
window.VersAIWidget.init({
  siteId: 'your-site-id',
  colors: {
    primary: '#3B82F6',
    secondary: '#6B7280',
    background: '#FFFFFF',
    text: '#1F2937',
    accent: '#F59E0B'
  },
  branding: {
    name: 'Your Brand',
    welcomeMessage: 'Merhaba! Size nasıl yardımcı olabilirim?'
  }
});
```

### Template Kullanımı

```tsx
import CatalogTemplate from './components/templates/CatalogTemplate';

<CatalogTemplate
  products={products}
  title="Önerilen Ürünler"
  onProductClick={handleProductClick}
  onViewAll={handleViewAll}
/>
```

## 🎨 Styling

### Chatbot UI (Inline CSS)
- Mevcut ui.1 tasarımı korundu
- Responsive ve accessible
- Custom color scheme support

### Templates (Tailwind CSS)
- Modern utility-first approach
- Responsive design
- Interactive hover effects
- Custom color schemes

## 📱 Responsive Design

- Mobile-first approach
- Breakpoint-based layouts
- Touch-friendly interactions
- Adaptive sizing

## ♿ Accessibility

- ARIA labels ve roles
- Keyboard navigation
- Screen reader support
- High contrast mode
- Focus management

## 🔧 Konfigürasyon

### Widget Config
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

## 📊 Event Tracking

Widget otomatik olarak şu event'leri track eder:
- `widget_initialized`: Widget başlatıldı
- `widget_opened`: Widget açıldı
- `message_sent`: Mesaj gönderildi
- `product_clicked`: Ürün tıklandı
- `template_viewed`: Template görüntülendi

## 🧪 Testing

```bash
# Unit tests
npm test

# E2E tests (Playwright)
npm run test:e2e
```

## 📦 Build & Distribution

### Development
```bash
npm start          # Development server
npm run dev        # Alias for start
```

### Production
```bash
npm run build              # Production build
npm run build:widget       # Widget build + dist kopyala
npm run build:prod         # Production build (no source maps)
```

### Build Output
- `build/` - React build output
- `dist/` - Widget distribution files
  - `embed.js` - Embed script
  - `widget.js` - Widget bundle
  - `widget.css` - Tailwind CSS

## 🌐 Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## 📈 Performance

- Code splitting (chatbot ve template ayrı)
- Lazy loading
- Bundle optimization
- CSS separation
- Memory management

## 🔒 Security

- XSS prevention
- Content sanitization
- Input validation
- CSP headers
- Data validation

## 📝 License

MIT License

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## 📞 Support

- Documentation: [docs.example.com](https://docs.example.com)
- Issues: [GitHub Issues](https://github.com/example/issues)
- Email: support@example.com

---

**VersAI Widget** - Modern chatbot widget sistemi 🚀
