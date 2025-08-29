@extends('layouts.dashboard')

@section('title', __('dashboard.widget_design'))

@section('content')
<style>
@keyframes pulse-glow {
    0%, 100% { 
        opacity: 0.3; 
        transform: scale(1);
    }
    50% { 
        opacity: 0.8; 
        transform: scale(1.05);
    }
}

@keyframes slide-in-up {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-in-scale {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.loading-pulse {
    animation: pulse-glow 2s ease-in-out infinite;
}

.slide-in-up {
    animation: slide-in-up 0.6s ease-out forwards;
}

.fade-in-scale {
    animation: fade-in-scale 0.5s ease-out forwards;
}

.progress-animation {
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>

<div class="space-y-6">
    <!-- Widget Design Header -->
    <div class="glass-effect rounded-2xl p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-glow rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-neon-purple rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        
        <div class="relative z-10 slide-in-up">
            <h1 class="text-4xl font-bold mb-4">
                <span class="gradient-text">{{ __('dashboard.widget_design') }}</span>
            </h1>
            <p class="text-xl text-gray-300">
                Widget tasarımı ve API ayarlarını buradan yönetebilirsiniz
            </p>
            @if($projectId)
                <div class="mt-3 p-3 bg-blue-500/20 border border-blue-500/30 rounded-lg">
                    <span class="text-blue-400 text-sm">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Proje ID: {{ $projectId }} için widget ayarları
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="glass-effect rounded-2xl p-8">
        <div class="flex flex-col items-center justify-center space-y-6">
            <!-- Animated Loading Spinner -->
            <div class="relative loading-pulse">
                <div class="w-16 h-16 border-4 border-gray-700 rounded-full"></div>
                <div class="absolute top-0 left-0 w-16 h-16 border-4 border-transparent border-t-purple-500 rounded-full animate-spin"></div>
                <div class="absolute top-0 left-0 w-16 h-16 border-4 border-transparent border-t-neon-purple rounded-full animate-spin" style="animation-delay: -0.5s;"></div>
                <div class="absolute top-0 left-0 w-16 h-16 border-4 border-transparent border-t-blue-500 rounded-full animate-spin" style="animation-delay: -1s;"></div>
            </div>
            
            <!-- Loading Text -->
            <div class="text-center">
                <h3 class="text-xl font-semibold text-white mb-2">Widget Ayarları Yükleniyor</h3>
                <p class="text-gray-400">Lütfen bekleyin...</p>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-64 bg-gray-700 rounded-full h-2 overflow-hidden">
                <div id="progressBar" class="bg-gradient-to-r from-purple-glow to-neon-purple h-2 rounded-full progress-animation" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <!-- Loading Skeleton (Hidden initially) -->
    <div id="skeletonState" class="hidden space-y-6">
        <!-- API Ayarları Skeleton -->
        <div class="glass-effect rounded-2xl p-8">
            <div class="animate-pulse">
                <div class="h-8 bg-gray-700 rounded-lg w-48 mb-6"></div>
                
                <!-- Sipariş Durumu Skeleton -->
                <div class="space-y-4 mb-6">
                    <div class="h-5 bg-gray-700 rounded w-64"></div>
                    <div class="flex space-x-3">
                        <div class="flex-1 h-12 bg-gray-700 rounded-lg"></div>
                        <div class="w-24 h-12 bg-gray-700 rounded-lg"></div>
                    </div>
                    <div class="h-4 bg-gray-700 rounded w-80"></div>
                </div>
                
                <!-- Kargo Durumu Skeleton -->
                <div class="space-y-4 mb-6">
                    <div class="h-5 bg-gray-700 rounded w-64"></div>
                    <div class="flex space-x-3">
                        <div class="flex-1 h-12 bg-gray-700 rounded-lg"></div>
                        <div class="w-24 h-12 bg-gray-700 rounded-lg"></div>
                    </div>
                    <div class="h-4 bg-gray-700 rounded w-80"></div>
                </div>
                
                <!-- Info Box Skeleton -->
                <div class="h-16 bg-gray-700 rounded-lg mb-6"></div>
                
                <!-- Button Skeleton -->
                <div class="flex justify-end">
                    <div class="w-32 h-12 bg-gray-700 rounded-lg"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Container (Hidden initially) -->
    <div id="contentContainer" class="hidden space-y-6 slide-in-up">
        <!-- API Ayarları Container -->
        <div class="glass-effect rounded-2xl p-8">
            <h2 class="text-2xl font-bold mb-6 text-white">API Ayarları</h2>
            
            <!-- Success Message -->
            <div id="successMessage" class="hidden mb-4 p-3 bg-green-500/20 border border-green-500/30 rounded-lg text-green-400 text-sm">
                Ayarlar başarıyla kaydedildi!
            </div>
            
            <!-- Error Message -->
            <div id="errorMessage" class="hidden mb-4 p-3 bg-red-500/20 border border-red-500/30 rounded-lg text-red-400 text-sm">
                Hata oluştu!
            </div>
            
            <form id="widgetApiForm" class="space-y-6">
                @csrf
                
                <!-- Sipariş Durumu API -->
                <div class="space-y-4">
                    <div>
                        <label for="siparis_durumu_endpoint" class="block text-sm font-medium text-gray-300 mb-2">
                            Sipariş Durumu API Endpoint
                        </label>
                        <div class="flex space-x-3">
                            <input 
                                type="url" 
                                id="siparis_durumu_endpoint" 
                                name="siparis_durumu_endpoint"
                                placeholder="https://example.com/api/order-tracking"
                                class="flex-1 px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                            >
                                                    <button 
                            type="button"
                            id="testSiparisButton"
                            onclick="testEndpoint('siparis')"
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded-lg text-white font-semibold transition-all duration-200 flex items-center space-x-2"
                        >
                            <span id="testSiparisText">Test Et</span>
                            <div id="testSiparisSpinner" class="hidden">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            </div>
                        </button>
                        </div>
                        <p class="text-sm text-gray-400 mt-1">
                            Sipariş durumu sorgulama için API endpoint'i
                        </p>
                    </div>
                </div>
                
                <!-- Kargo Durumu API -->
                <div class="space-y-4">
                    <div>
                        <label for="kargo_durumu_endpoint" class="block text-sm font-medium text-gray-300 mb-2">
                            Kargo Durumu API Endpoint
                        </label>
                        <div class="flex space-x-3">
                            <input 
                                type="url" 
                                id="kargo_durumu_endpoint" 
                                name="kargo_durumu_endpoint"
                                placeholder="https://example.com/api/cargo-tracking"
                                class="flex-1 px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                            >
                                                    <button 
                            type="button"
                            id="testKargoButton"
                            onclick="testEndpoint('kargo')"
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded-lg text-white font-semibold transition-all duration-200 flex items-center space-x-2"
                        >
                            <span id="testKargoText">Test Et</span>
                            <div id="testKargoSpinner" class="hidden">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            </div>
                        </button>
                        </div>
                        <p class="text-sm text-gray-400 mt-1">
                            Kargo durumu sorgulama için API endpoint'i
                        </p>
                    </div>
                </div>
                
                <!-- HTTP Action Info -->
                <div class="p-4 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-blue-400 text-sm">
                            Şu anda sadece GET işlemleri desteklenmektedir
                        </span>
                    </div>
                </div>
                
                            <!-- Save Button -->
            <div class="flex justify-end">
                <button 
                    type="submit"
                    id="saveButton"
                    class="px-8 py-3 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg text-white font-semibold hover:from-purple-dark hover:to-neon-purple transition-all duration-300 transform hover:scale-105 flex items-center space-x-2"
                >
                    <span id="saveButtonText">Ayarları Kaydet</span>
                    <div id="saveButtonSpinner" class="hidden">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    </div>
                </button>
            </div>
            </form>
        </div>
    </div>

    <!-- Error State -->
    <div id="errorState" class="hidden glass-effect rounded-2xl p-8 fade-in-scale">
        <div class="flex flex-col items-center justify-center space-y-4">
            <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-white">İçerik Yüklenemedi</h3>
            <p class="text-gray-400 text-center">Widget ayarları yüklenirken bir hata oluştu. Lütfen sayfayı yenileyin.</p>
            <button 
                onclick="retryLoading()"
                class="px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded-lg text-white font-semibold transition-all duration-200"
            >
                Tekrar Dene
            </button>
        </div>
    </div>
</div>

<script>
// Global variables
let loadingProgress = 0;
let loadingInterval;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    startLoading();
    loadContent();
});

// Start loading animation
function startLoading() {
    loadingProgress = 0;
    const progressBar = document.getElementById('progressBar');
    
    loadingInterval = setInterval(() => {
        loadingProgress += Math.random() * 15;
        if (loadingProgress > 90) loadingProgress = 90;
        
        progressBar.style.width = loadingProgress + '%';
    }, 200);
}

// Complete loading animation
function completeLoading() {
    clearInterval(loadingInterval);
    const progressBar = document.getElementById('progressBar');
    progressBar.style.width = '100%';
    
    setTimeout(() => {
        document.getElementById('loadingState').classList.add('hidden');
        document.getElementById('skeletonState').classList.remove('hidden');
        
        // Show skeleton for a moment to simulate content loading
        setTimeout(() => {
            document.getElementById('skeletonState').classList.add('hidden');
            document.getElementById('contentContainer').classList.remove('hidden');
            
            // Add fade-in animation
            const contentContainer = document.getElementById('contentContainer');
            contentContainer.style.opacity = '0';
            contentContainer.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                contentContainer.style.transition = 'all 0.5s ease-out';
                contentContainer.style.opacity = '1';
                contentContainer.style.transform = 'translateY(0)';
            }, 100);
        }, 800); // Show skeleton for 800ms
    }, 500);
}

// Load content from server
async function loadContent() {
    try {
        const projectId = '{{ $projectId }}';
        const url = '{{ route("dashboard.widget-design.load-content") }}' + (projectId ? `?project_id=${projectId}` : '');
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Populate form fields with loaded data
            populateFormFields(result.data);
            completeLoading();
        } else {
            throw new Error(result.message || 'İçerik yüklenemedi');
        }
        
    } catch (error) {
        console.error('Loading error:', error);
        showErrorState();
    }
}

// Populate form fields with loaded data
function populateFormFields(data) {
    if (data.widgetActions) {
        if (data.widgetActions.siparis_durumu_endpoint) {
            document.getElementById('siparis_durumu_endpoint').value = data.widgetActions.siparis_durumu_endpoint;
        }
        if (data.widgetActions.kargo_durumu_endpoint) {
            document.getElementById('kargo_durumu_endpoint').value = data.widgetActions.kargo_durumu_endpoint;
        }
    }
    
    // Add staggered animations to form elements
    const formElements = document.querySelectorAll('#contentContainer .glass-effect, #contentContainer form > div');
    formElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            element.style.transition = 'all 0.5s ease-out';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, 200 + (index * 100));
    });
}

// Show error state
function showErrorState() {
    clearInterval(loadingInterval);
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('errorState').classList.remove('hidden');
}

// Retry loading
function retryLoading() {
    document.getElementById('errorState').classList.add('hidden');
    document.getElementById('loadingState').classList.remove('hidden');
    startLoading();
    loadContent();
}

// Form submission
document.getElementById('widgetApiForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Show loading state
    const saveButton = document.getElementById('saveButton');
    const saveButtonText = document.getElementById('saveButtonText');
    const saveButtonSpinner = document.getElementById('saveButtonSpinner');
    
    saveButton.disabled = true;
    saveButtonText.textContent = 'Kaydediliyor...';
    saveButtonSpinner.classList.remove('hidden');
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('{{ route("dashboard.widget-design.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('successMessage', result.message);
        } else {
            showMessage('errorMessage', result.message);
        }
    } catch (error) {
        showMessage('errorMessage', 'Bir hata oluştu: ' + error.message);
    } finally {
        // Reset button state
        saveButton.disabled = false;
        saveButtonText.textContent = 'Ayarları Kaydet';
        saveButtonSpinner.classList.add('hidden');
    }
});

// Test endpoint function
async function testEndpoint(type) {
    const endpointInput = document.getElementById(type === 'siparis' ? 'siparis_durumu_endpoint' : 'kargo_durumu_endpoint');
    const endpoint = endpointInput.value.trim();
    
    if (!endpoint) {
        showMessage('errorMessage', 'Lütfen önce endpoint URL\'ini girin');
        return;
    }
    
    // Show loading state
    const button = document.getElementById(type === 'siparis' ? 'testSiparisButton' : 'testKargoButton');
    const buttonText = document.getElementById(type === 'siparis' ? 'testSiparisText' : 'testKargoText');
    const buttonSpinner = document.getElementById(type === 'siparis' ? 'testSiparisSpinner' : 'testKargoSpinner');
    
    button.disabled = true;
    buttonText.textContent = 'Test Ediliyor...';
    buttonSpinner.classList.remove('hidden');
    
    try {
        const response = await fetch('{{ route("dashboard.widget-design.test-endpoint") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({
                endpoint: endpoint,
                type: type
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('successMessage', `${type === 'siparis' ? 'Sipariş' : 'Kargo'} API endpoint başarıyla test edildi!`);
        } else {
            showMessage('errorMessage', result.message);
        }
    } catch (error) {
        showMessage('errorMessage', 'Test sırasında hata oluştu: ' + error.message);
    } finally {
        // Reset button state
        button.disabled = false;
        buttonText.textContent = 'Test Et';
        buttonSpinner.classList.add('hidden');
    }
}

// Show message function
function showMessage(elementId, message) {
    const element = document.getElementById(elementId);
    element.textContent = message;
    element.classList.remove('hidden');
    
    setTimeout(() => {
        element.classList.add('hidden');
    }, 5000);
}
</script>
@endsection
