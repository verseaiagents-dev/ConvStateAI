@extends('layouts.dashboard')

@section('title', __('dashboard.campaign_management'))

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Campaign Management Header -->
    <div class="glass-effect rounded-2xl p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-glow rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-neon-purple rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        
        <div class="relative z-10">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-4xl font-bold mb-4">
                        <span class="gradient-text">{{ __('dashboard.campaign_management') }}</span>
                    </h1>
                    <p class="text-xl text-gray-300">
                        {{ __('dashboard.campaign_management_description') }}
                    </p>
                </div>
                
                <button onclick="openCreateModal()" class="px-6 py-3 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg text-white font-semibold hover:from-purple-dark hover:to-neon-purple transition-all duration-300 transform hover:scale-105 flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span>{{ __('dashboard.create_new_campaign') }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Campaign List -->
    <div class="glass-effect rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gradient-to-r from-gray-800 to-gray-900">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-white uppercase tracking-wider">{{ __('dashboard.campaign') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-white uppercase tracking-wider">{{ __('dashboard.category') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-white uppercase tracking-wider">{{ __('dashboard.discount') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-white uppercase tracking-wider">{{ __('dashboard.status') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-white uppercase tracking-wider">{{ __('dashboard.validity') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-white uppercase tracking-wider">{{ __('dashboard.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700" id="campaignsTableBody">
                    <!-- Campaigns will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="glass-effect rounded-2xl p-8 text-center">
        <div class="inline-flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-6 w-6 text-purple-glow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-300">{{ __('dashboard.campaigns_loading') }}</span>
        </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden glass-effect rounded-2xl p-12 text-center">
        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
        </svg>
        <h3 class="text-xl font-semibold text-white mb-2">{{ __('dashboard.no_campaigns_yet') }}</h3>
        <p class="text-gray-400 mb-6">{{ __('dashboard.create_first_campaign_description') }}</p>
        <button onclick="openCreateModal()" class="px-6 py-3 bg-gradient-to-r from-purple-glow to-neon-purple rounded-xl text-white font-semibold hover:from-purple-dark hover:to-purple-glow transition-all duration-300 transform hover:scale-105 inline-flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span>{{ __('dashboard.create_first_campaign') }}</span>
        </button>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="campaignModal" class="fixed inset-0 bg-black bg-opacity-85 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50" onclick="closeModal()">
                    <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 p-6 border border-gray-700 w-3/4 max-w-4xl max-h-[80vh] shadow-2xl rounded-xl glass-effect overflow-y-auto custom-scrollbar" onclick="event.stopPropagation()">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-white" id="modalTitle">{{ __('dashboard.new_campaign') }}</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-white transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- AI Campaign Creation Tab -->
            <div class="mb-6">
                <div class="flex space-x-1 bg-gray-800 rounded-lg p-1">
                    <button id="aiTab" onclick="switchTab('ai')" class="flex-1 py-2 px-4 rounded-md text-sm font-medium text-white bg-purple-600 transition-all duration-200">
                        🤖 AI ile Kampanya Oluştur
                    </button>
                    <button id="manualTab" onclick="switchTab('manual')" class="flex-1 py-2 px-4 rounded-md text-sm font-medium text-gray-300 hover:text-white transition-all duration-200">
                        ✏️ Manuel Oluştur
                    </button>
                </div>
            </div>

            <!-- AI Campaign Creation Form -->
            <div id="aiForm" class="space-y-6">
                <!-- Step 1: Product Selection -->
                <div id="step1" class="step-content">
                    <h4 class="text-lg font-semibold text-white mb-4">1. Ürün Seçimi</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Ürün Kategorisi</label>
                            <select id="productCategory" onchange="filterProducts()" class="form-input w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                                <option value="">Tüm Kategoriler</option>
                                <option value="men's clothing">Erkek Giyim</option>
                                <option value="women's clothing">Kadın Giyim</option>
                                <option value="jewelery">Takı</option>
                                <option value="electronics">Elektronik</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Stok Durumu</label>
                            <select id="stockFilter" onchange="filterProducts()" class="form-input w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                                <option value="">Tüm Stoklar</option>
                                <option value="high">Yüksek Stok (>50)</option>
                                <option value="medium">Orta Stok (10-50)</option>
                                <option value="low">Düşük Stok (<10)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Ürünleri Seçin (Çoklu seçim için Ctrl tuşu ile tıklayın)</label>
                        <div class="max-h-60 overflow-y-auto border border-gray-600 rounded-lg p-3 bg-gray-800">
                            <div id="productList" class="space-y-2">
                                <!-- Products will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Product Campaign Settings -->
                <div id="step2" class="step-content hidden">
                    <h4 class="text-lg font-semibold text-white mb-4">2. Ürün Kampanya Ayarları</h4>
                    <div id="productSettingsContainer" class="space-y-6 max-h-96 overflow-y-auto custom-scrollbar pr-2">
                        <!-- Her ürün için ayrı ayar alanları buraya dinamik olarak eklenecek -->
                    </div>
                </div>

                <!-- Step 3: AI Suggestions -->
                <div id="step3" class="step-content hidden">
                    <h4 class="text-lg font-semibold text-white mb-4">3. AI Kampanya Önerileri</h4>
                    <div id="aiSuggestions" class="space-y-4">
                        <!-- AI suggestions will be loaded here -->
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between pt-6">
                    <button type="button" id="prevBtn" onclick="previousStep()" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-all duration-200 hidden">
                        ← Önceki
                    </button>
                    <button type="button" id="nextBtn" onclick="nextStep()" class="px-6 py-3 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg text-white font-semibold hover:from-purple-dark hover:to-purple-glow transition-all duration-300 transform hover:scale-105">
                        Sonraki →
                    </button>
                </div>
            </div>

            <!-- Manual Campaign Creation Form -->
            <div id="manualForm" class="hidden space-y-4">
                <form id="campaignForm" class="space-y-4">
                    <input type="hidden" id="campaignId" name="id">
                    <input type="hidden" name="site_id" value="1">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-300 mb-2">{{ __('dashboard.campaign_title') }} *</label>
                            <input type="text" id="title" name="title" required class="form-input w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                        </div>
                        
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-300 mb-2">{{ __('dashboard.category') }} *</label>
                            <select id="category" name="category" required class="form-input w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                                <option value="" class="bg-gray-800 text-white">{{ __('dashboard.select_category') }}</option>
                                <option value="Moda" class="bg-gray-800 text-white">{{ __('dashboard.fashion') }}</option>
                                <option value="Elektronik" class="bg-gray-800 text-white">{{ __('dashboard.electronics') }}</option>
                                <option value="Ev & Yaşam" class="bg-gray-800 text-white">{{ __('dashboard.home_lifestyle') }}</option>
                                <option value="Spor" class="bg-gray-800 text-white">{{ __('dashboard.sports') }}</option>
                                <option value="Kozmetik" class="bg-gray-800 text-white">Kozmetik</option>
                                <option value="Genel" class="bg-gray-800 text-white">Genel</option>
                                <option value="Üyelik" class="bg-gray-800 text-white">Üyelik</option>
                                <option value="Ödeme" class="bg-gray-800 text-white">Ödeme</option>
                                <option value="Kargo" class="bg-gray-800 text-white">Kargo</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Açıklama *</label>
                        <textarea id="description" name="description" rows="3" required class="form-input w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="discount" class="block text-sm font-medium text-gray-300 mb-2">İndirim Açıklaması *</label>
                            <input type="text" id="discount" name="discount" required class="form-input w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" placeholder="Örn: %20 İndirim">
                        </div>
                        
                        <div>
                            <label for="discount_type" class="block text-sm font-medium text-gray-300 mb-2">İndirim Türü *</label>
                            <select id="discount_type" name="discount_type" required class="form-input w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                                <option value="buy_x_get_y" class="bg-gray-800 text-white">2 Al 1 Bedava</option>
                                <option value="percentage" class="bg-gray-800 text-white">Yüzde İndirim</option>
                                <option value="fixed" class="bg-gray-800 text-white">Sabit İndirim</option>
                                <option value="free_shipping" class="bg-gray-800 text-white">Ücretsiz Kargo</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="discount_value" class="block text-sm font-medium text-gray-300 mb-2">İndirim Değeri</label>
                            <input type="number" id="discount_value" name="discount_value" step="0.01" min="0" class="form-input w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                        </div>
                        
                        <div>
                            <label for="valid_until" class="block text-sm font-medium text-gray-300 mb-2">Geçerlilik Tarihi</label>
                            <input type="date" id="valid_until" name="valid_until" class="form-input w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-300 mb-2">Başlangıç Tarihi</label>
                            <input type="datetime-local" id="start_date" name="start_date" class="form-input w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                        </div>
                        
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-white mb-2">Bitiş Tarihi</label>
                            <input type="datetime-local" id="end_date" name="end_date" class="form-input w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="minimum_order_amount" class="block text-sm font-medium text-gray-300 mb-2">Minimum Sipariş Tutarı</label>
                            <input type="number" id="minimum_order_amount" name="minimum_order_amount" step="0.01" min="0" class="form-input w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                        </div>
                        
                        <div>
                            <label for="max_usage" class="block text-sm font-medium text-gray-300 mb-2">Maksimum Kullanım</label>
                            <input type="number" id="max_usage" name="max_usage" min="1" class="form-input w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                        </div>
                    </div>

                    <div>
                        <label for="image_url" class="block text-sm font-medium text-gray-300 mb-2">Resim URL</label>
                        <input type="url" id="image_url" name="image_url" class="form-input w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                    </div>

                    <div>
                        <label for="terms_conditions" class="block text-sm font-medium text-gray-300 mb-2">Şartlar ve Koşullar</label>
                        <textarea id="terms_conditions" name="terms_conditions" rows="3" class="form-input w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"></textarea>
                    </div>

                    <div class="flex items-center space-x-3">
                        <input type="checkbox" id="is_active" name="is_active" checked class="form-checkbox">
                        <label for="is_active" class="block text-sm text-gray-300">Kampanyayı aktif yap</label>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6">
                        <button type="button" onclick="closeModal()" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-all duration-200">
                            İptal
                        </button>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg text-white font-semibold hover:from-purple-dark hover:to-purple-glow transition-all duration-300 transform hover:scale-105">
                            Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-85 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50" onclick="closeDeleteModal()">
    <div class="relative top-20 mx-auto p-6 border border-gray-700 w-96 shadow-2xl rounded-xl glass-effect" onclick="event.stopPropagation()">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-900/20 border border-red-500">
                <svg class="h-8 w-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-white mt-4">Kampanyayı Sil</h3>
            <p class="text-gray-400 mt-2">Bu kampanyayı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</p>
            <div class="flex justify-center space-x-3 mt-6">
                <button onclick="closeDeleteModal()" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-all duration-200">
                    İptal
                </button>
                <button onclick="confirmDelete()" class="px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg transition-all duration-300 transform hover:scale-105">
                    Sil
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let campaigns = [];
let currentCampaignId = null;
let currentAISuggestions = null; // AI suggestions for current session

// Load campaigns on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCampaigns();
});

// Load campaigns from API
async function loadCampaigns() {
    try {
        showLoading();
        const response = await fetch('/api/campaigns?site_id=1', {
            headers: {
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        
        if (result.success) {
            campaigns = result.data;
            displayCampaigns();
        } else {
            showError('Kampanyalar yüklenirken hata oluştu: ' + result.message);
        }
    } catch (error) {
        showError('Kampanyalar yüklenirken hata oluştu: ' + error.message);
    }
}

// Display campaigns in table
function displayCampaigns() {
    const tbody = document.getElementById('campaignsTableBody');
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    
    if (campaigns.length === 0) {
        loadingState.classList.add('hidden');
        emptyState.classList.remove('hidden');
        return;
    }
    
    loadingState.classList.add('hidden');
    emptyState.classList.add('hidden');
    
    tbody.innerHTML = campaigns.map(campaign => `
        <tr class="hover:bg-gray-800/30 transition-colors duration-200">
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-12 w-12">
                        <img class="h-12 w-12 rounded-xl object-cover border border-gray-600" src="${campaign.image_url || '/images/default-campaign.png'}" alt="" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBmaWxsPSIjMzc0MTUxIi8+CjxwYXRoIGQ9Ik0yMCAxMkMxNi42ODYzIDEyIDEzLjYyNjEgMTMuNzQwOSAxMiAxNi41QzEzLjYyNjEgMTkuMjU5MSAxNi42ODYzIDIxIDIwIDIxQzIzLjMxMzcgMjEgMjYuMzczOSAxOS4yNTkxIDI4IDE2LjVDMjYuMzczOSAxMy43NDA5IDIzLjMxMzcgMTIgMjAgMTJaIiBmaWxsPSIjNkI3Mjg4Ii8+CjxwYXRoIGQ9Ik0zMiAyOEMzMCAyNC42ODYzIDI2LjMxMzcgMjIgMjIgMjJIMThDMTMuNjg2MyAyMiAxMCAyNC42ODYzIDEwIDI4IiBmaWxsPSIjNkI3Mjg4Ii8+Cjwvc3ZnPg=='">
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-semibold text-white">${campaign.title}</div>
                        <div class="text-sm text-gray-400">${campaign.description.substring(0, 50)}${campaign.description.length > 50 ? '...' : ''}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4">
                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-purple-glow/20 text-purple-glow border border-purple-glow/30">
                    ${campaign.category}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-300">
                ${campaign.discount}
            </td>
            <td class="px-6 py-4">
                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full ${campaign.is_active ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30'}">
                    ${campaign.is_active ? 'Aktif' : 'Pasif'}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-400">
                ${campaign.end_date ? new Date(campaign.end_date).toLocaleDateString('tr-TR') : 'Sürekli'}
            </td>
            <td class="px-6 py-4 text-sm font-medium">
                <button onclick="editCampaign(${campaign.id})" class="text-purple-glow hover:text-neon-purple mr-3 transition-colors duration-200">Düzenle</button>
                <button onclick="deleteCampaign(${campaign.id})" class="text-red-400 hover:text-red-300 transition-colors duration-200">Sil</button>
            </td>
        </tr>
    `).join('');
}

// Open create modal
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Yeni Kampanya';
    document.getElementById('campaignForm').reset();
    document.getElementById('campaignId').value = '';
    currentCampaignId = null;
    document.getElementById('campaignModal').classList.remove('hidden');
    switchTab('manual'); // Open manual tab by default
}

// Open edit modal
function editCampaign(id) {
    const campaign = campaigns.find(c => c.id === id);
    if (!campaign) return;
    
    currentCampaignId = id;
    document.getElementById('modalTitle').textContent = 'Kampanya Düzenle';
    
    // Fill form fields
    document.getElementById('campaignId').value = campaign.id;
    document.getElementById('title').value = campaign.title;
    document.getElementById('description').value = campaign.description;
    document.getElementById('category').value = campaign.category;
    document.getElementById('discount').value = campaign.discount || '';
    document.getElementById('discount_type').value = campaign.discount_type;
    document.getElementById('discount_value').value = campaign.discount_value || '';
    document.getElementById('valid_until').value = campaign.valid_until ? campaign.valid_until.slice(0, 10) : '';
    document.getElementById('start_date').value = campaign.start_date ? campaign.start_date.slice(0, 16) : '';
    document.getElementById('end_date').value = campaign.end_date ? campaign.end_date.slice(0, 16) : '';
    document.getElementById('minimum_order_amount').value = campaign.minimum_order_amount || '';
    document.getElementById('max_usage').value = campaign.max_usage || '';
    document.getElementById('image_url').value = campaign.image_url || '';
    document.getElementById('terms_conditions').value = campaign.terms_conditions || '';
    document.getElementById('is_active').checked = campaign.is_active;
    
    document.getElementById('campaignModal').classList.remove('hidden');
    switchTab('manual'); // Ensure manual tab is active for editing
}

// Close modal
function closeModal() {
    document.getElementById('campaignModal').classList.add('hidden');
}

// Handle form submission
document.getElementById('campaignForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Convert checkbox value
    data.is_active = formData.get('is_active') === 'on';
    
    try {
        const url = currentCampaignId ? `/api/dashboard/campaigns/${currentCampaignId}` : '/api/dashboard/campaigns';
        const method = currentCampaignId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeModal();
            loadCampaigns();
            showSuccess(currentCampaignId ? 'Kampanya güncellendi' : 'Kampanya oluşturuldu');
        } else {
            showError('Hata: ' + result.message);
        }
    } catch (error) {
        showError('İşlem sırasında hata oluştu: ' + error.message);
    }
});

// Delete campaign
function deleteCampaign(id) {
    currentCampaignId = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

// Close delete modal
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    currentCampaignId = null;
}

// Confirm delete
async function confirmDelete() {
    try {
        const response = await fetch(`/api/dashboard/campaigns/${currentCampaignId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeDeleteModal();
            loadCampaigns();
            showSuccess('Kampanya silindi');
        } else {
            showError('Hata: ' + result.message);
        }
    } catch (error) {
        showError('İşlem sırasında hata oluştu: ' + error.message);
    }
}

// Utility functions
function showLoading() {
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('emptyState').classList.add('hidden');
}

function showSuccess(message) {
    // You can implement a toast notification here
    alert(message);
}

function showError(message) {
    // You can implement a toast notification here
    alert('Hata: ' + message);
}

// Tab switching functions
function switchTab(tab) {
    const aiForm = document.getElementById('aiForm');
    const manualForm = document.getElementById('manualForm');
    const aiTab = document.getElementById('aiTab');
    const manualTab = document.getElementById('manualTab');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const generateBtn = document.getElementById('generateBtn');

    if (tab === 'ai') {
        aiForm.classList.remove('hidden');
        manualForm.classList.add('hidden');
        aiTab.classList.add('bg-purple-600', 'text-white');
        manualTab.classList.remove('bg-purple-600', 'text-white');
        aiTab.classList.add('text-gray-300');
        manualTab.classList.add('text-gray-300');
        prevBtn.classList.remove('hidden');
        nextBtn.classList.remove('hidden');
        generateBtn.classList.remove('hidden');
        currentStep = 1;
        resetAIForm();
    } else {
        aiForm.classList.add('hidden');
        manualForm.classList.remove('hidden');
        aiTab.classList.remove('bg-purple-600', 'text-white');
        manualTab.classList.add('bg-purple-600', 'text-white');
        aiTab.classList.add('text-gray-300');
        manualTab.classList.add('text-gray-300');
        prevBtn.classList.add('hidden');
        nextBtn.classList.add('hidden');
        generateBtn.classList.add('hidden');
    }
}

// Reset AI form to initial state
function resetAIForm() {
    currentStep = 1;
    document.getElementById('step1').classList.remove('hidden');
    document.getElementById('step2').classList.add('hidden');
    document.getElementById('step3').classList.add('hidden');
    document.getElementById('prevBtn').classList.add('hidden');
    document.getElementById('nextBtn').classList.remove('hidden');
    document.getElementById('nextBtn').textContent = 'Sonraki →';
    document.getElementById('productList').innerHTML = '';
    document.getElementById('aiSuggestions').innerHTML = '';
    document.getElementById('productSettingsContainer').innerHTML = '';
    productSettings = {};
}



// Product filtering and loading
async function filterProducts() {
    const productCategory = document.getElementById('productCategory').value;
    const stockFilter = document.getElementById('stockFilter').value;
    const productList = document.getElementById('productList');

    productList.innerHTML = '<div class="text-center"><div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-purple-500"></div><p class="text-gray-400 mt-2">Ürünler yükleniyor...</p></div>';

    if (!productCategory) {
        productList.innerHTML = '<p class="text-gray-400 text-center">Lütfen bir kategori seçin.</p>';
        return;
    }

    try {
        const response = await fetch(`/dashboard/campaigns/products/list?category=${productCategory}&stock_status=${stockFilter}`, {
            headers: {
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        if (result.success) {
            if (result.data.length === 0) {
                productList.innerHTML = '<p class="text-gray-400 text-center">Bu kategori ve stok durumunda ürün bulunamadı.</p>';
            } else {
                productList.innerHTML = result.data.map(product => `
                    <div class="flex items-center space-x-3 p-3 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors duration-200">
                        <input type="checkbox" 
                               value="${product.id}" 
                               name="product_ids[]" 
                               class="form-checkbox h-4 w-4 text-purple-600 bg-gray-800 border-gray-600 rounded focus:ring-purple-500 focus:ring-2">
                        <div class="flex-1">
                            <label class="text-sm font-medium text-white cursor-pointer">${product.name}</label>
                            <div class="text-xs text-gray-400">
                                Kategori: ${product.category ? product.category.name : 'Genel'} | Fiyat: ${product.price} TL | Stok: ${product.stock || 0}
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        } else {
            productList.innerHTML = '<p class="text-red-400 text-center">Ürünler yüklenirken hata oluştu: ' + result.message + '</p>';
        }
    } catch (error) {
        productList.innerHTML = '<p class="text-red-400 text-center">Ürünler yüklenirken hata oluştu: ' + error.message + '</p>';
    }
}

// AI Suggestions generation
async function generateAISuggestions() {
    const selectedProducts = Array.from(document.querySelectorAll('#productList input[name="product_ids[]"]:checked')).map(input => input.value);
    
    if (selectedProducts.length === 0) {
        showError('Lütfen en az bir ürün seçin.');
        return;
    }

    // Her ürün için ayarları kontrol et
    let hasError = false;
    for (const productId of selectedProducts) {
        if (!productSettings[productId] || 
            !productSettings[productId].salePrice || 
            !productSettings[productId].profitMargin || 
            !productSettings[productId].stockQuantity) {
            showError(`Lütfen ${productId} ID'li ürün için tüm kampanya ayarlarını doldurun.`);
            hasError = true;
            break;
        }
    }
    
    if (hasError) {
        return;
    }

    const aiSuggestions = document.getElementById('aiSuggestions');
    aiSuggestions.innerHTML = '<div class="text-center"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-500"></div><p class="text-gray-400 mt-2">AI önerileri oluşturuluyor...</p></div>';

    try {
        const response = await fetch('/dashboard/campaigns/ai-suggestions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                product_ids: selectedProducts,
                product_settings: productSettings,
                season: 'all' // Genel sezon kullan
            })
        });
        const result = await response.json();

        if (result.success) {
            currentAISuggestions = result.data; // Store suggestions for later use
            displayAISuggestions(result.data, selectedProducts);
        } else {
            aiSuggestions.innerHTML = '<p class="text-red-400 text-center">AI önerileri oluşturulurken hata oluştu: ' + result.message + '</p>';
        }
    } catch (error) {
        aiSuggestions.innerHTML = '<p class="text-red-400 text-center">AI önerileri oluşturulurken hata oluştu: ' + error.message + '</p>';
    }
}

// Display AI suggestions
function displayAISuggestions(suggestions, selectedProducts) {
    const aiSuggestions = document.getElementById('aiSuggestions');
    
    if (!suggestions.suggestions || suggestions.suggestions.length === 0) {
        aiSuggestions.innerHTML = '<p class="text-gray-400 text-center">AI önerisi bulunamadı.</p>';
        return;
    }

    aiSuggestions.innerHTML = `
        <div class="mb-4 p-4 bg-gray-800 rounded-lg border border-gray-600">
            <h5 class="text-lg font-semibold text-white mb-2">📊 AI Analiz Özeti</h5>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-400">Toplam Öneri:</span>
                    <span class="text-white ml-2">${suggestions.summary?.total_suggestions || suggestions.suggestions.length}</span>
                </div>
                <div>
                    <span class="text-gray-400">En İyi Tür:</span>
                    <span class="text-white ml-2">${suggestions.summary?.best_campaign || 'N/A'}</span>
                </div>
                <div>
                    <span class="text-gray-400">Tahmini Gelir:</span>
                    <span class="text-white ml-2">${suggestions.summary?.estimated_revenue || 'N/A'}</span>
                </div>
                <div>
                    <span class="text-gray-400">Risk Seviyesi:</span>
                    <span class="text-white ml-2">${suggestions.summary?.risk_level || 'N/A'}</span>
                </div>
            </div>
        </div>
        
        <div class="space-y-4">
            ${suggestions.suggestions.map((suggestion, index) => `
                <div class="bg-gray-800 p-4 rounded-lg border border-gray-600 hover:border-purple-500 transition-colors duration-200">
                    <div class="flex items-start justify-between mb-3">
                        <h5 class="text-lg font-semibold text-white">${suggestion.title}</h5>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-400 border border-green-500/30">
                                ${suggestion.confidence_score || 'N/A'}% Güven
                            </span>
                            <input type="checkbox" name="selected_suggestions[]" value="${index}" class="form-checkbox h-4 w-4 text-purple-600 bg-gray-800 border-gray-600 focus:ring-purple-500 focus:ring-2">
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-300 mb-3">${suggestion.description}</p>
                    
                    <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                        <div>
                            <span class="text-gray-400">Kampanya Türü:</span>
                            <span class="text-white ml-2">${suggestion.campaign_type || 'N/A'}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">İndirim:</span>
                            <span class="text-white ml-2">${suggestion.discount_type || 'N/A'}: ${suggestion.discount_value || 'N/A'}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Geçerlilik:</span>
                            <span class="text-white ml-2">${suggestion.validity_days || 'N/A'} gün</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Min. Sipariş:</span>
                            <span class="text-white ml-2">${suggestion.minimum_order || 'N/A'} TL</span>
                        </div>
                    </div>
                    
                    <div class="text-sm text-gray-400 mb-3">
                        <strong>Şartlar:</strong> ${suggestion.terms || 'Belirtilmemiş'}
                    </div>
                    
                    <div class="text-sm text-gray-400">
                        <strong>Hedef Kitle:</strong> ${suggestion.target_audience || 'N/A'} | 
                        <strong>Beklenen Etki:</strong> ${suggestion.expected_impact || 'N/A'}
                    </div>
                </div>
            `).join('')}
        </div>
        
        <div class="mt-6 text-center">
            <button onclick="createCampaignFromAI()" class="px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg text-white font-semibold hover:from-green-600 hover:to-emerald-600 transition-all duration-300 transform hover:scale-105">
                🚀 Seçili Önerileri Kampanya Olarak Oluştur
            </button>
        </div>
    `;
}

// Create campaign from AI suggestion
async function createCampaignFromAI() {
    const selectedSuggestionIndexes = Array.from(document.querySelectorAll('input[name="selected_suggestions[]"]:checked')).map(input => input.value);
    const selectedProducts = Array.from(document.querySelectorAll('#productList input[name="product_ids[]"]:checked')).map(input => input.value);

    if (selectedSuggestionIndexes.length === 0) {
        showError('Lütfen en az bir kampanya önerisi seçin.');
        return;
    }

    if (selectedProducts.length === 0) {
        showError('Lütfen en az bir ürün seçin.');
        return;
    }

    try {
        // Birden fazla kampanya oluştur
        const selectedSuggestions = selectedSuggestionIndexes.map(index => currentAISuggestions.suggestions[index]);
        
        try {
            const response = await fetch('/dashboard/campaigns/create-multiple-from-ai', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    suggestions: selectedSuggestions,
                    selected_products: selectedProducts,
                    site_id: 1
                })
            });
            const result = await response.json();

            if (result.success) {
                showSuccess(result.message);
                closeModal();
                loadCampaigns(); // Refresh campaign list
            } else {
                showError('Kampanyalar oluşturulurken hata oluştu: ' + result.message);
            }
        } catch (error) {
            showError('Kampanyalar oluşturulurken hata oluştu: ' + error.message);
        }


    } catch (error) {
        showError('Kampanya oluşturulurken hata oluştu: ' + error.message);
    }
}

// Product settings storage
let productSettings = {};

// Create product settings for each selected product
function createProductSettings(selectedProducts) {
    const container = document.getElementById('productSettingsContainer');
    container.innerHTML = '';
    
    selectedProducts.forEach((productId, index) => {
        const productElement = document.querySelector(`#productList input[value="${productId}"]`).closest('.flex');
        const productName = productElement.querySelector('label').textContent;
        
        const productSettingsHtml = `
            <div class="bg-gray-800 p-4 rounded-lg border border-gray-600" data-product-id="${productId}">
                <h5 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <span class="w-6 h-6 bg-purple-500 rounded-full flex items-center justify-center text-sm font-bold mr-3">${index + 1}</span>
                    ${productName}
                </h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="salePrice_${productId}" class="block text-sm font-medium text-gray-300 mb-2">Satış Fiyatı (TL)</label>
                        <input type="number" id="salePrice_${productId}" step="0.01" min="0" class="form-input w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" placeholder="0" onchange="updateProductSetting('${productId}', 'salePrice', this.value)">
                    </div>
                    <div>
                        <label for="profitMargin_${productId}" class="block text-sm font-medium text-gray-300 mb-2">Kar Oranı (%)</label>
                        <input type="number" id="profitMargin_${productId}" step="0.1" min="0" max="100" class="form-input w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" placeholder="20" onchange="updateProductSetting('${productId}', 'profitMargin', this.value)">
                    </div>
                    <div>
                        <label for="stockQuantity_${productId}" class="block text-sm font-medium text-gray-300 mb-2">Stok Miktarı</label>
                        <input type="number" id="stockQuantity_${productId}" min="0" class="form-input w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" placeholder="100" onchange="updateProductSetting('${productId}', 'stockQuantity', this.value)">
                    </div>
                    <div>
                        <label for="season_${productId}" class="block text-sm font-medium text-gray-300 mb-2">Sezon</label>
                        <select id="season_${productId}" class="form-input w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" onchange="updateProductSetting('${productId}', 'season', this.value)">
                            <option value="spring">İlkbahar</option>
                            <option value="summer">Yaz</option>
                            <option value="autumn">Sonbahar</option>
                            <option value="winter">Kış</option>
                            <option value="all">Tüm Sezon</option>
                        </select>
                    </div>
                </div>
            </div>
        `;
        
        container.innerHTML += productSettingsHtml;
        
        // Default değerleri ayarla
        productSettings[productId] = {
            salePrice: 0,
            profitMargin: 20,
            stockQuantity: 100,
            season: 'spring'
        };
    });
}

// Update product setting value
function updateProductSetting(productId, field, value) {
    if (!productSettings[productId]) {
        productSettings[productId] = {};
    }
    productSettings[productId][field] = value;
}

// Step navigation
let currentStep = 1;

function nextStep() {
    if (currentStep === 1) {
        const selectedProducts = document.querySelectorAll('#productList input[name="product_ids[]"]:checked');
        if (selectedProducts.length === 0) {
            showError('Lütfen en az bir ürün seçin.');
            return;
        }
        
        // Her seçilen ürün için ayrı ayar alanları oluştur
        createProductSettings(selectedProducts);
        
        currentStep = 2;
        document.getElementById('step1').classList.add('hidden');
        document.getElementById('step2').classList.remove('hidden');
        document.getElementById('prevBtn').classList.remove('hidden');
        document.getElementById('nextBtn').textContent = 'Sonraki →';
    } else if (currentStep === 2) {
        // Her ürün için ayarları kontrol et
        const selectedProducts = Array.from(document.querySelectorAll('#productList input[name="product_ids[]"]:checked')).map(input => input.value);
        let hasError = false;
        
        for (const productId of selectedProducts) {
            const salePrice = document.getElementById(`salePrice_${productId}`).value;
            const profitMargin = document.getElementById(`profitMargin_${productId}`).value;
            const stockQuantity = document.getElementById(`stockQuantity_${productId}`).value;
            
            if (!salePrice || !profitMargin || !stockQuantity) {
                showError(`Lütfen ${productId} ID'li ürün için tüm kampanya ayarlarını doldurun.`);
                hasError = true;
                break;
            }
        }
        
        if (hasError) {
            return;
        }
        
        // Direkt AI önerileri oluştur
        currentStep = 3;
        document.getElementById('step2').classList.add('hidden');
        document.getElementById('step3').classList.remove('hidden');
        document.getElementById('nextBtn').classList.add('hidden');
        
        // AI önerileri oluştur
        generateAISuggestions();
    }
}

function previousStep() {
    if (currentStep === 2) {
        currentStep = 1;
        document.getElementById('step2').classList.add('hidden');
        document.getElementById('step1').classList.remove('hidden');
        document.getElementById('prevBtn').classList.add('hidden');
        document.getElementById('nextBtn').textContent = 'Sonraki →';
    } else if (currentStep === 3) {
        currentStep = 2;
        document.getElementById('step3').classList.add('hidden');
        document.getElementById('step2').classList.remove('hidden');
        document.getElementById('nextBtn').classList.remove('hidden');
    }
}


</script>
@endpush
