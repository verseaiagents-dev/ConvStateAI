# API Örnek Dosyaları

Bu klasör, React widget'ta kullanılacak API endpoint'leri için örnek JSON response dosyalarını içerir.

## 📁 Dosya Yapısı

### Sipariş Durumu API
- **`order-status-example.json`** - Detaylı sipariş durumu örneği (tam özellikler)
- **`order-status-simple.json`** - Widget UI için basitleştirilmiş örnek

### Kargo Takip API
- **`cargo-tracking-example.json`** - Detaylı kargo takip örneği (tam özellikler)
- **`cargo-tracking-simple.json`** - Widget UI için basitleştirilmiş örnek

## 🎯 Kullanım Amacı

Bu dosyalar şunlar için kullanılır:

1. **Geliştirici Referansı** - API'nizin nasıl yanıt vermesi gerektiğini gösterir
2. **Widget UI Tasarımı** - React widget'ta hangi verilerin gösterileceğini belirler
3. **API Test** - Kendi API'nizi test ederken beklenen formatı gösterir
4. **Dokümantasyon** - API response yapısını açıklar

## 🔧 API Gereksinimleri

### Genel Gereksinimler
- **HTTP Status Code:** 200 (OK)
- **Content-Type:** application/json
- **Response Format:** JSON
- **Encoding:** UTF-8

### Response Yapısı
```json
{
  "success": true,
  "data": { ... },
  "message": "Açıklama metni"
}
```

## 📱 Widget UI Entegrasyonu

React widget'ta bu veriler şu şekilde kullanılır:

### Sipariş Durumu
- Kullanıcı: "sipariş durumum nedir"
- Widget: Bu API'yi çağırır
- Sonuç: Sipariş bilgileri gösterilir

### Kargo Takip
- Kullanıcı: "kargom nerede"
- Widget: Bu API'yi çağırır
- Sonuç: Kargo durumu gösterilir

## 🚀 Özelleştirme

Bu örnek dosyaları kendi ihtiyaçlarınıza göre özelleştirebilirsiniz:

1. **Alan Ekleme/Çıkarma** - Gereksinimlerinize göre
2. **Veri Formatı** - Tarih, para birimi, dil vb.
3. **Ek Bilgiler** - Müşteri notları, özel alanlar vb.

## 📞 Destek

API entegrasyonu ile ilgili sorularınız için:
- **Email:** destek@example.com
- **Dokümantasyon:** https://docs.example.com
- **API Test:** Dashboard > API Ayarları sayfası
