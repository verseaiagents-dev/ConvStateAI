# ConvStateAI - Laravel AI SaaS Uygulaması

Modern tasarım, AI özellikleri ve gelişmiş kampanya sistemi ile donatılmış Laravel tabanlı SaaS uygulaması.

## 🚀 Özellikler

### Landing Page
- **Modern Tasarım**: Framer.com tarzında glass morphism efektleri
- **Responsive**: Mobil ve desktop uyumlu
- **Alex Hormozi Funnel Sistemi**: Awareness → Interest → Decision → Action
- **Tailwind CSS**: Modern ve hızlı styling
- **Purple Glow Efektleri**: Neon ve gradient animasyonlar

### Kullanıcı Sistemi
- **Kayıt ve Giriş**: Tam kimlik doğrulama sistemi
- **Şifre Sıfırlama**: E-posta ile şifre yenileme
- **Profil Yönetimi**: Kullanıcı bilgileri ve avatar
- **Admin Sistemi**: Kullanıcı tablosunda admin kontrolü

### Dashboard
- **Sidebar Navigation**: 4 ana menü (Dashboard, Kampanyalar, Profil, Ayarlar)
- **Responsive Layout**: Mobil uyumlu sidebar
- **Glass Effect**: Modern görsel tasarım
- **Kullanıcı İstatistikleri**: Dashboard metrikleri
- **Kampanya Yönetimi**: Tam CRUD işlemleri

### Form Elementleri
- **Kapsamlı Form Koleksiyonu**: Tüm HTML form elementleri
- **Validasyon**: Gerçek zamanlı form doğrulama
- **Modern Styling**: Landing page ile uyumlu tasarım
- **Responsive**: Mobil ve desktop uyumlu

### AI Helper Sistemi
- **AIHelper**: AI işlemleri için özel helper
- **GeneralHelper**: Genel yardımcı fonksiyonlar
- **Global Functions**: Kolay erişim için global helper fonksiyonları
- **Composer Autoload**: Otomatik yükleme

### Kampanya Sistemi
- **7 Farklı Kampanya Tipi**: value_stack, scarcity, bundle, gamification, social_proof, lightning_deal, subscribe_and_save
- **Dinamik Form Alanları**: Kampanya tipine göre otomatik form oluşturma
- **JSON Veri Saklama**: Esnek veri yapısı
- **API Entegrasyonu**: RESTful API endpoints
- **Admin Panel**: Tam CRUD işlemleri

## 🛠️ Teknik Detaylar

### Laravel Versiyonu
- **Laravel 12**: En güncel Laravel sürümü
- **PHP 8.1+**: Modern PHP özellikleri
- **Tailwind CSS**: Utility-first CSS framework

### Veritabanı Yapısı
```sql
users table:
- id (primary key)
- name (string)
- email (unique, string)
- password (hashed)
- is_admin (boolean, default: false)
- avatar (nullable string)
- bio (nullable text)
- email_verified_at (timestamp)
- remember_token
- timestamps


```

### Route Yapısı
```
Web Routes:
- / (landing page)
- /login (giriş)
- /register (kayıt)
- /forgot-password (şifre sıfırlama)
- /reset-password/{token} (şifre yenileme)
- /forms (form elementleri)
- /dashboard (ana dashboard)

- /dashboard/profile (profil)
- /dashboard/settings (ayarlar)

API Routes:
- /api/ai/response (AI yanıt)
- /api/ai/personalized (kişiselleştirilmiş yanıt)
- /api/ai/stats (istatistikler)
- /api/ai/test-quality (kalite testi)

```

## 📁 Dosya Yapısı

```
TESTAI/
├── app/
│   ├── Http/Controllers/
│   │   ├── AIController.php
│   │   ├── AuthController.php

│   │   └── DashboardController.php
│   ├── Helpers/
│   │   ├── AIHelper.php
│   │   ├── GeneralHelper.php
│   │   ├── helpers.php
│   │   └── README.md
│   └── Models/

│       └── User.php
├── resources/views/
│   ├── auth/
│   │   ├── login.blade.php
│   │   ├── register.blade.php
│   │   ├── forgot-password.blade.php
│   │   └── reset-password.blade.php
│   ├── dashboard/

│   │   ├── index.blade.php
│   │   ├── profile.blade.php
│   │   └── settings.blade.php
│   ├── layouts/
│   │   └── dashboard.blade.php
│   ├── forms.blade.php
│   └── index.blade.php
├── routes/
│   ├── web.php
│   └── api.php
└── database/migrations/
    ├── 0001_01_01_000000_create_users_table.php

```

## 🚀 Kurulum

### Gereksinimler
- PHP 8.1+
- Composer
- MySQL/PostgreSQL
- Node.js (opsiyonel)

### Adımlar
1. **Projeyi klonlayın**
   ```bash
   git clone <repository-url>
   cd TESTAI
   ```

2. **Bağımlılıkları yükleyin**
   ```bash
   composer install
   npm install
   ```

3. **Environment dosyasını oluşturun**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Veritabanını yapılandırın**
   ```bash
   # .env dosyasında veritabanı bilgilerini güncelleyin
   php artisan migrate:fresh
   ```

5. **Test kullanıcıları oluşturun**
   ```bash
   # Admin kullanıcı
   php artisan tinker --execute="App\Models\User::create(['name' => 'Admin User', 'email' => 'admin@example.com', 'password' => bcrypt('password123'), 'is_admin' => true]);"
   
   # Normal kullanıcı
   php artisan tinker --execute="App\Models\User::create(['name' => 'Test User', 'email' => 'test@example.com', 'password' => bcrypt('password123'), 'is_admin' => false]);"
   ```

6. **Uygulamayı çalıştırın**
   ```bash
   php artisan serve
   ```

## 🔐 Test Kullanıcıları

### Admin Kullanıcı
- **E-posta**: admin@example.com
- **Şifre**: password123
- **Yetki**: Admin

### Test Kullanıcı
- **E-posta**: test@example.com
- **Şifre**: password123
- **Yetki**: Normal kullanıcı

## 🎨 Tasarım Sistemi

### Renk Paleti
- **Primary**: #8B5CF6 (Purple Glow)
- **Secondary**: #A855F7 (Neon Purple)
- **Dark**: #4C1D95 (Purple Dark)
- **Background**: #000000 (Black)
- **Text**: #FFFFFF (White)
- **Gray**: #6B7280 (Gray)

### Efektler
- **Glass Morphism**: Backdrop blur ve transparency
- **Gradient Text**: Çok renkli metin efektleri
- **Floating Animation**: Yumuşak hareket animasyonları
- **Glow Effect**: Neon ışık efektleri

### Responsive Breakpoints
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

## 🔧 Helper Sistemi

### AIHelper
```php
// AI işlemleri
AIHelper::processMessage($message);
AIHelper::formatResponse($response);
AIHelper::analyzeResponse($response);
AIHelper::personalizeResponse($message, $user);
AIHelper::translateToEnglish($text);
AIHelper::makeCasual($text);
AIHelper::logResponse($message, $response, $metadata);
```

### GeneralHelper
```php
// Genel yardımcı fonksiyonlar
GeneralHelper::truncate($text, $length);
GeneralHelper::createSlug($text);
GeneralHelper::generateRandomString($length);
GeneralHelper::formatMoney($amount);
GeneralHelper::formatDate($date);
GeneralHelper::validateEmail($email);
GeneralHelper::getClientIP();
```

### Global Functions
```php
// Global helper fonksiyonları
ai_process($message);
ai_format($response);
ai_analyze($text);
truncate($text, $length);
create_slug($text);
random_string($length);
format_money($amount);
validate_email($email);
client_ip();
```

## 📱 Kullanım Örnekleri

### Landing Page
- Ana sayfa: `/`
- Form elementleri: `/forms`

### Kullanıcı İşlemleri
- Giriş: `/login`
- Kayıt: `/register`
- Şifre sıfırlama: `/forgot-password`

### Dashboard
- Ana dashboard: `/dashboard`
- Profil: `/dashboard/profile`
- Ayarlar: `/dashboard/settings`

### API Endpoints
```bash
# AI yanıt
curl -X POST http://localhost:8000/api/ai/response \
  -H "Content-Type: application/json" \
  -d '{"message": "Merhaba"}'

# İstatistikler
curl http://localhost:8000/api/ai/stats

# Helper test
curl http://localhost:8000/test-helpers
```

## 🚀 Geliştirme

### Yeni Helper Ekleme
1. `app/Helpers/` dizininde yeni helper sınıfı oluşturun
2. `app/Helpers/helpers.php` dosyasına global fonksiyon ekleyin
3. `composer.json` autoload ayarlarını kontrol edin
4. `composer dump-autoload` çalıştırın

### Yeni Sayfa Ekleme
1. Controller oluşturun
2. Route tanımlayın
3. View dosyası oluşturun
4. Dashboard layout'unu kullanın

### Styling
- Tailwind CSS utility sınıfları kullanın
- Glass effect için `.glass-effect` sınıfını kullanın
- Gradient text için `.gradient-text` sınıfını kullanın
- Responsive tasarım için Tailwind breakpoint'lerini kullanın

## 📝 Notlar

- Tüm form elementleri `/forms` sayfasında mevcuttur
- Landing page tasarımı tüm sayfalarda tutarlıdır
- Helper sistemi genişletilebilir yapıdadır
- Admin kullanıcılar `is_admin` alanı ile belirlenir
- Şifre güvenliği Laravel'in varsayılan kurallarını kullanır

## 🤝 Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit yapın (`git commit -m 'Add amazing feature'`)
4. Push yapın (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun

## 📄 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## 📞 İletişim

- **Proje**: ConvStateAI
- **Versiyon**: 1.0.0
- **Son Güncelleme**: 2024

---

**ConvStateAI** - Geleceğin AI çözümleri 🚀
# ConvStateAI
