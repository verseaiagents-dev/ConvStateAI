# 🧪 Field Mapping System Demo Script

## 📋 **Test Senaryoları**

### **1. Field Detection Test**
```bash
# 1. Knowledge Base oluştur
curl -X POST http://localhost:8001/api/knowledge-base/upload \
  -F "file=@test_products.csv" \
  -F "name=Test Products" \
  -F "description=Test data for field mapping"

# 2. Field detection çalıştır
curl -X GET http://localhost:8001/api/knowledge-base/{KB_ID}/detect-fields
```

**Beklenen Sonuç:**
- CSV headers tespit edilmeli
- Field types otomatik belirlenmeli
- Smart mapping önerileri gelmeli

### **2. Template System Test**
```bash
# 1. E-commerce template uygula
# UI'da "Template Kullan" butonuna tıkla
# E-commerce template'ini seç ve uygula

# 2. Template önizlemesi kontrol et
# Template preview modal'ında mapping'leri gör
```

**Beklenen Sonuç:**
- Template seçenekleri görünmeli
- E-commerce template uygulanmalı
- Field mapping'ler otomatik doldurulmalı

### **3. Transformation Rules Test**
```bash
# 1. Field mapping düzenle
# Herhangi bir field'ı düzenle
# Currency conversion ekle (USD → TRY, rate: 30.5)
# Date format conversion ekle (YYYY-MM-DD → DD/MM/YYYY)
# Text processing ekle (uppercase, trim)

# 2. Veri önizle
# "Veri Önizle" butonuna tıkla
```

**Beklenen Sonuç:**
- Price: 999 USD → 30,484.5 TRY
- Date: 2024-09-15 → 15/09/2024
- Text: "iPhone 15 Pro" → "IPHONE 15 PRO"

### **4. Validation Rules Test**
```bash
# 1. Validation rules ekle
# "Validation Rules" butonuna tıkla
# Field seç ve validation kuralları ekle:
# - product_title: required, min_length: 3
# - price_usd: required, min_value: 0
# - email: email format validation
# - website: URL format validation

# 2. Validation test çalıştır
# "Validation Test" butonuna tıkla
```

**Beklenen Sonuç:**
- Valid data: ✅ All data is valid!
- Invalid data: ❌ Validation errors found
- Detailed error messages gösterilmeli

### **5. Batch Processing Test**
```bash
# 1. Batch processing modal'ını aç
# "Batch Processing" butonuna tıkla

# 2. İşlemi başlat
# "İşlemi Başlat" butonuna tıkla
```

**Beklenen Sonuç:**
- Progress bar çalışmalı
- İstatistikler güncellenmeli
- Processing log'u görünmeli
- Pause/Resume çalışmalı

### **6. API Endpoint Test**
```bash
# 1. Field mappings kaydet
curl -X POST http://localhost:8001/api/knowledge-base/{KB_ID}/save-mappings \
  -H "Content-Type: application/json" \
  -d '{
    "mappings": [
      {
        "source_field": "product_title",
        "target_field": "product_name",
        "field_type": "text",
        "is_required": true
      }
    ]
  }'

# 2. Mapping statistics al
curl -X GET http://localhost:8001/api/knowledge-base/{KB_ID}/mapping-stats

# 3. Data validation çalıştır
curl -X POST http://localhost:8001/api/knowledge-base/{KB_ID}/validate-data \
  -H "Content-Type: application/json" \
  -d '{
    "mappings": [...],
    "data": [...]
  }'

# 4. Batch processing çalıştır
curl -X POST http://localhost:8001/api/knowledge-base/{KB_ID}/process-batch \
  -H "Content-Type: application/json" \
  -d '{
    "mappings": [...],
    "chunk_size": 100
  }'

# 5. Data export
curl -X POST http://localhost:8001/api/knowledge-base/{KB_ID}/export-data \
  -H "Content-Type: application/json" \
  -d '{
    "mappings": [...],
    "format": "csv"
  }'
```

## 🎯 **Test Checklist**

### **Frontend UI Test**
- [ ] Field Detection Modal açılıyor
- [ ] Template System çalışıyor
- [ ] Transformation Rules form'ları görünüyor
- [ ] Validation Rules modal'ı açılıyor
- [ ] Batch Processing progress gösteriliyor
- [ ] Error handling çalışıyor

### **Backend API Test**
- [ ] Field detection endpoint çalışıyor
- [ ] Mapping save endpoint çalışıyor
- [ ] Data validation endpoint çalışıyor
- [ ] Batch processing endpoint çalışıyor
- [ ] Export endpoint çalışıyor
- [ ] Error responses doğru format'ta

### **Data Processing Test**
- [ ] CSV parsing çalışıyor
- [ ] Field type detection doğru
- [ ] Transformation rules uygulanıyor
- [ ] Validation rules çalışıyor
- [ ] Batch processing chunk'lar halinde çalışıyor

## 🚀 **Demo Flow**

1. **Setup**: Test CSV dosyasını yükle
2. **Detection**: Field detection çalıştır
3. **Template**: E-commerce template uygula
4. **Customization**: Transformation rules ekle
5. **Validation**: Validation rules ekle ve test et
6. **Processing**: Batch processing çalıştır
7. **Export**: Transformed data export et

## 📊 **Expected Results**

- **Field Detection**: 7 fields detected (product_title, price_usd, etc.)
- **Template Mapping**: 7 fields mapped to standard fields
- **Transformation**: Price converted, dates reformatted, text processed
- **Validation**: Required fields validated, format checks passed
- **Processing**: 1000 rows processed in 10 chunks
- **Export**: CSV file with transformed data
