Sen bir full-stack SaaS mimarı ve yazılımcısın. Aşağıdaki context’e göre bana hem Widget (React + TypeScript) hem de Laravel API için katmanlı ve ölçeklenebilir bir yapı kuracaksın. Kodları production-ready, temiz ve modüler olacak.

# Genel Mimari
- Widget (React + TypeScript) → sadece UI + event yakalama + API istekleri
- Laravel API → iş mantığı, session yönetimi, CRM, AI entegrasyonu
- AI Service → intent analizi, response üretimi (Laravel service olarak OpenAI API çağrısı)

---

# Widget (Frontend) Gereksinimleri
- Framework: React + TypeScript
- State management: Redux Toolkit veya Zustand
- Data fetching: react-query
- UI:inline style
- Widget config JSON ile çalışmalı → her site kendi renk, logo, yazı tipi, welcome mesajı özelleştirebilsin
- embed.js ile siteye kolayca eklenebilecek şekilde build edilecek
- Event tabanlı mimari: (sepete ekleme, kategori gezme, checkout terk etme)
- API entegrasyonları (REST veya GraphQL):
  - GET /api/widget/config → config JSON al
  - POST /api/chat/send → mesaj gönder
  - POST /api/event/track → kullanıcı davranış event gönder
  - POST /api/feedback → yararlı/yararsız feedback gönder
  - POST /api/leads/subscribe → CRM lead kaydı
- Widget tarafında templates olacak:
  - CatalogTemplate (ürün önerisi)
  - CheckoutTemplate (sepete bırakılan ürün hatırlatma)
  - WheelSpinTemplate (gamification indirim çarkı)
- Kullanıcı ilk giriş → session yoksa welcome balonu + özelleştirilebilir mesaj
- Kullanıcı tıklama, sepet, checkout eventleri → Laravel API’ye kaydolmalı
- Feedback mekanizması: her response’un sonunda “Yararlı / Yararsız” butonu
- Response içinde `template` parametresi olacak → React doğru template render edecek
Yapılacak Tasarım kiti olarak /Users/kadirburakdurmazlar/cursor apps/TESTAI/ui.1/ içinde yer alan index.html içindeki .chat-container div içindeki tasarımı script.js ve style.css kullanarak  yapmalısın
---

# Laravel API (Backend) Gereksinimleri
- Framework: Laravel 10
- Katmanlı mimari:
  - Controllers → sadece request/response
  - Services → iş mantığı
  - Repositories → DB erişimi
  - Integrations → OpenAI, CRM, Mailchimp vb.
- Endpointler:
  - GET /api/widget/config (Widget config JSON)
  - POST /api/chat/send (Chat mesajı gönder, AI çağır, intent belirle, template seç, response döndür)
  - POST /api/event/track (Sepet bırakma, ödeme terk etme, gezinti eventleri işlenir)
  - POST /api/feedback (feedback kaydı)
  - POST /api/leads/subscribe (lead kaydı, CRM entegrasyonu)
- Database tabloları:
  - sessions (id, site_id, session_id, user_id, started_at, status)
  - session_messages (id, session_id, sender, message, intent, template, created_at)
  - session_events (id, session_id, event_type, payload, created_at)
  - widget_configs (id, site_id, colors JSON, logo_url, font, welcome_message, templates JSON)
  - feedback (id, session_id, message_id, is_helpful, created_at)
  - leads (id, site_id, email, coupon_code, created_at)
  - event_rules (id, site_id, event_type, trigger_condition JSON, response_template_id, is_active)
  - templates (id, name, body JSON)
- Services:
  - ChatService (AI entegrasyonu, intent mapping, response)
  - EventService (event_rules ile eşleştirme, tetikleyici çalıştırma)
  - ConfigService (config JSON üretme)
  - CRMService (lead kaydet, kupon verme, Mailchimp entegrasyonu)
- AI Adapter:
  - Laravel Service class → OpenAI API’ye bağlanır
  - Output: intent, template, message, extra_data JSON

---

# Çalışma Akışları
1. Kullanıcı siteye girer:
   - Widget → GET /api/widget/config
   - Eğer session yoksa → Laravel API yeni session_id döner
   - Welcome balonu gösterilir
2. Kullanıcı tıklar:
   - POST /api/event/track (event=balloon_click)
   - Eğer kupon isterse → POST /api/leads/subscribe (email kaydı, kupon ver)
3. Kullanıcı mesaj gönderir:
   - POST /api/chat/send → Laravel → OpenAI API
   - Response: {intent, template, message, products[]}
   - Widget → doğru template render eder
4. Kullanıcı checkout terk eder:
   - Event gönderilir → EventService → kupon teklif response
5. Feedback:
   - Kullanıcı “yararlı/yararsız” seçer → POST /api/feedback

---

# Beklenen Çıktı
1. React widget proje yapısı (src/components, src/state, src/api, src/templates)
2. Laravel proje yapısı (app/Http/Controllers, app/Services, app/Repositories, app/Integrations, database/migrations)
3. API endpoint örnek kodları
4. AI service entegrasyonu (OpenAI çağrısı için Laravel service class)
5. Widget embed kodu (embed.js)
6. Session + event tracking + feedback işleyişi

Kodları ve yapı planını modern best practice’lere uygun, okunabilir ve modüler yaz.
