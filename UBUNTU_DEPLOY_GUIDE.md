# 🚀 Ubuntu Canlı Sunucuda Laravel Projesi Deploy Rehberi

Bu rehber, Laravel projenizi Ubuntu canlı sunucuda deploy etmek ve subscription middleware'ini aktif hale getirmek için gerekli tüm adımları içerir.

## 📋 Ön Gereksinimler

- Ubuntu Server (18.04+ önerilir)
- PHP 8.1+ kurulu
- Composer kurulu
- Nginx/Apache kurulu
- MySQL/PostgreSQL kurulu
- Git kurulu (opsiyonel)

## 🔧 Deploy Adımları

### 1. Sunucuya Bağlanma
```bash
ssh username@your-server-ip
```

### 2. Proje Dizinine Gitme
```bash
cd /var/www/your-project-name
```

### 3. Git Pull (Eğer Git Kullanıyorsanız)
```bash
git pull origin main
# veya
git pull origin master
```

### 4. Composer Dependencies Kurulumu
```bash
composer install --optimize-autoloader --no-dev
```

### 5. Environment File Ayarları
```bash
# Environment file kopyalama
cp .env.example .env

# Environment file'ı düzenleme
nano .env
```

#### .env Dosyasında Olması Gerekenler:
```env
APP_NAME="Your App Name"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### 6. Application Key Oluşturma
```bash
php artisan key:generate
```

### 7. Cache Temizleme
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### 8. Production Cache Oluşturma
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 9. File Permissions Ayarlama
```bash
# Storage dizini permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Web server user ownership
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/
chown -R www-data:www-data public/
```

### 10. Database Migration ve Seeding
```bash
# Migration çalıştırma
php artisan migrate --force

# Seeding (eğer gerekirse)
php artisan db:seed --force
```

### 11. Queue Restart (Eğer Queue Kullanıyorsanız)
```bash
php artisan queue:restart
```

### 12. Optimize
```bash
php artisan optimize
```

## 📁 Tam Deploy Script

Aşağıdaki script'i `deploy.sh` olarak kaydedip çalıştırabilirsiniz:

```bash
#!/bin/bash

echo "🚀 Laravel Projesi Deploy Ediliyor..."

# 1. Proje dizinine git
cd /var/www/your-project-name

# 2. Git pull
echo "📥 Git pull yapılıyor..."
git pull origin main

# 3. Composer dependencies
echo "📦 Composer dependencies kuruluyor..."
composer install --optimize-autoloader --no-dev

# 4. Environment file
echo "⚙️ Environment file kontrol ediliyor..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "⚠️ .env dosyası oluşturuldu. Lütfen manuel olarak düzenleyin!"
fi

# 5. Application key
echo "🔑 Application key oluşturuluyor..."
php artisan key:generate

# 6. Cache temizleme
echo "🧹 Cache temizleniyor..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 7. Production cache oluşturma
echo "💾 Production cache oluşturuluyor..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Permissions
echo "🔐 Permissions ayarlanıyor..."
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/
chown -R www-data:www-data public/

# 9. Queue restart
echo "🔄 Queue restart yapılıyor..."
php artisan queue:restart

# 10. Optimize
echo "⚡ Optimize yapılıyor..."
php artisan optimize

echo "✅ Deploy tamamlandı!"
```

### Script'i Çalıştırma:
```bash
chmod +x deploy.sh
./deploy.sh
```

## 🔍 Deploy Sonrası Test

### 1. Route'ları Kontrol Etme
```bash
# Dashboard route'larını kontrol et
php artisan route:list --name=dashboard

# Subscription expired route'unu kontrol et
php artisan route:list --name=subscription.expired

# Subscription middleware'ini kontrol et
php artisan route:list --middleware=subscription
```

### 2. Log'ları Kontrol Etme
```bash
# Laravel log'larını takip et
tail -f storage/logs/laravel.log

# Nginx error log'larını kontrol et
tail -f /var/log/nginx/error.log

# Apache error log'larını kontrol et (eğer Apache kullanıyorsanız)
tail -f /var/log/apache2/error.log
```

### 3. Web Server Test
```bash
# Nginx config test
nginx -t

# Nginx restart
sudo systemctl restart nginx

# Apache config test (eğer Apache kullanıyorsanız)
apache2ctl configtest

# Apache restart
sudo systemctl restart apache2
```

## ⚠️ Önemli Güvenlik Notları

### 1. Environment Variables
- `.env` dosyası public dizinde olmamalı
- Database credentials güvenli olmalı
- `APP_DEBUG=false` production'da olmalı

### 2. File Permissions
- Storage dizini web server tarafından yazılabilir olmalı
- Bootstrap cache dizini yazılabilir olmalı
- Public dizini sadece gerekli dosyaları içermeli

### 3. SSL Certificate
- HTTPS kullanımı zorunlu
- SSL certificate güncel olmalı
- HTTP → HTTPS redirect aktif olmalı

## 🐛 Sorun Giderme

### Subscription Middleware Çalışmıyor

#### Olası Nedenler:
1. **Cache Sorunu:** Route cache'de eski route'lar kalmış
2. **Config Cache:** Eski config ayarları cache'de kalmış
3. **File Permissions:** Middleware dosyalarına erişim sorunu
4. **Composer Autoload:** Yeni eklenen middleware'ler autoload edilmemiş

#### Çözümler:
```bash
# Cache temizleme
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Composer autoload yenileme
composer dump-autoload

# Route cache yeniden oluşturma
php artisan route:cache
```

### Database Bağlantı Sorunu
```bash
# Database bağlantısını test et
php artisan tinker
# Tinker'da: DB::connection()->getPdo();

# Migration durumunu kontrol et
php artisan migrate:status
```

### File Permission Sorunu
```bash
# Permissions'ları düzelt
sudo chown -R www-data:www-data /var/www/your-project-name
sudo chmod -R 755 /var/www/your-project-name
sudo chmod -R 775 /var/www/your-project-name/storage
sudo chmod -R 775 /var/www/your-project-name/bootstrap/cache
```

## 📞 Destek

Eğer deploy sırasında sorun yaşarsanız:

1. **Log dosyalarını kontrol edin**
2. **File permissions'ları kontrol edin**
3. **Web server config'ini kontrol edin**
4. **Database bağlantısını test edin**

## 🎯 Sonuç

Bu rehberi takip ederek Laravel projenizi Ubuntu canlı sunucuda başarıyla deploy edebilir ve subscription middleware'ini aktif hale getirebilirsiniz. Deploy sonrası tüm özellikler çalışır durumda olacaktır.

---

**Not:** Bu rehber production environment için hazırlanmıştır. Development environment'da farklı ayarlar kullanılabilir.
