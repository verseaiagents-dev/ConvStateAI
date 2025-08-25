@extends('layouts.dashboard')

@section('title', 'Widget Tasarımı')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white">Widget Tasarımı</h1>
            <p class="text-gray-400 mt-2">AI asistanınızın ismini ve karşılama mesajını özelleştirin</p>
        </div>
    </div>

    <!-- Widget Customization Form -->
    <div class="bg-gray-900/50 backdrop-blur-xl border border-gray-800 rounded-xl p-8">
        <div class="max-w-2xl mx-auto">
            <h3 class="text-xl font-medium text-white mb-6 text-center">🤖 AI Asistan Özelleştirme</h3>
            
            <form id="widgetCustomizationForm" class="space-y-6">
                <!-- AI Name Input -->
                <div>
                    <label for="ai_name" class="block text-sm font-medium text-gray-300 mb-2">
                        AI Asistan İsmi
                    </label>
                    <input 
                        type="text" 
                        id="ai_name" 
                        name="ai_name" 
                        placeholder="Örn: Kadir AI"
                        class="w-full px-4 py-3 bg-gray-800/50 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20 transition-all duration-200"
                        maxlength="100"
                    >
                    <p class="text-sm text-gray-400 mt-1">AI asistanınızın görünecek ismini belirleyin</p>
                </div>

                <!-- Welcome Message Input -->
                <div>
                    <label for="welcome_message" class="block text-sm font-medium text-gray-300 mb-2">
                        Karşılama Mesajı
                    </label>
                    <textarea 
                        id="welcome_message" 
                        name="welcome_message" 
                        rows="4"
                        placeholder="Örn: Merhaba ben Kadir, senin dijital asistanınım. Sana nasıl yardımcı olabilirim?"
                        class="w-full px-4 py-3 bg-gray-800/50 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20 transition-all duration-200 resize-none"
                        maxlength="1000"
                    ></textarea>
                    <p class="text-sm text-gray-400 mt-1">AI asistanınızın kullanıcıları karşılayacağı mesajı yazın</p>
                </div>

                <!-- Save Button -->
                <div class="flex justify-center">
                    <button 
                        type="submit" 
                        class="px-8 py-3 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg text-white font-semibold hover:from-purple-dark hover:to-neon-purple transition-all duration-300 transform hover:scale-105"
                    >
                        💾 Özelleştirmeleri Kaydet
                    </button>
                </div>
            </form>

            <!-- Preview Section -->
            <div class="mt-8 pt-8 border-t border-gray-700">
                <h4 class="text-lg font-medium text-white mb-4 text-center">👀 Önizleme</h4>
                <div class="bg-gray-800/50 rounded-lg p-6 border border-gray-700">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center">
                            <span class="text-purple-400 text-lg">🤖</span>
                        </div>
                        <div>
                            <h5 class="text-white font-medium" id="previewAiName">Kadir AI</h5>
                            <p class="text-sm text-gray-400">AI Asistan</p>
                        </div>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-4">
                        <p class="text-gray-300" id="previewWelcomeMessage">
                            Merhaba ben Kadir, senin dijital asistanınım. Sana nasıl yardımcı olabilirim?
                        </p>
                    </div>
                </div>
            </div>

            <!-- Personal Token Management -->
            <div class="mt-8 pt-8 border-t border-gray-700">
                <h4 class="text-lg font-medium text-white mb-4 text-center">🔑 Personal Token Yönetimi</h4>
                <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                    <div id="tokenInfo" class="mb-4">
                        <p class="text-sm text-gray-300 mb-3">API kullanımı için personal token gerekli:</p>
                        <div class="bg-gray-900 rounded p-3 text-xs text-gray-400 font-mono">
                            <div id="tokenDisplay" class="hidden">
                                <strong>Token:</strong> <span id="tokenValue" class="text-green-400"></span><br>
                                <strong>User ID:</strong> <span id="userIdValue" class="text-blue-400"></span><br>
                                <strong>Expires:</strong> <span id="tokenExpiry" class="text-yellow-400"></span>
                            </div>
                            <div id="noTokenMessage" class="text-red-400">
                                Henüz token oluşturulmamış
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button 
                            id="generateTokenBtn"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm transition-colors duration-200"
                        >
                            🔑 Token Oluştur
                        </button>
                        <button 
                            id="revokeTokenBtn"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm transition-colors duration-200 hidden"
                        >
                            🗑️ Token İptal Et
                        </button>
                    </div>
                </div>
            </div>

            <!-- API Info -->
            <div class="mt-8 pt-8 border-t border-gray-700">
                <h4 class="text-lg font-medium text-white mb-4 text-center">🔗 API Kullanımı</h4>
                <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                    <p class="text-sm text-gray-300 mb-3">React projenizde bu özelleştirmeleri kullanmak için:</p>
                    <div class="bg-gray-900 rounded p-3 text-xs text-gray-400 font-mono">
                        GET /api/widget-customization<br>
                        Headers:<br>
                        X-Personal-Token: [YOUR_TOKEN]<br>
                        X-User-ID: [YOUR_USER_ID]
                    </div>
                    <p class="text-sm text-gray-400 mt-2">Bu endpoint size JSON formatında özelleştirme verilerini döner.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Message -->
<div id="successMessage" class="fixed top-4 right-4 bg-green-500/20 border border-green-500/30 rounded-lg p-4 text-green-400 hidden z-50">
    <div class="flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span id="successText">Özelleştirmeler başarıyla kaydedildi!</span>
    </div>
</div>

<!-- Error Message -->
<div id="errorMessage" class="fixed top-4 right-4 bg-red-500/20 border border-red-500/30 rounded-lg p-4 text-red-400 hidden z-50">
    <div class="flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
        <span id="errorText">Bir hata oluştu!</span>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('widgetCustomizationForm');
    const aiNameInput = document.getElementById('ai_name');
    const welcomeMessageInput = document.getElementById('welcomeMessage');
    const previewAiName = document.getElementById('previewAiName');
    const previewWelcomeMessage = document.getElementById('previewWelcomeMessage');
    
    // Personal Token Elements
    const generateTokenBtn = document.getElementById('generateTokenBtn');
    const revokeTokenBtn = document.getElementById('revokeTokenBtn');
    const tokenDisplay = document.getElementById('tokenDisplay');
    const noTokenMessage = document.getElementById('noTokenMessage');
    const tokenValue = document.getElementById('tokenValue');
    const userIdValue = document.getElementById('userIdValue');
    const tokenExpiry = document.getElementById('tokenExpiry');
    
    // Load existing customization data
    loadCustomization();
    
    // Load token info
    loadTokenInfo();
    
    // Token management event listeners
    generateTokenBtn.addEventListener('click', generateToken);
    revokeTokenBtn.addEventListener('click', revokeToken);
    
    // Real-time preview updates
    aiNameInput.addEventListener('input', function() {
        previewAiName.textContent = this.value || 'Kadir AI';
    });
    
    welcomeMessageInput.addEventListener('input', function() {
        previewWelcomeMessage.textContent = this.value || 'Merhaba ben Kadir, senin dijital asistanınım. Sana nasıl yardımcı olabilirim?';
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        saveCustomization();
    });
    
    function loadCustomization() {
        fetch('/dashboard/widget-customization', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                aiNameInput.value = data.data.ai_name || '';
                welcomeMessageInput.value = data.data.welcome_message || '';
                
                // Update preview
                previewAiName.textContent = data.data.ai_name || 'Kadir AI';
                previewWelcomeMessage.textContent = data.data.welcome_message || 'Merhaba ben Kadir, senin dijital asistanınım. Sana nasıl yardımcı olabilirim?';
            }
        })
        .catch(error => {
            console.error('Error loading customization:', error);
        });
    }
    
    function loadTokenInfo() {
        fetch('/dashboard/personal-token', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.has_token) {
                showTokenInfo(data.data);
            } else {
                hideTokenInfo();
            }
        })
        .catch(error => {
            console.error('Error loading token info:', error);
            hideTokenInfo();
        });
    }
    
    function generateToken() {
        generateTokenBtn.disabled = true;
        generateTokenBtn.textContent = '🔄 Oluşturuluyor...';
        
        fetch('/dashboard/personal-token/generate', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showTokenInfo(data.data);
                showMessage('success', data.message);
            } else {
                showMessage('error', data.message || 'Token oluşturulamadı');
            }
        })
        .catch(error => {
            console.error('Error generating token:', error);
            showMessage('error', 'Token oluşturulurken hata oluştu');
        })
        .finally(() => {
            generateTokenBtn.disabled = false;
            generateTokenBtn.textContent = '🔑 Token Oluştur';
        });
    }
    
    function revokeToken() {
        if (!confirm('Bu token\'ı iptal etmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
            return;
        }
        
        revokeTokenBtn.disabled = true;
        revokeTokenBtn.textContent = '🔄 İptal Ediliyor...';
        
        fetch('/dashboard/personal-token/revoke', {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideTokenInfo();
                showMessage('success', data.message);
            } else {
                showMessage('error', data.message || 'Token iptal edilemedi');
            }
        })
        .catch(error => {
            console.error('Error revoking token:', error);
            showMessage('error', 'Token iptal edilirken hata oluştu');
        })
        .finally(() => {
            revokeTokenBtn.disabled = false;
            revokeTokenBtn.textContent = '🗑️ Token İptal Et';
        });
    }
    
    function showTokenInfo(tokenData) {
        tokenValue.textContent = tokenData.token;
        userIdValue.textContent = tokenData.user_id;
        tokenExpiry.textContent = new Date(tokenData.expires_at).toLocaleString('tr-TR');
        
        tokenDisplay.classList.remove('hidden');
        noTokenMessage.classList.add('hidden');
        generateTokenBtn.classList.add('hidden');
        revokeTokenBtn.classList.remove('hidden');
    }
    
    function hideTokenInfo() {
        tokenDisplay.classList.add('hidden');
        noTokenMessage.classList.remove('hidden');
        generateTokenBtn.classList.remove('hidden');
        revokeTokenBtn.classList.add('hidden');
    }
    
    function saveCustomization() {
        const formData = new FormData();
        formData.append('ai_name', aiNameInput.value);
        formData.append('welcome_message', welcomeMessageInput.value);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        fetch('/dashboard/widget-customization', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('success', data.message);
            } else {
                showMessage('error', data.message || 'Bir hata oluştu');
            }
        })
        .catch(error => {
            console.error('Error saving customization:', error);
            showMessage('error', 'Bağlantı hatası oluştu');
        });
    }
    
    function showMessage(type, message) {
        const successMessage = document.getElementById('successMessage');
        const errorMessage = document.getElementById('errorMessage');
        const successText = document.getElementById('successText');
        const errorText = document.getElementById('errorText');
        
        if (type === 'success') {
            successText.textContent = message;
            successMessage.classList.remove('hidden');
            setTimeout(() => successMessage.classList.add('hidden'), 3000);
        } else {
            errorText.textContent = message;
            errorMessage.classList.remove('hidden');
            setTimeout(() => errorMessage.classList.add('hidden'), 3000);
        }
    }
});
</script>
@endsection
