# ConvState AI Mail Sistemi Kurulum Rehberi

Bu rehber, ConvState AI projesinde Gmail SMTP ile mail sistemi kurulumunu açıklar.

## 📧 Gmail SMTP Kurulumu

### 1. Gmail App Password Oluşturma

1. [Google Account Settings](https://myaccount.google.com/) sayfasına gidin
2. **Security** sekmesine tıklayın
3. **2-Step Verification** aktif olmalı
4. **App passwords** bölümüne gidin
5. **Select app** dropdown'dan "Mail" seçin
6. **Generate** butonuna tıklayın
7. 16 karakterlik şifreyi kopyalayın (örn: `abcd efgh ijkl mnop`)

### 2. .env Dosyası Ayarları

Proje kök dizininde `.env` dosyası oluşturun ve aşağıdaki mail ayarlarını ekleyin:

```env
# Mail Ayarları - Gmail SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreplyconvstateai@gmail.com
MAIL_PASSWORD="iuvy hsjc tzpi bmqg"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreplyconvstateai@gmail.com
MAIL_FROM_NAME="ConvState AI"
```

**Önemli:** `MAIL_PASSWORD` alanına Gmail App Password'ü yazın, normal Gmail şifrenizi değil!

### 3. Gmail Güvenlik Ayarları

- **Less secure app access** kapalı olmalı
- **2-Step Verification** aktif olmalı
- **App passwords** kullanılmalı

## 🚀 Mail Sistemi Test Etme

### Komut Satırından Test

```bash
# Test maili gönder
php artisan mail:test your-email@example.com

# Mail durumunu kontrol et
php artisan tinker
>>> Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });
```

### Tarayıcıdan Test

1. Kullanıcı kaydı yapın
2. Hoşgeldin maili otomatik gönderilecek
3. Mail klasörünüzü kontrol edin

## 📋 Oluşturulan Mail Template'leri

### 1. Hoşgeldin Maili (`WelcomeEmail`)
- **Dosya:** `app/Mail/WelcomeEmail.php`
- **Template:** `resources/views/emails/welcome.blade.php`
- **Kullanım:** Kullanıcı kaydı sonrası otomatik gönderim

### 2. Şifre Sıfırlama Maili (`PasswordResetEmail`)
- **Dosya:** `app/Mail/PasswordResetEmail.php`
- **Template:** `resources/views/emails/password-reset.blade.php`
- **Kullanım:** Şifre sıfırlama talebi sonrası

### 3. Abonelik Hoşgeldin Maili (`SubscriptionWelcomeEmail`)
- **Dosya:** `app/Mail/SubscriptionWelcomeEmail.php`
- **Template:** `resources/views/emails/subscription-welcome.blade.php`
- **Kullanım:** Premium abonelik başlangıcında

### 4. Hesap Doğrulama Maili (`AccountVerificationEmail`)
- **Dosya:** `app/Mail/AccountVerificationEmail.php`
- **Template:** `resources/views/emails/account-verification.blade.php`
- **Kullanım:** E-posta doğrulama işleminde

## 🔧 Mail Service Kullanımı

### Service Sınıfı

```php
use App\Services\MailService;

class YourController extends Controller
{
    protected $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function sendWelcomeEmail(User $user)
    {
        return $this->mailService->sendWelcomeEmail($user);
    }
}
```

### Manuel Mail Gönderimi

```php
// Hoşgeldin maili
Mail::to($user->email)->send(new WelcomeEmail($user));

// Şifre sıfırlama
Mail::to($email)->send(new PasswordResetEmail($resetUrl, $userName));

// Abonelik hoşgeldin
Mail::to($user->email)->send(new SubscriptionWelcomeEmail($user, $subscription));
```

## 🎨 Mail Template Özelleştirme

### CSS Stilleri
- Responsive tasarım
- Modern gradient butonlar
- Profesyonel renk paleti
- Mobil uyumlu layout

### Değişkenler
- `{{ $user->name }}` - Kullanıcı adı
- `{{ $user->email }}` - E-posta adresi
- `{{ $loginUrl }}` - Giriş linki
- `{{ $resetUrl }}` - Şifre sıfırlama linki
- `{{ $verificationUrl }}` - Doğrulama linki

## 📱 Mobil Uyumluluk

Tüm mail template'leri:
- Responsive tasarım
- Mobil cihazlarda optimize edilmiş
- Farklı mail client'larda uyumlu
- Modern CSS özellikleri

## 🚨 Hata Giderme

### Yaygın Hatalar

1. **Authentication failed**
   - Gmail App Password doğru mu?
   - 2-Step Verification aktif mi?

2. **Connection refused**
   - Port 587 açık mı?
   - Firewall ayarları kontrol edildi mi?

3. **Mail gönderilmiyor**
   - .env dosyası doğru mu?
   - Cache temizlendi mi?

### Cache Temizleme

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 🔒 Güvenlik Notları

- Gmail App Password'ü güvenli tutun
- .env dosyasını git'e commit etmeyin
- Production'da rate limiting kullanın
- Mail loglarını düzenli kontrol edin

## 📊 Mail İstatistikleri

MailService sınıfı otomatik olarak:
- Başarılı gönderimleri loglar
- Hataları kaydeder
- Performans metrikleri tutar

## 🆘 Destek

Herhangi bir sorun yaşarsanız:
1. Laravel log dosyalarını kontrol edin
2. Mail test komutunu çalıştırın
3. Gmail ayarlarını doğrulayın
4. Destek ekibiyle iletişime geçin

---

**Not:** Bu rehber Laravel 10+ ve PHP 8.1+ için yazılmıştır.
