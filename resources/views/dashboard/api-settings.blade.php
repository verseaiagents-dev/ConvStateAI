@extends('layouts.dashboard')

@section('title', 'API Ayarları')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white">API Ayarları</h1>
            <p class="text-gray-400 mt-2">API konfigürasyonu ve istatistikleri</p>
        </div>
    </div>

    <!-- API Configuration Section -->
    <div class="glass-effect rounded-xl p-6 border border-gray-700">
        <h2 class="text-2xl font-bold mb-6 text-white">🔌 API Konfigürasyonu</h2>
        <p class="text-gray-400 mb-6">Widget'tan gelen intent'ler için API endpoint'lerini yapılandırın.</p>
        
        <!-- Usage Instructions -->
        <div class="mb-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
            <h3 class="text-blue-400 font-semibold mb-2">📚 Kullanım Talimatları</h3>
            <div class="text-sm text-gray-300 space-y-2">
                <p>• <strong>Sipariş Durumu API:</strong> Kullanıcı "sipariş durumum nedir" dediğinde, widget bu API'yi kullanarak sipariş bilgilerini getirir</p>
                <p>• <strong>Kargo Takip API:</strong> Kullanıcı "kargom nerede" dediğinde, widget bu API'yi kullanarak kargo konumunu getirir</p>
                <p>• API endpoint'leriniz HTTP 200 döndürmeli ve JSON formatında yanıt vermelidir</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Order Status API -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        📦 Sipariş Durumu API
                        <span id="orderStatusStatus" class="ml-2 px-2 py-1 text-xs rounded-full bg-gray-500/20 text-gray-400">Pasif</span>
                    </h3>
                    <div class="relative group">
                        <button type="button" class="text-blue-400 hover:text-blue-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                        <div class="absolute bottom-full right-0 mb-2 w-80 bg-gray-900 border border-gray-700 rounded-lg p-3 shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
                            <h4 class="text-white font-semibold mb-2">📦 Sipariş Durumu API Kullanımı</h4>
                            <div class="text-sm text-gray-300 space-y-2">
                                <p><strong>Widget Intent:</strong> "sipariş durumum nedir", "siparişim nerede"</p>
                                <p><strong>API Metodu:</strong> GET</p>
                                <p><strong>Beklenen Yanıt:</strong> JSON formatında sipariş bilgileri</p>
                                <p><strong>Örnek Response:</strong></p>
                                <pre class="text-xs bg-gray-800 p-2 rounded text-green-400 overflow-x-auto">{
  "order_id": "12345",
  "status": "kargoda",
  "estimated_delivery": "2024-01-15"
}</pre>
                            </div>
                            <div class="absolute top-full right-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <input type="url" id="orderStatusApiUrl" placeholder="https://api.example.com/order-status" 
                           class="flex-1 px-4 py-2 bg-gray-800/50 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20">
                    <button id="testOrderStatusBtn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        Kontrol Et
                    </button>
                </div>
                <p class="text-sm text-gray-400">Sipariş durumu sorgulama için API endpoint</p>
            </div>

            <!-- Cargo Tracking API -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        🚚 Kargo Takip API
                        <span id="cargoTrackingStatus" class="ml-2 px-2 py-1 text-xs rounded-full bg-gray-500/20 text-gray-400">Pasif</span>
                    </h3>
                    <div class="relative group">
                        <button type="button" class="text-blue-400 hover:text-blue-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                        <div class="absolute bottom-full right-0 mb-2 w-80 bg-gray-900 border border-gray-700 rounded-lg p-3 shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
                            <h4 class="text-white font-semibold mb-2">🚚 Kargo Takip API Kullanımı</h4>
                            <div class="text-sm text-gray-300 space-y-2">
                                <p><strong>Widget Intent:</strong> "kargom nerede", "kargo durumu"</p>
                                <p><strong>API Metodu:</strong> GET</p>
                                <p><strong>Beklenen Yanıt:</strong> JSON formatında kargo bilgileri</p>
                                <p><strong>Örnek Response:</strong></p>
                                <pre class="text-xs bg-gray-800 p-2 rounded text-green-400 overflow-x-auto">{
  "tracking_number": "TRK123456",
  "status": "dağıtımda",
  "location": "İstanbul",
  "estimated_delivery": "bugün"
}</pre>
                            </div>
                            <div class="absolute top-full right-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <input type="url" id="cargoTrackingApiUrl" placeholder="https://api.example.com/cargo-tracking" 
                           class="flex-1 px-4 py-2 bg-gray-800/50 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20">
                    <button id="testCargoTrackingBtn" class="px-4 py-2 bg-blue-400 hover:bg-blue-500 text-white rounded-lg transition-colors">
                        Kontrol Et
                    </button>
                </div>
                <p class="text-sm text-gray-400">Kargo takip sorgulama için API endpoint</p>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <div class="flex items-center space-x-2 text-sm text-gray-400">
                <span>💡</span>
                <span>API endpoint'leriniz HTTP 200 döndürmeli ve JSON formatında yanıt vermelidir</span>
            </div>
            <button id="saveApiConfigBtn" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                Konfigürasyonu Kaydet
            </button>
        </div>

        <!-- AI Field Mapping Results -->
        <div id="fieldMappingResults" class="mt-6 space-y-4 hidden">
            <!-- Order Status Field Mapping -->
            <div id="orderStatusMapping" class="glass-effect rounded-xl p-6 border border-gray-700 hidden">
                <h3 class="text-lg font-semibold text-white mb-4">📦 Sipariş Durumu API Field Mapping</h3>
                <div id="orderStatusMappingContent"></div>
            </div>

            <!-- Cargo Tracking Field Mapping -->
            <div id="cargoTrackingMapping" class="glass-effect rounded-xl p-6 border border-gray-700 hidden">
                <h3 class="text-lg font-semibold text-white mb-4">🚚 Kargo Takip API Field Mapping</h3>
                <div id="cargoTrackingMappingContent"></div>
            </div>
        </div>
    </div>

 



</div>

<!-- API Test Result Modal -->
<div id="apiTestModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-gray-900 rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold text-white">API Test Sonucu</h3>
            <button onclick="closeApiTestModal()" class="text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div id="apiTestResult" class="space-y-4">
            <!-- Result content will be inserted here -->
        </div>
        
        <div class="mt-6 flex justify-end">
            <button onclick="closeApiTestModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                Kapat
            </button>
        </div>
    </div>
</div>

<!-- AI Field Mapping Modal -->
<div id="aiFieldMappingModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-gray-900 rounded-lg p-6 max-w-2xl w-full mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold text-white">🤖 Yapay Zeka Field Mapping</h3>
        </div>
        
        <div id="aiFieldMappingContent" class="space-y-4">
            <!-- Content will be inserted here -->
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-gray-900 rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-glow"></div>
        <span class="text-white">İstatistikler yükleniyor...</span>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load statistics and API config on page load
    loadStatistics();
    loadApiConfig();
    
    // Refresh statistics every 30 seconds
    setInterval(loadStatistics, 30000);
    
    // Event listener'ları ekle
    setupEventListeners();
    
    function loadStatistics() {
        showLoading();
        
        fetch('/dashboard/api-settings/stats', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatistics(data.data);
            } else {
                console.error('Error loading statistics:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
        })
        .finally(() => {
            hideLoading();
        });
    }
    
    function loadApiConfig() {
        fetch('/dashboard/api-settings/config', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            updateApiConfig(data);
        })
        .catch(error => {
            console.error('Error loading API config:', error);
        });
    }
    
    function updateStatistics(stats) {
        // Update cart statistics
        document.getElementById('totalCarts').textContent = stats.cart.total_carts;
        document.getElementById('activeCarts').textContent = stats.cart.active_carts;
        document.getElementById('abandonedCarts').textContent = stats.cart.abandoned_carts;
        document.getElementById('totalItems').textContent = stats.cart.total_items;
        document.getElementById('avgCartValue').textContent = '$' + parseFloat(stats.cart.average_cart_value).toFixed(2);
        
        // Update order statistics
        document.getElementById('totalOrders').textContent = stats.orders.total_orders;
        document.getElementById('pendingOrders').textContent = stats.orders.pending_orders;
        document.getElementById('completedOrders').textContent = stats.orders.completed_orders;
        document.getElementById('cancelledOrders').textContent = stats.orders.cancelled_orders;
        document.getElementById('totalRevenue').textContent = '$' + parseFloat(stats.orders.total_revenue).toFixed(2);
        
        // Update API calls (mock data)
        document.getElementById('apiCalls').textContent = Math.floor(Math.random() * 1000) + 'k';
    }
    
    function updateApiConfig(config) {
        // Update order status API
        if (config.order_status_api.url) {
            document.getElementById('orderStatusApiUrl').value = config.order_status_api.url;
        }
        if (config.order_status_api.active) {
            updateOrderStatusStatus(true);
        }
        
        // Update cargo tracking API
        if (config.cargo_tracking_api.url) {
            document.getElementById('cargoTrackingApiUrl').value = config.cargo_tracking_api.url;
        }
        if (config.cargo_tracking_api.active) {
            updateCargoTrackingStatus(true);
        }
    }
    
    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }
    
    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }
    
    function setupEventListeners() {
        // Order Status API Kontrol Et butonu
        const orderStatusBtn = document.getElementById('testOrderStatusBtn');
        if (orderStatusBtn) {
            orderStatusBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Order Status butonu tıklandı!');
                testOrderStatusApi();
            });
        }
        
        // Cargo Tracking API Kontrol Et butonu
        const cargoTrackingBtn = document.getElementById('testCargoTrackingBtn');
        if (cargoTrackingBtn) {
            cargoTrackingBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Cargo Tracking butonu tıklandı!');
                testCargoTrackingApi();
            });
        }
        
        // Konfigürasyonu Kaydet butonu
        const saveConfigBtn = document.getElementById('saveApiConfigBtn');
        if (saveConfigBtn) {
            saveConfigBtn.addEventListener('click', function(e) {
                e.preventDefault();
                saveApiConfig();
            });
        }
    }
    
    // Global API Test Functions
    window.testOrderStatusApi = function() {
        const apiUrl = document.getElementById('orderStatusApiUrl').value;
        
        if (!apiUrl) {
            alert('Lütfen API URL girin');
            return;
        }
        
        // CSRF token'ı kontrol et
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        
        if (!csrfToken) {
            alert('CSRF token bulunamadı. Sayfayı yenileyin.');
            return;
        }
        
        const tokenValue = csrfToken.getAttribute('content');
        
        showApiTestModal('Sipariş Durumu API test ediliyor...');
        
        fetch('/dashboard/api-settings/test-order-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': tokenValue,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ api_url: apiUrl })
        })
        .then(response => response.json())
        .then(data => {
            showApiTestResult(data, 'orderStatus');
        })
        .catch(error => {
            showApiTestResult({ success: false, error: error.message }, 'orderStatus');
        });
    };

    window.testCargoTrackingApi = function() {
        const apiUrl = document.getElementById('cargoTrackingApiUrl').value;
        
        if (!apiUrl) {
            alert('Lütfen API URL girin');
            return;
        }
        
        // CSRF token'ı kontrol et
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        
        if (!csrfToken) {
            alert('CSRF token bulunamadı. Sayfayı yenileyin.');
            return;
        }
        
        const tokenValue = csrfToken.getAttribute('content');
        
        showApiTestModal('Kargo Takip API test ediliyor...');
        
        fetch('/dashboard/api-settings/test-cargo-tracking', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': tokenValue,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ api_url: apiUrl })
        })
        .then(response => response.json())
        .then(data => {
            showApiTestResult(data, 'cargoTracking');
        })
        .catch(error => {
            showApiTestResult({ success: false, error: error.message }, 'cargoTracking');
        });
    };

    window.showApiTestModal = function(message) {
        document.getElementById('apiTestResult').innerHTML = `
            <div class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-glow"></div>
                <span class="ml-3 text-white">${message}</span>
            </div>
        `;
        document.getElementById('apiTestModal').classList.remove('hidden');
    };

    function showApiTestResult(data, type) {
        let resultHtml = '';
        
        if (data.success) {
            resultHtml = `
                <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-lg">
                    <h4 class="text-green-400 font-semibold mb-2">✅ API Test Başarılı!</h4>
                    <div class="space-y-2 text-sm">
                        <div><span class="text-gray-400">Status Code:</span> <span class="text-white">${data.status_code}</span></div>
                        ${data.response_time ? `<div><span class="text-gray-400">Yanıt Süresi:</span> <span class="text-white">${(data.response_time * 1000).toFixed(2)}ms</span></div>` : ''}
                    </div>
                    <div class="mt-3 p-3 bg-green-500/20 rounded-lg">
                        <p class="text-green-300 text-sm">🎉 Bu API artık React widget'ta kullanılabilir!</p>
                        <p class="text-green-300 text-xs mt-1">Kullanıcılar "${type === 'orderStatus' ? 'sipariş durumum nedir' : 'kargom nerede'}" dediğinde bu API çağrılacak.</p>
                    </div>
                </div>
            `;
            
            // Update status
            if (type === 'orderStatus') {
                updateOrderStatusStatus(true);
            } else if (type === 'cargoTracking') {
                updateCargoTrackingStatus(true);
            }
        } else {
            resultHtml = `
                <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-lg">
                    <h4 class="text-red-400 font-semibold mb-2">❌ API Test Başarısız!</h4>
                    <div class="space-y-2 text-sm">
                        <div><span class="text-gray-400">Hata:</span> <span class="text-white">${data.error || 'Bilinmeyen hata'}</span></div>
                        ${data.status_code ? `<div><span class="text-gray-400">Status Code:</span> <span class="text-white">${data.status_code}</span></div>` : ''}
                    </div>
                    <div class="mt-3 p-3 bg-red-500/20 rounded-lg">
                        <p class="text-red-300 text-sm">🔧 API'nizi kontrol edin:</p>
                        <ul class="text-red-300 text-xs mt-1 space-y-1">
                            <li>• URL'nin doğru olduğundan emin olun</li>
                            <li>• API'nin çalışır durumda olduğunu kontrol edin</li>
                            <li>• HTTP 200 döndürdüğünden emin olun</li>
                            <li>• JSON formatında yanıt verdiğinden emin olun</li>
                        </ul>
                    </div>
                </div>
            `;
        }
        
        // Add response details
        if (data.response) {
            resultHtml += `
                <div class="p-4 bg-gray-800/50 rounded-lg">
                    <h4 class="text-white font-semibold mb-2">API Yanıtı:</h4>
                    <pre class="text-sm text-gray-300 overflow-x-auto">${JSON.stringify(data.response, null, 2)}</pre>
                </div>
            `;
        }
        
        document.getElementById('apiTestResult').innerHTML = resultHtml;
    }

    function updateOrderStatusStatus(active) {
        const statusElement = document.getElementById('orderStatusStatus');
        if (active) {
            statusElement.textContent = 'Aktif';
            statusElement.className = 'ml-2 px-2 py-1 text-xs rounded-full bg-green-500/20 text-green-400';
        } else {
            statusElement.textContent = 'Pasif';
            statusElement.className = 'ml-2 px-2 py-1 text-xs rounded-full bg-gray-500/20 text-gray-400';
        }
    }

    function updateCargoTrackingStatus(active) {
        const statusElement = document.getElementById('cargoTrackingStatus');
        if (active) {
            statusElement.textContent = 'Aktif';
            statusElement.className = 'ml-2 px-2 py-1 text-xs rounded-full bg-green-500/20 text-green-400';
        } else {
            statusElement.textContent = 'Pasif';
            statusElement.className = 'ml-2 px-2 py-1 text-xs rounded-full bg-gray-500/20 text-gray-400';
        }
    }

    function saveApiConfig() {
        const orderStatusUrl = document.getElementById('orderStatusApiUrl').value;
        const cargoTrackingUrl = document.getElementById('cargoTrackingApiUrl').value;
        
        // AI Field Mapping modal'ını göster
        showAIFieldMappingModal();
        
        // Her iki API için field mapping yap
        const promises = [];
        
        if (orderStatusUrl) {
            promises.push(performFieldMapping(orderStatusUrl, 'order_status'));
        }
        
        if (cargoTrackingUrl) {
            promises.push(performFieldMapping(cargoTrackingUrl, 'cargo_tracking'));
        }
        
        if (promises.length === 0) {
            hideAIFieldMappingModal();
            alert('Lütfen en az bir API URL girin');
            return;
        }
        
        // Tüm field mapping işlemlerini bekle
        Promise.all(promises)
            .then(results => {
                // Field mapping sonuçlarını göster
                displayFieldMappingResults(results);
                
                // API konfigürasyonunu kaydet
                return fetch('/dashboard/api-settings/config', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        order_status_api_url: orderStatusUrl || null,
                        cargo_tracking_api_url: cargoTrackingUrl || null
                    })
                });
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideAIFieldMappingModal();
                    alert('✅ API konfigürasyonu ve field mapping başarıyla tamamlandı!');
                } else {
                    hideAIFieldMappingModal();
                    alert('❌ Hata: ' + data.message);
                }
            })
            .catch(error => {
                hideAIFieldMappingModal();
                alert('❌ Hata: ' + error.message);
            });
    }

    function performFieldMapping(apiUrl, apiType) {
        return fetch('/dashboard/api-settings/field-mapping', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                api_url: apiUrl,
                api_type: apiType
            })
        })
        .then(response => response.json())
        .then(data => {
            return {
                apiType: apiType,
                result: data
            };
        });
    }

    function showAIFieldMappingModal() {
        document.getElementById('aiFieldMappingContent').innerHTML = `
            <div class="text-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-glow mx-auto mb-4"></div>
                <h4 class="text-white text-lg font-semibold mb-2">🤖 Yapay Zeka Field Mapping Yapılıyor</h4>
                <p class="text-gray-400">API response'larınız analiz ediliyor ve field mapping yapılıyor...</p>
                <p class="text-gray-500 text-sm mt-2">Bu işlem birkaç saniye sürebilir.</p>
            </div>
        `;
        document.getElementById('aiFieldMappingModal').classList.remove('hidden');
    }

    function hideAIFieldMappingModal() {
        document.getElementById('aiFieldMappingModal').classList.add('hidden');
    }

    function displayFieldMappingResults(results) {
        const resultsContainer = document.getElementById('fieldMappingResults');
        resultsContainer.classList.remove('hidden');
        
        results.forEach(({ apiType, result }) => {
            if (result.success) {
                displayMappingResult(apiType, result);
            }
        });
    }

    function displayMappingResult(apiType, result) {
        const containerId = apiType === 'order_status' ? 'orderStatusMapping' : 'cargoTrackingMapping';
        const contentId = apiType === 'order_status' ? 'orderStatusMappingContent' : 'cargoTrackingMappingContent';
        
        document.getElementById(containerId).classList.remove('hidden');
        
        const quality = result.quality_evaluation;
        const qualityColor = quality.quality === 'excellent' ? 'text-green-400' : 
                            quality.quality === 'good' ? 'text-blue-400' : 
                            quality.quality === 'fair' ? 'text-yellow-400' : 'text-red-400';
        
        let html = `
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-white font-semibold mb-3">Field Mapping Kalitesi</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Kalite Skoru:</span>
                            <span class="${qualityColor} font-semibold">${quality.score}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Kalite:</span>
                            <span class="${qualityColor} font-semibold">${quality.quality}</span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-white font-semibold mb-3">Eşleşen Alanlar</h4>
                    <div class="space-y-2">
        `;
        
        if (result.mapping && result.mapping.fields) {
            Object.entries(result.mapping.fields).forEach(([fieldName, fieldData]) => {
                html += `
                    <div class="flex justify-between items-center p-2 bg-gray-800/50 rounded">
                        <span class="text-gray-300">${fieldName}</span>
                        <span class="text-green-400 text-sm">✓ ${Math.round(fieldData.confidence * 100)}%</span>
                    </div>
                `;
            });
        }
        
        html += `
                    </div>
                </div>
            </div>
        `;
        
        if (quality.missing_fields && quality.missing_fields.length > 0) {
            html += `
                <div class="mt-4 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                    <h4 class="text-yellow-400 font-semibold mb-2">⚠️ Eksik Alanlar</h4>
                    <div class="space-y-2">
            `;
            
            quality.missing_fields.forEach(field => {
                const suggestion = quality.suggestions[field] || 'Öneri bulunamadı';
                html += `
                    <div class="text-sm">
                        <span class="text-yellow-300 font-semibold">${field}:</span>
                        <span class="text-gray-300">${suggestion}</span>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        }
        
        document.getElementById(contentId).innerHTML = html;
    }

    function closeApiTestModal() {
        document.getElementById('apiTestModal').classList.add('hidden');
    }
</script>
@endsection
