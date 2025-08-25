# 3. Backend Mimari Geliştirme Planı

## 3.1 Mevcut Durum Analizi

### 3.1.1 TestAPI.php'deki Mevcut Yapı
- Monolithic controller yapısı
- ChatSession class (JSON file-based storage)
- SmartProductRecommender class
- IntentDetectionService integration
- Basic API endpoints

### 3.1.2 Geliştirilecek Alanlar
- Katmanlı mimari (Layered Architecture)
- Service layer implementation
- Repository pattern
- Database migration improvements
- Event tracking system
- CRM integration

## 3.2 Yeni Mimari Yapısı

### 3.2.1 Katmanlı Mimari (Layered Architecture)

```
app/
├── Http/
│   ├── Controllers/          # Request/Response handling
│   ├── Middleware/           # Authentication, validation
│   └── Requests/             # Form request validation
├── Services/                 # Business logic layer
├── Repositories/             # Data access layer
├── Models/                   # Eloquent models
├── Integrations/             # External service integrations
├── Events/                   # Event classes
├── Listeners/                # Event listeners
├── Jobs/                     # Queue jobs
└── Providers/                # Service providers
```

### 3.2.2 Service Layer Implementation

#### 3.2.2.1 ChatService
```php
<?php

namespace App\Services;

use App\Repositories\ChatRepository;
use App\Services\IntentDetectionService;
use App\Services\AIResponseService;
use App\Events\ChatMessageReceived;

class ChatService
{
    public function __construct(
        private ChatRepository $chatRepository,
        private IntentDetectionService $intentService,
        private AIResponseService $aiService
    ) {}

    public function processMessage(string $message, string $sessionId): array
    {
        // 1. Intent detection
        $intent = $this->intentService->detectIntent($message);
        
        // 2. AI response generation
        $response = $this->aiService->generateResponse($message, $intent, $sessionId);
        
        // 3. Save to database
        $this->chatRepository->saveMessage($sessionId, 'user', $message, $intent);
        $this->chatRepository->saveMessage($sessionId, 'bot', $response['message'], $intent);
        
        // 4. Fire event
        event(new ChatMessageReceived($sessionId, $message, $intent));
        
        return $response;
    }
}
```

#### 3.2.2.2 EventService
```php
<?php

namespace App\Services;

use App\Repositories\EventRepository;
use App\Services\RuleEngineService;
use App\Events\UserEventTracked;

class EventService
{
    public function trackEvent(string $sessionId, string $eventType, array $payload): void
    {
        // 1. Save event
        $event = $this->eventRepository->create([
            'session_id' => $sessionId,
            'event_type' => $eventType,
            'payload' => $payload,
            'timestamp' => now()
        ]);
        
        // 2. Check rules
        $rules = $this->ruleEngine->evaluateRules($eventType, $payload);
        
        // 3. Execute actions
        foreach ($rules as $rule) {
            $this->executeRule($rule, $event);
        }
        
        // 4. Fire event
        event(new UserEventTracked($event));
    }
}
```

#### 3.2.2.3 ConfigService
```php
<?php

namespace App\Services;

use App\Repositories\WidgetConfigRepository;
use App\Services\CacheService;

class ConfigService
{
    public function getWidgetConfig(string $siteId): array
    {
        // 1. Check cache
        $cached = $this->cacheService->get("widget_config:{$siteId}");
        if ($cached) {
            return $cached;
        }
        
        // 2. Get from database
        $config = $this->widgetConfigRepository->findBySiteId($siteId);
        
        // 3. Cache result
        $this->cacheService->put("widget_config:{$siteId}", $config, 3600);
        
        return $config;
    }
}
```

### 3.2.3 Repository Pattern Implementation

#### 3.2.3.1 ChatRepository
```php
<?php

namespace App\Repositories;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Support\Collection;

class ChatRepository
{
    public function createSession(string $siteId, ?string $userId = null): ChatSession
    {
        return ChatSession::create([
            'site_id' => $siteId,
            'user_id' => $userId,
            'session_id' => $this->generateSessionId(),
            'started_at' => now(),
            'status' => 'active'
        ]);
    }
    
    public function saveMessage(string $sessionId, string $role, string $content, ?array $intent = null): ChatMessage
    {
        return ChatMessage::create([
            'session_id' => $sessionId,
            'sender' => $role,
            'message' => $content,
            'intent' => $intent ? json_encode($intent) : null,
            'created_at' => now()
        ]);
    }
    
    public function getSessionMessages(string $sessionId, int $limit = 50): Collection
    {
        return ChatMessage::where('session_id', $sessionId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
```

#### 3.2.3.2 EventRepository
```php
<?php

namespace App\Repositories;

use App\Models\EventLog;
use Illuminate\Support\Collection;

class EventRepository
{
    public function create(array $data): EventLog
    {
        return EventLog::create($data);
    }
    
    public function getEventsByType(string $sessionId, string $eventType): Collection
    {
        return EventLog::where('session_id', $sessionId)
            ->where('event_type', $eventType)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    public function getAbandonedCheckouts(string $siteId, int $hours = 24): Collection
    {
        return EventLog::where('site_id', $siteId)
            ->where('event_type', 'checkout_started')
            ->where('created_at', '>=', now()->subHours($hours))
            ->whereDoesntHave('relatedEvents', function($query) {
                $query->where('event_type', 'checkout_completed');
            })
            ->get();
    }
}
```

### 3.2.4 Database Migrations

#### 3.2.4.1 Widget Configs Table
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_configs', function (Blueprint $table) {
            $table->id();
            $table->string('site_id')->unique();
            $table->json('colors');
            $table->string('logo_url')->nullable();
            $table->string('font_family')->default('Inter');
            $table->text('welcome_message');
            $table->json('templates');
            $table->json('features');
            $table->json('styling');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('site_id');
        });
    }
};
```

#### 3.2.4.2 Event Rules Table
```php
<?php

Schema::create('event_rules', function (Blueprint $table) {
    $table->id();
    $table->string('site_id');
    $table->string('event_type');
    $table->json('trigger_conditions');
    $table->string('response_template_id');
    $table->json('actions');
    $table->boolean('is_active')->default(true);
    $table->integer('priority')->default(0);
    $table->timestamps();
    
    $table->index(['site_id', 'event_type']);
});
```

#### 3.2.4.3 Feedback Table
```php
<?php

Schema::create('feedback', function (Blueprint $table) {
    $table->id();
    $table->string('session_id');
    $table->unsignedBigInteger('message_id');
    $table->boolean('is_helpful');
    $table->text('comment')->nullable();
    $table->timestamps();
    
    $table->foreign('message_id')->references('id')->on('chat_messages');
    $table->index('session_id');
});
```

### 3.2.5 Event System

#### 3.2.5.1 Event Classes
```php
<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageReceived
{
    use Dispatchable, SerializesModels;
    
    public function __construct(
        public string $sessionId,
        public string $message,
        public array $intent
    ) {}
}

class UserEventTracked
{
    use Dispatchable, SerializesModels;
    
    public function __construct(
        public array $event
    ) {}
}

class CheckoutAbandoned
{
    use Dispatchable, SerializesModels;
    
    public function __construct(
        public string $sessionId,
        public array $products,
        public float $totalAmount
    ) {}
}
```

#### 3.2.5.2 Event Listeners
```php
<?php
<?php

namespace App\Listeners;

use App\Events\ChatMessageReceived;
use App\Services\AnalyticsService;
use App\Services\NotificationService;

class ProcessChatMessage
{
    public function handle(ChatMessageReceived $event): void
    {
        // 1. Analytics tracking
        $this->analyticsService->trackMessage($event->sessionId, $event->intent);
        
        // 2. Send notifications if needed
        if ($this->shouldNotify($event->intent)) {
            $this->notificationService->notifyTeam($event);
        }
    }
}
```

### 3.2.6 CRM Integration

#### 3.2.6.1 CRMService
```php
<?php

namespace App\Services;

use App\Repositories\LeadRepository;
use App\Integrations\MailchimpService;
use App\Integrations\HubSpotService;

class CRMService
{
    public function createLead(array $data): array
    {
        // 1. Save lead locally
        $lead = $this->leadRepository->create($data);
        
        // 2. Generate coupon code
        $couponCode = $this->generateCouponCode();
        $lead->update(['coupon_code' => $couponCode]);
        
        // 3. Sync to external CRM
        $this->syncToExternalCRM($lead);
        
        // 4. Send welcome email
        $this->sendWelcomeEmail($lead);
        
        return $lead->toArray();
    }
    
    private function syncToExternalCRM($lead): void
    {
        // Mailchimp integration
        $this->mailchimpService->addSubscriber($lead);
        
        // HubSpot integration
        $this->hubSpotService->createContact($lead);
    }
}
```

### 3.2.7 API Endpoints Restructuring

#### 3.2.7.1 Widget Controller
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ConfigService;
use App\Services\ChatService;
use App\Services\EventService;
use App\Http\Requests\ChatRequest;
use App\Http\Requests\EventRequest;

class WidgetController extends Controller
{
    public function getConfig(string $siteId)
    {
        $config = $this->configService->getWidgetConfig($siteId);
        return response()->json($config);
    }
    
    public function sendMessage(ChatRequest $request)
    {
        $response = $this->chatService->processMessage(
            $request->message,
            $request->session_id
        );
        
        return response()->json($response);
    }
    
    public function trackEvent(EventRequest $request)
    {
        $this->eventService->trackEvent(
            $request->session_id,
            $request->event_type,
            $request->payload
        );
        
        return response()->json(['success' => true]);
    }
}
```

## 3.3 Geliştirme Adımları

### 3.3.1 Phase 1: Service Layer (1-2 hafta)
1. Service classes oluştur
2. Business logic'i controller'lardan taşı
3. Dependency injection kur

### 3.3.2 Phase 2: Repository Pattern (1 hafta)
1. Repository classes oluştur
2. Database access logic'i taşı
3. Model relationships kur

### 3.3.3 Phase 3: Event System (1 hafta)
1. Event classes oluştur
2. Event listeners implement et
3. Event dispatching kur

### 3.3.4 Phase 4: Database Improvements (1 hafta)
1. New migrations oluştur
2. Existing tables optimize et
3. Indexes ekle

### 3.3.5 Phase 5: CRM Integration (1-2 hafta)
1. External service integrations
2. Lead management system
3. Email automation

### 3.3.6 Phase 6: Testing & Documentation (1 hafta)
1. Unit tests yaz
2. Integration tests yaz
3. API documentation

## 3.4 Sonraki Adımlar

### 3.4.1 Backend Refactoring
1. **Service layer implementation başla**
2. **Repository pattern kur**
3. **Event system implement et**

### 3.4.2 Database & Integration
4. **Database migrations oluştur**
5. **CRM integration ekle**
6. **Testing implement et**
7. **API documentation hazırla**
