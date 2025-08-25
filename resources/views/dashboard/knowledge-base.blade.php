@extends('layouts.dashboard')

@section('title', 'Bilgi Tabanı')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="glass-effect rounded-2xl p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-glow rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-neon-purple rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        
        <div class="relative z-10">
            <h1 class="text-4xl font-bold mb-4">
                <span class="gradient-text">Bilgi Tabanı</span>
            </h1>
            <p class="text-xl text-gray-300">
                AI destekli bilgi tabanı sistemi ile dosyalarınızı yükleyin ve akıllı arama yapın
            </p>
        </div>
    </div>



    <!-- New Bilgi Tabanı Creation Container -->
    <div class="glass-effect rounded-2xl p-8">
        <h2 class="text-2xl font-bold mb-6 text-white">Yeni Bilgi Tabanı Oluştur</h2>
        
        <!-- Method Selection Guide -->
        <div class="mb-8 p-6 bg-gray-800/30 rounded-lg border border-gray-700">
            <div class="text-center mb-4">
                <h3 class="text-lg font-semibold text-white mb-2">📋 Bilgi Tabanı Oluşturma Yöntemi Seçin</h3>
                <p class="text-gray-300">Aşağıdaki iki yöntemden birini kullanarak yeni bilgi tabanı oluşturabilirsiniz:</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="p-4 bg-purple-500/10 border border-purple-500/30 rounded-lg text-center hover:bg-purple-500/20 transition-colors duration-300 cursor-pointer" onclick="scrollToSection('file-upload')">
                    <div class="text-2xl mb-2">📁</div>
                    <h4 class="font-semibold text-white mb-2">Dosya Yükleme</h4>
                    <p class="text-sm text-gray-300">Bilgisayarınızdan dosya seçin ve yükleyin</p>
                    <div class="mt-3 text-xs text-purple-300">→ Tıklayın</div>
                </div>
                
                <div class="p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg text-center hover:bg-blue-500/20 transition-colors duration-300 cursor-pointer" onclick="scrollToSection('url-fetch')">
                    <div class="text-2xl mb-2">🌐</div>
                    <h4 class="font-semibold text-white mb-2">URL ile İçerik Çekme</h4>
                    <p class="text-sm text-gray-300">Web'den dosya URL'si ile içerik çekin</p>
                    <div class="mt-3 text-xs text-blue-300">→ Tıklayın</div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- File Upload Section -->
            <div id="file-upload" class="lg:col-span-1">
                <h3 class="text-lg font-semibold mb-4 text-white">Dosya Yükleme</h3>
                
                <!-- Upload Area -->
                <div id="upload-area" class="border-2 border-dashed border-gray-600 rounded-2xl p-8 text-center hover:border-purple-glow transition-colors duration-300 cursor-pointer">
                    <div class="space-y-4">
                        <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <div>
                            <p class="text-lg font-semibold text-white mb-2">Dosya seçin veya sürükleyin</p>
                            <p class="text-gray-400 mb-4">Desteklenen formatlar: CSV, TXT, XML, JSON, Excel</p>
                            <p class="text-sm text-gray-500">Maksimum dosya boyutu: 10MB</p>
                        </div>
                        <button id="select-file-btn" class="px-6 py-3 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg text-white font-semibold hover:from-purple-dark hover:to-neon-purple transition-all duration-300 transform hover:scale-105">
                            Dosya Seç
                        </button>
                    </div>
                </div>

                <!-- Hidden File Input -->
                <input type="file" id="file-input" accept=".csv,.txt,.xml,.json,.xlsx,.xls" class="hidden">
                
                <!-- Upload Progress -->
                <div id="upload-progress" class="hidden mt-6">
                    <div class="flex items-center space-x-3 mb-2">
                        <div class="w-4 h-4 border-2 border-purple-glow border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-purple-glow">Dosya yükleniyor ve işleniyor...</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div id="progress-bar" class="bg-gradient-to-r from-purple-glow to-neon-purple h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <!-- URL Fetch Section -->
            <div id="url-fetch" class="lg:col-span-2 flex items-center justify-center">
                <div class="w-full max-w-2xl">
                    <h3 class="text-lg font-semibold mb-4 text-white text-center">URL ile İçerik Çekme</h3>
                    
                    <div class="space-y-4">
                        <div class="flex flex-col space-y-3">
                            <input type="text" id="kb-name" placeholder="Bilgi Tabanı Adı" class="w-full px-4 py-3 bg-gray-800/50 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20 text-center">
                            <input type="url" id="url-input" placeholder="https://example.com/data.csv" class="w-full px-4 py-3 bg-gray-800/50 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20 text-center">
                            <button id="fetch-url-btn" class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-lg text-white font-semibold hover:from-blue-600 hover:to-cyan-600 transition-all duration-300 transform hover:scale-105">
                                İçerik Çek
                            </button>
                        </div>
                        
                        <p class="text-sm text-gray-400 text-center">
                            CSV, TXT, XML, JSON veya Excel dosyalarının URL'lerini girin. İçerik otomatik olarak chunk'lara bölünecek ve AI ile işlenecektir.
                        </p>
                    </div>
                    
                    <!-- URL Fetch Progress -->
                    <div id="url-fetch-progress" class="hidden mt-6">
                        <div class="flex items-center justify-center space-x-3 mb-2">
                            <div class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-blue-400">URL'den içerik çekiliyor ve işleniyor...</span>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div id="url-progress-bar" class="bg-gradient-to-r from-blue-500 to-cyan-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bilgi Tabanı Detay Modal -->
        <div id="kbDetailModal" class="fixed inset-0 bg-black bg-opacity-85 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50" onclick="closeKBDetailModal()">
            <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 p-6 border border-gray-700 w-4/5 h-4/5 shadow-2xl rounded-xl glass-effect overflow-y-auto custom-scrollbar" onclick="event.stopPropagation()">
                <div class="flex justify-between items-center mb-6">
                                            <h3 class="text-2xl font-bold text-white" id="modalTitle">Bilgi Tabanı Detayı</h3>
                    <button onclick="closeKBDetailModal()" class="text-gray-400 hover:text-white transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div id="kbDetailContent" class="space-y-6">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>



    <!-- Results Container -->
    <div id="results-container" class="glass-effect rounded-2xl p-8 hidden">
        <h2 class="text-2xl font-bold mb-6 text-white">İşlem Sonuçları</h2>
        
        <div id="results-content" class="space-y-6">
            <!-- Results will be populated here -->
        </div>
    </div>

    <!-- Chunks Modal -->
    <div id="chunks-modal" class="fixed inset-0 z-50 hidden">
        <!-- Modal Backdrop -->
        <div class="absolute inset-0 bg-black/85 backdrop-blur-sm"></div>
        
        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="w-full max-w-6xl max-h-[90vh] bg-gray-900 rounded-2xl border border-gray-700 shadow-2xl overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-700 bg-gray-800/50">
                    <h2 class="text-2xl font-bold text-white">Bilgi Tabanı Chunk'ları</h2>
                    <button id="close-chunks-modal" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                    <div id="chunks-content" class="space-y-6">
                        <!-- Chunks will be populated here -->
                    </div>
                </div>
                
                <!-- Modal Footer with Pagination -->
                <div id="chunks-pagination" class="flex items-center justify-between p-6 border-t border-gray-700 bg-gray-800/50">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-400">Sayfa:</span>
                        <span id="current-page" class="text-white font-medium">1</span>
                        <span class="text-gray-400">/</span>
                        <span id="total-pages" class="text-white font-medium">1</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button id="prev-page-btn" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-white text-sm font-medium transition-colors duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button id="next-page-btn" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-white text-sm font-medium transition-colors duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <!-- Bilgi Tabanı Listesi -->
    @if(isset($knowledgeBases) && $knowledgeBases->count() > 0)
    <div class="glass-effect rounded-2xl p-8">
        <h2 class="text-2xl font-bold mb-6 text-white">Mevcut Bilgi Tabanları</h2>
        
        <!-- Search Container -->
        <div class="mb-8 p-6 bg-gray-800/30 rounded-lg border border-gray-700">
            <h3 class="text-lg font-semibold mb-4 text-white">Bilgi Tabanı Arama</h3>
            
            <div class="space-y-4">
                <div class="flex space-x-4">
                    <input type="text" id="search-query" placeholder="Ürün arama, kategori bilgisi, yardım..." class="flex-1 px-4 py-3 bg-gray-800/50 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20">
                    <button id="search-btn" class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg text-white font-semibold hover:from-green-600 hover:to-emerald-600 transition-all duration-300 transform hover:scale-105">
                        AI ile Ara
                    </button>
                </div>
                
                <p class="text-sm text-gray-400">
                    AI destekli intent detection ile bilgi tabanında arama yapın. Sistem otomatik olarak en uygun yanıtı üretecektir.
                </p>
            </div>
            
            <!-- Search Results -->
            <div id="search-results" class="hidden mt-6">
                <div id="search-content" class="space-y-4">
                    <!-- Search results will be populated here -->
                </div>
            </div>
        </div>
        
        <div class="grid gap-4">
            @foreach($knowledgeBases as $kb)
            <div class="p-6 bg-gray-800/30 rounded-lg border border-gray-700 hover:border-purple-glow transition-colors duration-300 cursor-pointer" onclick="viewKnowledgeBaseDetail({{ $kb->id }})">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-white mb-2">{{ $kb->name }}</h3>
                        @if($kb->description)
                            <p class="text-gray-300 text-sm mb-3">{{ $kb->description }}</p>
                        @endif
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-400">Kaynak:</span>
                                <span class="text-white ml-2">{{ ucfirst($kb->source_type) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400">Format:</span>
                                <span class="text-white ml-2">{{ strtoupper($kb->file_type ?? 'N/A') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400">Chunk Sayısı:</span>
                                <span class="text-white ml-2">{{ $kb->chunk_count }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400">Durum:</span>
                                <span class="ml-2 px-2 py-1 rounded-full text-xs font-medium
                                    @if($kb->processing_status === 'completed') bg-green-500/20 text-green-300
                                    @elseif($kb->processing_status === 'processing') bg-yellow-500/20 text-yellow-300
                                    @elseif($kb->processing_status === 'failed') bg-red-500/20 text-red-300
                                    @else bg-gray-500/20 text-gray-300
                                    @endif">
                                    {{ ucfirst($kb->processing_status) }}
                                </span>
                            </div>
                        </div>
                        
                        @if($kb->processing_status === 'completed')
                        <div class="mt-4 flex space-x-3">
                            <button onclick="searchKnowledgeBase({{ $kb->id }})" class="px-4 py-2 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg text-white text-sm font-medium hover:from-purple-dark hover:to-neon-purple transition-all duration-300">
                                Arama Yap
                            </button>
                            <button onclick="viewChunks({{ $kb->id }})" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-white text-sm font-medium transition-colors duration-300">
                                Chunk'ları Gör
                            </button>
                            <button onclick="openFieldDetectionModal({{ $kb->id }})" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded-lg text-white text-sm font-medium transition-colors duration-300">
                                Field Mapping
                            </button>
                        </div>
                        @endif
                    </div>
                    
                    <div class="flex flex-col items-end space-y-2">
                        <span class="text-xs text-gray-500">{{ $kb->created_at->format('d.m.Y H:i') }}</span>
                        <button onclick="deleteKnowledgeBase({{ $kb->id }})" class="text-red-400 hover:text-red-300 transition-colors duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<script>
// Scroll to section function
function scrollToSection(sectionId) {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
        
        // Add highlight effect
        element.style.transition = 'all 0.3s ease';
        element.style.boxShadow = '0 0 20px rgba(139, 92, 246, 0.5)';
        element.style.borderColor = '#8B5CF6';
        
        setTimeout(() => {
            element.style.boxShadow = '';
            element.style.borderColor = '';
        }, 2000);
    }
}

// Debug function
function debugLog(message) {
    console.log(`[DEBUG] ${message}`);
    // Optionally show in UI
    const debugDiv = document.getElementById('debug-log');
    if (debugDiv) {
        debugDiv.innerHTML += `<div>[${new Date().toLocaleTimeString()}] ${message}</div>`;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // ESC key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeKBDetailModal();
        }
    });
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('file-input');
    const selectFileBtn = document.getElementById('select-file-btn');
    const uploadProgress = document.getElementById('upload-progress');
    const progressBar = document.getElementById('progress-bar');
    const resultsContainer = document.getElementById('results-container');
    const resultsContent = document.getElementById('results-content');
    
    // Chunks elements
    const chunksModal = document.getElementById('chunks-modal');
    const chunksContent = document.getElementById('chunks-content');
    const closeChunksModal = document.getElementById('close-chunks-modal');
    const currentPageSpan = document.getElementById('current-page');
    const totalPagesSpan = document.getElementById('total-pages');
    const prevPageBtn = document.getElementById('prev-page-btn');
    const nextPageBtn = document.getElementById('next-page-btn');
    
    // URL fetch elements
    const kbNameInput = document.getElementById('kb-name');
    const urlInput = document.getElementById('url-input');
    const fetchUrlBtn = document.getElementById('fetch-url-btn');
    const urlFetchProgress = document.getElementById('url-fetch-progress');
    const urlProgressBar = document.getElementById('url-progress-bar');
    
    // Search elements
    const searchQuery = document.getElementById('search-query');
    const searchBtn = document.getElementById('search-btn');
    const searchResults = document.getElementById('search-results');
    const searchContent = document.getElementById('search-content');

    // File selection - Event listener'ları düzelt
    if (selectFileBtn) {
        selectFileBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (fileInput) {
                fileInput.click();
            } else {
                alert('Dosya seçimi için gerekli element bulunamadı');
            }
        });
    }
    
    if (uploadArea) {
        uploadArea.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (fileInput) {
                fileInput.click();
            } else {
                alert('Dosya seçimi için gerekli element bulunamadı');
            }
        });
    }

    // URL fetch - Event listener'ları düzelt
    if (fetchUrlBtn) {
        fetchUrlBtn.addEventListener('click', () => {
            handleUrlFetch();
        });
    }
    
    if (urlInput) {
        urlInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                handleUrlFetch();
            }
        });
    }

    // Search - Event listener'ları düzelt
    if (searchBtn) {
        searchBtn.addEventListener('click', () => {
            handleSearch();
        });
    }
    
    if (searchQuery) {
        searchQuery.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                handleSearch();
            }
        });
    }

    // Chunks modal close button
    if (closeChunksModal) {
        closeChunksModal.addEventListener('click', () => {
            if (chunksModal) {
                chunksModal.classList.add('hidden');
            }
        });
    }

    // Drag and drop
    if (uploadArea) {
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('border-purple-glow');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('border-purple-glow');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('border-purple-glow');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileUpload(files[0]);
            }
        });
    }

    // File input change
    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                const selectedFile = e.target.files[0];
                handleFileUpload(selectedFile);
            }
        });
    }

    function handleUrlFetch() {
        debugLog('handleUrlFetch fonksiyonu çağrıldı');
        
        if (!kbNameInput || !urlInput) {
            debugLog('ERROR: kbNameInput veya urlInput bulunamadı');
            alert('URL işlemi için gerekli elementler bulunamadı');
            return;
        }
        
        const name = kbNameInput.value.trim();
        const url = urlInput.value.trim();
        
        debugLog(`Knowledge base adı: "${name}"`);
        debugLog(`URL: "${url}"`);
        
        if (!name) {
            debugLog('Hata: Knowledge base adı boş');
            alert('Lütfen bilgi tabanı adı girin');
            return;
        }
        
        if (!url) {
            debugLog('Hata: URL boş');
            alert('Lütfen geçerli bir URL girin');
            return;
        }

        if (!isValidUrl(url)) {
            debugLog('Hata: Geçersiz URL formatı');
            alert('Lütfen geçerli bir URL formatı girin (örn: https://example.com/data.csv)');
            return;
        }

        // Show progress
        if (urlFetchProgress) {
            urlFetchProgress.classList.remove('hidden');
        }
        if (resultsContainer) {
            resultsContainer.classList.add('hidden');
        }
        
        // Simulate progress
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 20;
            if (progress > 85) progress = 85;
            if (urlProgressBar) {
                urlProgressBar.style.width = progress + '%';
            }
        }, 300);

        // Create form data
        const formData = new FormData();
        formData.append('name', name);
        formData.append('url', url);
        
        // CSRF token'ı güvenli şekilde al
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            formData.append('_token', csrfToken.getAttribute('content'));
            debugLog(`CSRF token alındı: ${csrfToken.getAttribute('content')}`);
        } else {
            debugLog('ERROR: CSRF token bulunamadı');
            alert('Güvenlik token\'ı bulunamadı. Sayfayı yenileyin.');
            return;
        }

        debugLog('URL fetch API çağrısı yapılıyor...');

        // Fetch from URL
        fetch('/api/knowledge-base/fetch-url', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            debugLog(`API response status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            debugLog('API response alındı:', data);
            clearInterval(progressInterval);
            if (urlProgressBar) {
                urlProgressBar.style.width = '100%';
            }
            
            setTimeout(() => {
                if (urlFetchProgress) {
                    urlFetchProgress.classList.add('hidden');
                }
                if (data.success) {
                    showResults(data);
                    // Reload page to show new knowledge base
                    setTimeout(() => location.reload(), 2000);
                } else {
                    debugLog(`API Error: ${data.message}`);
                    alert('Hata: ' + data.message);
                }
            }, 500);
        })
        .catch(error => {
            debugLog(`API Error: ${error.message}`);
            clearInterval(progressInterval);
            if (urlFetchProgress) {
                urlFetchProgress.classList.add('hidden');
            }
            alert('URL\'den içerik çekilirken hata oluştu: ' + error.message);
        });
    }

    function handleSearch() {
        debugLog('handleSearch fonksiyonu çağrıldı');
        
        if (!searchQuery) {
            debugLog('ERROR: searchQuery bulunamadı');
            alert('Arama için gerekli element bulunamadı');
            return;
        }
        
        const query = searchQuery.value.trim();
        debugLog(`Arama sorgusu: "${query}"`);
        
        if (!query) {
            debugLog('Hata: Arama sorgusu boş');
            alert('Lütfen arama sorgusu girin');
            return;
        }

        // Show loading
        if (searchResults) {
            searchResults.classList.remove('hidden');
        }
        if (searchContent) {
            searchContent.innerHTML = `
                <div class="flex items-center space-x-3">
                    <div class="w-4 h-4 border-2 border-green-500 border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-green-400">AI ile arama yapılıyor...</span>
                </div>
            `;
        }

        debugLog('Arama API çağrısı yapılıyor...');

        // Create form data
        const formData = new FormData();
        formData.append('query', query);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        // Search
        fetch('/api/knowledge-base/search', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            debugLog(`Search API response status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            debugLog('Search API response alındı:', data);
            if (data.success) {
                showSearchResults(data);
            } else {
                debugLog(`Search API Error: ${data.message}`);
                if (searchContent) {
                    searchContent.innerHTML = `
                        <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-lg">
                            <p class="text-red-400">Hata: ${data.message}</p>
                        </div>
                    `;
                }
            }
        })
        .catch(error => {
            debugLog(`Search API Error: ${error.message}`);
            if (searchContent) {
                searchContent.innerHTML = `
                    <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-lg">
                        <p class="text-red-400">Arama yapılırken hata oluştu: ${error.message}</p>
                    </div>
                `;
            }
        });
    }

    function showSearchResults(data) {
        debugLog('showSearchResults fonksiyonu çağrıldı:', data);
        
        if (!searchContent) {
            debugLog('ERROR: searchContent bulunamadı');
            return;
        }
        
        let html = '';
        
        // Intent info
        if (data.intent) {
            html += `
                <div class="p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg mb-4">
                    <h4 class="text-lg font-semibold text-blue-400 mb-2">Tespit Edilen Intent</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-400">Intent:</span>
                            <span class="text-white ml-2">${data.intent.intent || 'N/A'}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Güven:</span>
                            <span class="text-white ml-2">${data.intent.confidence ? (data.intent.confidence * 100).toFixed(1) + '%' : 'N/A'}</span>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // AI Response
        if (data.response) {
            html += `
                <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-lg mb-4">
                    <h4 class="text-lg font-semibold text-green-400 mb-2">AI Yanıtı</h4>
                    <p class="text-white">${data.response}</p>
                </div>
            `;
        }
        
        // Suggestions
        if (data.suggestions && data.suggestions.length > 0) {
            html += `
                <div class="p-4 bg-purple-500/10 border border-purple-500/30 rounded-lg mb-4">
                    <h4 class="text-lg font-semibold text-purple-400 mb-2">Öneriler</h4>
                    <ul class="space-y-2">
                        ${data.suggestions.map(suggestion => `<li class="text-white">• ${suggestion}</li>`).join('')}
                    </ul>
                </div>
            `;
        }
        
        // Used chunks
        if (data.chunks && data.chunks.length > 0) {
            html += `
                <div class="p-4 bg-gray-800/30 border border-gray-700 rounded-lg">
                    <h4 class="text-lg font-semibold text-white mb-2">Kullanılan Bilgi Parçaları</h4>
                    <div class="space-y-3">
                        ${data.chunks.map(chunk => `
                            <div class="p-3 bg-gray-900/50 rounded-lg">
                                <div class="text-sm text-gray-400 mb-1">Chunk ${chunk.chunk_index || 'N/A'} (${chunk.content_type || 'N/A'})</div>
                                <div class="text-white text-sm">${chunk.content ? (chunk.content.substring(0, 200) + (chunk.content.length > 200 ? '...' : '')) : 'İçerik bulunamadı'}</div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }
        
        if (html === '') {
            html = `
                <div class="p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                    <p class="text-yellow-400">Arama sonucu bulunamadı veya veri eksik.</p>
                </div>
            `;
        }
        
        searchContent.innerHTML = html;
        debugLog('Arama sonuçları gösterildi');
    }

    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    function handleFileUpload(file) {
        debugLog(`handleFileUpload fonksiyonu çağrıldı: ${file.name}`);
        
        // Validate file type
        const allowedTypes = ['csv', 'txt', 'xml', 'json', 'xlsx', 'xls'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (!allowedTypes.includes(fileExtension)) {
            debugLog(`Hata: Desteklenmeyen dosya formatı: ${fileExtension}`);
            alert('Desteklenmeyen dosya formatı. Lütfen CSV, TXT, XML, JSON veya Excel dosyası seçin.');
            return;
        }

        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            debugLog(`Hata: Dosya boyutu çok büyük: ${(file.size / 1024 / 1024).toFixed(2)}MB`);
            alert('Dosya boyutu çok büyük. Maksimum 10MB olmalıdır.');
            return;
        }

        debugLog(`Dosya validasyonu başarılı: ${fileExtension}, ${(file.size / 1024).toFixed(2)}KB`);

        // Get bilgi tabanı name
        const kbName = prompt('Bilgi Tabanı için bir isim girin:');
        if (!kbName) {
            debugLog('Knowledge base adı girilmedi, işlem iptal edildi');
            return;
        }

        debugLog(`Knowledge base adı: ${kbName}`);

        // Show progress
        if (uploadProgress) {
            uploadProgress.classList.remove('hidden');
        }
        if (resultsContainer) {
            resultsContainer.classList.add('hidden');
        }
        
        // Simulate progress
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            if (progressBar) {
                progressBar.style.width = progress + '%';
            }
        }, 200);

        // Create FormData
        const formData = new FormData();
        formData.append('file', file);
        formData.append('name', kbName);
        
        // CSRF token'ı güvenli şekilde al
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            formData.append('_token', csrfToken.getAttribute('content'));
            debugLog(`CSRF token alındı: ${csrfToken.getAttribute('content')}`);
        } else {
            debugLog('ERROR: CSRF token bulunamadı');
            alert('Güvenlik token\'ı bulunamadı. Sayfayı yenileyin.');
            return;
        }

        debugLog('FormData oluşturuldu:');
        debugLog(`- file: ${file.name} (${file.size} bytes)`);
        debugLog(`- name: ${kbName}`);
        debugLog(`- _token: ${csrfToken.getAttribute('content')}`);

        // Upload file
        fetch('/api/knowledge-base/upload', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            debugLog(`Upload API response status: ${response.status}`);
            debugLog(`Upload API response headers:`, response.headers);
            
            if (!response.ok) {
                // HTTP error handling
                if (response.status === 500) {
                    throw new Error('HTTP 500: Internal Server Error - Sunucu tarafında bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
                } else if (response.status === 413) {
                    throw new Error('HTTP 413: Payload Too Large - Dosya boyutu çok büyük.');
                } else if (response.status === 422) {
                    throw new Error('HTTP 422: Validation Error - Dosya formatı veya içerik geçersiz.');
                } else {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
            }
            
            return response.json();
        })
        .then(data => {
            debugLog('Upload API response alındı:', data);
            clearInterval(progressInterval);
            if (progressBar) {
                progressBar.style.width = '100%';
            }
            
            setTimeout(() => {
                if (uploadProgress) {
                    uploadProgress.classList.add('hidden');
                }
                if (data.success) {
                    showResults(data);
                    // Reload page to show new knowledge base
                    setTimeout(() => location.reload(), 2000);
                } else {
                    debugLog(`Upload API Error: ${data.message}`);
                    alert('Hata: ' + data.message);
                }
            }, 500);
        })
        .catch(error => {
            debugLog(`Upload API Error: ${error.message}`);
            clearInterval(progressInterval);
            if (uploadProgress) {
                uploadProgress.classList.add('hidden');
            }
            
            // Daha detaylı hata mesajı
            let errorMessage = 'Dosya yüklenirken hata oluştu';
            
            if (error.message.includes('HTTP 500')) {
                errorMessage = 'Sunucu hatası: Dosya işlenirken bir hata oluştu. Lütfen daha sonra tekrar deneyin veya farklı bir dosya ile test edin.';
            } else if (error.message.includes('The string did not match the expected pattern')) {
                errorMessage = 'Dosya formatı geçersiz. Lütfen dosyayı kontrol edin ve tekrar deneyin.';
            } else if (error.message.includes('HTTP')) {
                errorMessage = `Sunucu hatası: ${error.message}`;
            } else if (error.message.includes('Failed to fetch')) {
                errorMessage = 'Bağlantı hatası: Sunucuya ulaşılamıyor. İnternet bağlantınızı kontrol edin.';
            } else {
                errorMessage += `: ${error.message}`;
            }
            
            // Hata detaylarını console'da göster
            console.error('Upload Error Details:', error);
            
            // Kullanıcıya hata mesajını göster
            alert(errorMessage);
        });
    }

    function showResults(data) {
        debugLog('showResults fonksiyonu çağrıldı:', data);
        
        if (!resultsContent || !resultsContainer) {
            debugLog('ERROR: resultsContent veya resultsContainer bulunamadı');
            return;
        }
        
        if (data.success) {
            let html = '';

            // Success info
            html += `
                <div class="p-6 bg-green-500/10 border border-green-500/30 rounded-lg mb-6">
                    <h3 class="text-lg font-semibold text-green-400 mb-2">✓ ${data.message || 'İşlem başarılı'}</h3>
                    <div class="grid md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-400">Bilgi Tabanı ID:</span>
                            <span class="text-white ml-2">${data.knowledge_base_id || 'N/A'}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Chunk Sayısı:</span>
                            <span class="text-white ml-2">${data.chunk_count || 'N/A'}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Dosya Adı:</span>
                            <span class="text-white ml-2">${data.file_name || 'N/A'}</span>
                        </div>
                    </div>
                </div>
            `;

            resultsContent.innerHTML = html;
            resultsContainer.classList.remove('hidden');
            debugLog('Sonuçlar başarıyla gösterildi');
        } else {
            debugLog(`Hata: ${data.message || 'Bilinmeyen hata'}`);
            alert('Hata: ' + (data.message || 'Bilinmeyen hata'));
        }
    }

});

// Global displayChunks function - moved outside DOMContentLoaded
function displayChunks(chunks, knowledgeBase, currentPage = 1) {
    // Get elements
    const chunksContent = document.getElementById('chunks-content');
    const currentPageSpan = document.getElementById('current-page');
    const totalPagesSpan = document.getElementById('total-pages');
    const prevPageBtn = document.getElementById('prev-page-btn');
    const nextPageBtn = document.getElementById('next-page-btn');
    
    if (!chunksContent) {
        return;
    }
    
    const chunksPerPage = 5;
    const totalPages = Math.ceil(chunks.length / chunksPerPage);
    const startIndex = (currentPage - 1) * chunksPerPage;
    const endIndex = startIndex + chunksPerPage;
    const pageChunks = chunks.slice(startIndex, endIndex);
    
    // Update pagination info
    if (currentPageSpan) currentPageSpan.textContent = currentPage;
    if (totalPagesSpan) totalPagesSpan.textContent = totalPages;
    
    // Update pagination buttons
    if (prevPageBtn) {
        prevPageBtn.disabled = currentPage <= 1;
        prevPageBtn.classList.toggle('opacity-50', currentPage <= 1);
        prevPageBtn.classList.toggle('cursor-not-allowed', currentPage <= 1);
    }
    if (nextPageBtn) {
        nextPageBtn.disabled = currentPage >= totalPages;
        nextPageBtn.classList.toggle('opacity-50', currentPage >= totalPages);
        nextPageBtn.classList.toggle('cursor-not-allowed', currentPage >= totalPages);
    }
    
    // Add pagination event listeners
    if (prevPageBtn) {
        prevPageBtn.onclick = () => {
            if (currentPage > 1) {
                displayChunks(chunks, knowledgeBase, currentPage - 1);
            }
        };
    }
    if (nextPageBtn) {
        nextPageBtn.onclick = () => {
            if (currentPage < totalPages) {
                displayChunks(chunks, knowledgeBase, currentPage + 1);
            }
        };
    }
    
    // Display chunks
    let html = '';
    
            // Bilgi Tabanı info header
    html += `
        <div class="p-6 bg-blue-500/10 border border-blue-500/30 rounded-lg mb-6">
            <h3 class="text-lg font-semibold text-blue-400 mb-3">${knowledgeBase.name}</h3>
            <div class="grid md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-gray-400">Toplam Chunk:</span>
                    <span class="text-white ml-2">${chunks.length}</span>
                </div>
                <div>
                    <span class="text-gray-400">Dosya Tipi:</span>
                    <span class="text-white ml-2">${knowledgeBase.file_type?.toUpperCase() || 'N/A'}</span>
                </div>
                <div>
                    <span class="text-gray-400">Kaynak:</span>
                    <span class="text-white ml-2">${knowledgeBase.source_type === 'file' ? 'Dosya' : 'URL'}</span>
                </div>
                <div>
                    <span class="text-gray-400">Oluşturulma:</span>
                    <span class="text-white ml-2">${new Date(knowledgeBase.created_at).toLocaleDateString('tr-TR')}</span>
                </div>
            </div>
        </div>
    `;
    
    // Chunks list
    if (pageChunks.length > 0) {
        html += `
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-lg font-semibold text-white">Chunk'lar (Sayfa ${currentPage}/${totalPages})</h4>
                    <span class="text-sm text-gray-400">${startIndex + 1}-${Math.min(endIndex, chunks.length)} / ${chunks.length}</span>
                </div>
        `;
        
        pageChunks.forEach((chunk, index) => {
            const globalIndex = startIndex + index;
            html += `
                <div class="p-6 bg-gray-800/30 rounded-lg border border-gray-700 hover:border-purple-glow transition-colors duration-300">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <span class="px-3 py-1 bg-purple-500/20 text-purple-300 rounded-full text-sm font-medium">
                                Chunk ${chunk.chunk_index || globalIndex + 1}
                            </span>
                            <span class="px-2 py-1 bg-gray-600/50 text-gray-300 rounded text-xs">
                                ${chunk.content_type || 'text'}
                            </span>
                        </div>
                        <div class="flex items-center space-x-2 text-xs text-gray-400">
                            <span>${chunk.word_count || 'N/A'} kelime</span>
                            <span>•</span>
                            <span>${chunk.chunk_size || 'N/A'} karakter</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="text-sm text-gray-400 mb-2">İçerik:</div>
                        <div class="p-4 bg-gray-900/50 rounded-lg border border-gray-700">
                            <pre class="text-white text-sm whitespace-pre-wrap break-words">${chunk.content || 'İçerik bulunamadı'}</pre>
                        </div>
                    </div>
                    
                    ${chunk.metadata && Object.keys(chunk.metadata).length > 0 ? `
                        <div class="mb-3">
                            <div class="text-sm text-gray-400 mb-2">Meta Veri:</div>
                            <div class="p-3 bg-gray-900/30 rounded-lg border border-gray-700">
                                <pre class="text-gray-300 text-xs">${JSON.stringify(chunk.metadata, null, 2)}</pre>
                            </div>
                        </div>
                    ` : ''}
                    
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Hash: ${chunk.content_hash || 'N/A'}</span>
                        <span>Oluşturulma: ${new Date(chunk.created_at).toLocaleString('tr-TR')}</span>
                    </div>
                </div>
            `;
        });
        
        html += `</div>`;
    } else {
        html += `
            <div class="p-6 bg-yellow-500/10 border border-yellow-500/30 rounded-lg text-center">
                <p class="text-yellow-400">Bu sayfada chunk bulunamadı.</p>
            </div>
        `;
    }
    
    chunksContent.innerHTML = html;
}

// Global functions for bilgi tabanı operations
function searchKnowledgeBase(kbId) {
    console.log(`searchKnowledgeBase çağrıldı: ${kbId}`);
            const query = prompt('Bu bilgi tabanında arama yapmak için sorgu girin:');
    if (query) {
        const searchInput = document.getElementById('search-query');
        const searchButton = document.getElementById('search-btn');
        if (searchInput && searchButton) {
            searchInput.value = query;
            searchButton.click();
        } else {
            console.log('searchInput veya searchButton bulunamadı');
        }
    }
}

// Global viewChunks function - moved outside DOMContentLoaded
function viewChunks(kbId) {
    console.log(`viewChunks çağrıldı: ${kbId}`);
    

    
    // Get elements
    const chunksModal = document.getElementById('chunks-modal');
    const chunksContent = document.getElementById('chunks-content');
    
    // Show loading
    if (chunksModal) {
        chunksModal.classList.remove('hidden');
    } else {
        alert('Chunks modal bulunamadı. Sayfayı yenileyin.');
        return;
    }
    
    if (chunksContent) {
        chunksContent.innerHTML = `
            <div class="flex items-center justify-center py-12">
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 border-2 border-purple-glow border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-purple-glow">Chunk'lar yükleniyor...</span>
                </div>
            </div>
        `;
    } else {
        alert('Chunks content bulunamadı. Sayfayı yenileyin.');
        return;
    }
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('Güvenlik token\'ı bulunamadı. Sayfayı yenileyin.');
        return;
    }
    
    // Fetch chunks from API
    fetch(`/api/knowledge-base/${kbId}/chunks`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
        }
    })
    .then(response => {
        console.log(`Chunks API response status: ${response.status}`);
        if (typeof debugLog === 'function') {
            debugLog(`Chunks API response status: ${response.status}`);
        }
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Chunks API response alındı:', data);
        if (typeof debugLog === 'function') {
            debugLog('Chunks API response alındı: ' + JSON.stringify(data));
        }
        if (data.success) {
            if (typeof displayChunks === 'function') {
                displayChunks(data.chunks, data.knowledge_base, 1);
            } else {
                console.log('ERROR: displayChunks fonksiyonu bulunamadı');
                alert('displayChunks fonksiyonu bulunamadı. Sayfayı yenileyin.');
            }
        } else {
            console.log(`Chunks API Error: ${data.message}`);
            if (chunksContent) {
                chunksContent.innerHTML = `
                    <div class="p-6 bg-red-500/10 border border-red-500/30 rounded-lg">
                        <p class="text-red-400">Hata: ${data.message}</p>
                    </div>
                `;
            }
        }
    })
    .catch(error => {
        console.log(`Chunks API Error: ${error.message}`);
        if (typeof debugLog === 'function') {
            debugLog(`Chunks API Error: ${error.message}`);
        }
        if (chunksContent) {
            chunksContent.innerHTML = `
                <div class="p-6 bg-red-500/10 border border-red-500/30 rounded-lg">
                    <p class="text-red-400">Chunk'lar yüklenirken hata oluştu: ${error.message}</p>
                </div>
            `;
        }
    });
}



function deleteKnowledgeBase(kbId) {
    console.log(`deleteKnowledgeBase çağrıldı: ${kbId}`);
            if (confirm('Bu bilgi tabanını silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
        fetch(`/api/knowledge-base/${kbId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            alert('Silme işlemi sırasında hata oluştu: ' + error.message);
        });
    }
}

function viewKnowledgeBaseDetail(kbId) {
    console.log(`viewKnowledgeBaseDetail çağrıldı: ${kbId}`);
    
    // Show modal
    const modal = document.getElementById('kbDetailModal');
    const content = document.getElementById('kbDetailContent');
    const title = document.getElementById('modalTitle');
    
    if (modal && content) {
        modal.classList.remove('hidden');
        
        // Show loading
        content.innerHTML = `
            <div class="flex items-center justify-center py-12">
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 border-2 border-purple-glow border-t-transparent rounded-full animate-spin"></div>
                                            <span class="text-purple-glow">Bilgi tabanı detayları yükleniyor...</span>
                </div>
            </div>
        `;
        
        // Fetch bilgi tabanı details
        fetch(`/api/knowledge-base/${kbId}/detail`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayKnowledgeBaseDetail(data.knowledge_base, data.chunks, data.stats);
                title.textContent = data.knowledge_base.name;
            } else {
                content.innerHTML = `
                    <div class="p-6 bg-red-500/10 border border-red-500/30 rounded-lg">
                        <p class="text-red-400">Hata: ${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="p-6 bg-red-500/10 border border-red-500/30 rounded-lg">
                    <p class="text-red-400">Detaylar yüklenirken hata oluştu: ${error.message}</p>
                </div>
            `;
        });
    }
}

function closeKBDetailModal() {
    const modal = document.getElementById('kbDetailModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function displayKnowledgeBaseDetail(kb, chunks, stats) {
    const content = document.getElementById('kbDetailContent');
    
    content.innerHTML = `
        <!-- Bilgi Tabanı Overview -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700">
            <h4 class="text-xl font-semibold text-white mb-4">Genel Bilgiler</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">ID:</span>
                        <span class="text-white font-mono">#${kb.id}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Oluşturulma:</span>
                        <span class="text-white">${new Date(kb.created_at).toLocaleDateString('tr-TR')}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Son Güncelleme:</span>
                        <span class="text-white">${new Date(kb.updated_at).toLocaleDateString('tr-TR')}</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Kaynak Tipi:</span>
                        <span class="text-white">${kb.source_type}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Dosya Formatı:</span>
                        <span class="text-white">${kb.file_type || 'N/A'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Site ID:</span>
                        <span class="text-white">${kb.site_id}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="glass-effect rounded-xl p-6 border border-gray-700 text-center">
                <div class="text-3xl font-bold text-purple-glow mb-2">${stats.total_chunks}</div>
                <div class="text-gray-400">Toplam Chunk</div>
            </div>
            <div class="glass-effect rounded-xl p-6 border border-gray-700 text-center">
                <div class="text-3xl font-bold text-green-400 mb-2">${stats.avg_chunk_size}</div>
                <div class="text-gray-400">Ortalama Chunk Boyutu</div>
            </div>
            <div class="glass-effect rounded-xl p-6 border border-gray-700 text-center">
                <div class="text-3xl font-bold text-blue-400 mb-2">${stats.total_tokens}</div>
                <div class="text-gray-400">Toplam Token</div>
            </div>
        </div>

        <!-- Chunks Preview -->
        <div class="glass-effect rounded-xl border border-gray-700">
            <div class="p-6 border-b border-gray-700">
                <h4 class="text-xl font-semibold text-white">Chunk Önizlemesi (İlk 10)</h4>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    ${chunks.slice(0, 10).map((chunk, index) => `
                        <div class="p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-sm text-purple-glow font-medium">Chunk #${index + 1}</span>
                                <span class="text-xs text-gray-400">${chunk.content_type}</span>
                            </div>
                            <p class="text-gray-300 text-sm mb-2">${chunk.content.substring(0, 200)}${chunk.content.length > 200 ? '...' : ''}</p>
                            <div class="flex justify-between text-xs text-gray-400">
                                <span>Boyut: ${chunk.content.length} karakter</span>
                                <span>Oluşturulma: ${new Date(chunk.created_at).toLocaleDateString('tr-TR')}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
                ${chunks.length > 10 ? `
                    <div class="mt-4 text-center">
                        <p class="text-gray-400">Ve ${chunks.length - 10} chunk daha...</p>
                    </div>
                ` : ''}
            </div>
        </div>

        <!-- Actions -->
        <div class="flex space-x-4">
            <button onclick="searchKnowledgeBase(${kb.id})" class="px-6 py-3 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg text-white font-semibold hover:from-purple-dark hover:to-neon-purple transition-all duration-300">
                Arama Yap
            </button>
            <button onclick="viewChunks(${kb.id})" class="px-6 py-3 bg-gray-700 hover:bg-gray-600 rounded-lg text-white font-semibold transition-all duration-300">
                Tüm Chunk'ları Gör
            </button>
            <button onclick="closeKBDetailModal()" class="px-6 py-3 bg-gray-600 hover:bg-gray-500 rounded-lg text-white font-semibold transition-all duration-300">
                Kapat
            </button>
        </div>
    `;
}

// Field Mapping Functions
let currentKnowledgeBaseId = null;
let currentMappings = [];

function openFieldDetectionModal(kbId) {
    currentKnowledgeBaseId = kbId;
    const modal = document.getElementById('fieldDetectionModal');
    const loading = document.getElementById('fieldDetectionLoading');
    const results = document.getElementById('fieldDetectionResults');
    
    if (modal && loading && results) {
        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        results.classList.add('hidden');
        
        // Start field detection
        detectFields(kbId);
    }
}

function closeFieldDetectionModal() {
    const modal = document.getElementById('fieldDetectionModal');
    if (modal) {
        modal.classList.add('hidden');
        currentKnowledgeBaseId = null;
        currentMappings = [];
    }
}

function detectFields(kbId) {
    fetch(`/api/knowledge-base/${kbId}/detect-fields`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayFieldDetectionResults(data);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Field detection error:', error);
        alert('Field detection hatası: ' + error.message);
        closeFieldDetectionModal();
    });
}

function displayFieldDetectionResults(data) {
    const loading = document.getElementById('fieldDetectionLoading');
    const results = document.getElementById('fieldDetectionResults');
    const detectedFieldsGrid = document.getElementById('detectedFieldsGrid');
    const mappingTableBody = document.getElementById('mappingTableBody');
    
    if (loading && results && detectedFieldsGrid && mappingTableBody) {
        loading.classList.add('hidden');
        results.classList.remove('hidden');
        
        // Display detected fields
        displayDetectedFields(data.detected_fields, detectedFieldsGrid);
        
        // Initialize mappings table
        initializeMappingsTable(data.detected_fields, data.suggested_mappings, data.standard_fields, mappingTableBody);
        
        // Store current mappings
        currentMappings = generateInitialMappings(data.detected_fields, data.suggested_mappings);
    }
}

function displayDetectedFields(detectedFields, container) {
    container.innerHTML = '';
    
    Object.entries(detectedFields).forEach(([fieldName, fieldType]) => {
        const fieldCard = document.createElement('div');
        fieldCard.className = 'p-4 bg-gray-800/30 rounded-lg border border-gray-700';
        fieldCard.innerHTML = `
            <div class="flex items-center justify-between mb-2">
                <h5 class="text-white font-medium">${fieldName}</h5>
                <span class="px-2 py-1 text-xs font-medium rounded-full ${getFieldTypeColor(fieldType)}">${fieldType}</span>
            </div>
            <p class="text-gray-400 text-sm">Otomatik tespit edildi</p>
        `;
        container.appendChild(fieldCard);
    });
}

function getFieldTypeColor(fieldType) {
    const colors = {
        'text': 'bg-blue-500/20 text-blue-400',
        'number': 'bg-green-500/20 text-green-400',
        'date': 'bg-purple-500/20 text-purple-400',
        'boolean': 'bg-yellow-500/20 text-yellow-400',
        'array': 'bg-orange-500/20 text-orange-400'
    };
    return colors[fieldType] || 'bg-gray-500/20 text-gray-400';
}

function initializeMappingsTable(detectedFields, suggestedMappings, standardFields, container) {
    container.innerHTML = '';
    
    Object.entries(detectedFields).forEach(([sourceField, fieldType], index) => {
        const suggestion = suggestedMappings[sourceField];
        const targetField = suggestion ? suggestion.target_field : '';
        const confidence = suggestion ? Math.round(suggestion.confidence * 100) : 0;
        
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-800/30 transition-colors duration-200';
        row.innerHTML = `
            <td class="px-4 py-3 whitespace-nowrap">
                <div class="text-sm text-white">${sourceField}</div>
                ${suggestion ? `<div class="text-xs text-gray-400">Öneri: ${confidence}% güven</div>` : ''}
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
                <select class="form-input bg-gray-800 border-gray-700 text-white text-sm" onchange="updateMapping(${index}, 'target_field', this.value)">
                    <option value="">Seçiniz</option>
                    ${standardFields.map(field => `
                        <option value="${field}" ${targetField === field ? 'selected' : ''}>${field}</option>
                    `).join('')}
                </select>
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
                <select class="form-input bg-gray-800 border-gray-700 text-white text-sm" onchange="updateMapping(${index}, 'field_type', this.value)">
                    <option value="text" ${fieldType === 'text' ? 'selected' : ''}>Text</option>
                    <option value="number" ${fieldType === 'number' ? 'selected' : ''}>Number</option>
                    <option value="date" ${fieldType === 'date' ? 'selected' : ''}>Date</option>
                    <option value="boolean" ${fieldType === 'boolean' ? 'selected' : ''}>Boolean</option>
                    <option value="array" ${fieldType === 'array' ? 'selected' : ''}>Array</option>
                </select>
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
                <input type="checkbox" class="form-checkbox" onchange="updateMapping(${index}, 'is_required', this.checked)">
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
                <input type="text" class="form-input bg-gray-800 border-gray-700 text-white text-sm" placeholder="Opsiyonel" onchange="updateMapping(${index}, 'default_value', this.value)">
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
                <button onclick="editFieldMapping(${index})" class="text-blue-400 hover:text-blue-300 transition-colors duration-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </button>
            </td>
        `;
        container.appendChild(row);
    });
}

function generateInitialMappings(detectedFields, suggestedMappings) {
    const mappings = [];
    
    Object.entries(detectedFields).forEach(([sourceField, fieldType]) => {
        const suggestion = suggestedMappings[sourceField];
        mappings.push({
            source_field: sourceField,
            target_field: suggestion ? suggestion.target_field : '',
            field_type: fieldType,
            is_required: false,
            default_value: null,
            transformation: null,
            validation_rules: null
        });
    });
    
    return mappings;
}

function updateMapping(index, field, value) {
    if (currentMappings[index]) {
        currentMappings[index][field] = value;
    }
}

function editFieldMapping(index) {
    const mapping = currentMappings[index];
    if (!mapping) return;
    
    // Populate edit modal
    document.getElementById('editSourceField').value = mapping.source_field;
    document.getElementById('editTargetField').value = mapping.target_field;
    document.getElementById('editFieldType').value = mapping.field_type;
    document.getElementById('editIsRequired').checked = mapping.is_required;
    document.getElementById('editDefaultValue').value = mapping.default_value || '';
    
    // Populate transformation rules
    populateTransformationRules(mapping.transformation || {});
    
    // Store current editing index
    document.getElementById('fieldMappingModal').setAttribute('data-editing-index', index);
    
    // Show modal
    document.getElementById('fieldMappingModal').classList.remove('hidden');
}

function populateTransformationRules(transformation) {
    // Currency conversion
    if (transformation.currency_conversion) {
        document.getElementById('enableCurrencyConversion').checked = true;
        document.getElementById('currencyFrom').value = transformation.currency_conversion.from || 'USD';
        document.getElementById('currencyTo').value = transformation.currency_conversion.to || 'TRY';
        document.getElementById('currencyRate').value = transformation.currency_conversion.rate || '30.5';
    } else {
        document.getElementById('enableCurrencyConversion').checked = false;
    }
    
    // Date format conversion
    if (transformation.date_format) {
        document.getElementById('enableDateFormatConversion').checked = true;
        document.getElementById('dateFormatFrom').value = transformation.date_format.from || 'Y-m-d';
        document.getElementById('dateFormatTo').value = transformation.date_format.to || 'd/m/Y';
    } else {
        document.getElementById('enableDateFormatConversion').checked = false;
    }
    
    // Text processing
    if (transformation.text_processing) {
        document.getElementById('enableTextProcessing').checked = true;
        document.getElementById('textUppercase').checked = transformation.text_processing.uppercase || false;
        document.getElementById('textTrim').checked = transformation.text_processing.trim || false;
        document.getElementById('textRemoveSpecialChars').checked = transformation.text_processing.remove_special_chars || false;
    } else {
        document.getElementById('enableTextProcessing').checked = false;
    }
}

function closeFieldMappingModal() {
    document.getElementById('fieldMappingModal').classList.add('hidden');
}

function updateFieldMapping() {
    const index = parseInt(document.getElementById('fieldMappingModal').getAttribute('data-editing-index'));
    if (isNaN(index) || !currentMappings[index]) return;
    
    // Update mapping
    currentMappings[index].target_field = document.getElementById('editTargetField').value;
    currentMappings[index].field_type = document.getElementById('editFieldType').value;
    currentMappings[index].is_required = document.getElementById('editIsRequired').checked;
    currentMappings[index].default_value = document.getElementById('editDefaultValue').value || null;
    
    // Update transformation rules
    currentMappings[index].transformation = collectTransformationRules();
    
    // Close modal
    closeFieldMappingModal();
    
    // Refresh mappings table
    refreshMappingsTable();
}

function collectTransformationRules() {
    const transformation = {};
    
    // Currency conversion
    if (document.getElementById('enableCurrencyConversion').checked) {
        transformation.currency_conversion = {
            from: document.getElementById('currencyFrom').value,
            to: document.getElementById('currencyTo').value,
            rate: parseFloat(document.getElementById('currencyRate').value)
        };
    }
    
    // Date format conversion
    if (document.getElementById('enableDateFormatConversion').checked) {
        transformation.date_format = {
            from: document.getElementById('dateFormatFrom').value,
            to: document.getElementById('dateFormatTo').value
        };
    }
    
    // Text processing
    if (document.getElementById('enableTextProcessing').checked) {
        transformation.text_processing = {
            uppercase: document.getElementById('textUppercase').checked,
            trim: document.getElementById('textTrim').checked,
            remove_special_chars: document.getElementById('textRemoveSpecialChars').checked
        };
    }
    
    return Object.keys(transformation).length > 0 ? transformation : null;
}

function refreshMappingsTable() {
    const mappingTableBody = document.getElementById('mappingTableBody');
    if (mappingTableBody) {
        // Re-populate the table with current mappings
        const detectedFields = {};
        currentMappings.forEach(mapping => {
            detectedFields[mapping.source_field] = mapping.field_type;
        });
        
        // Re-initialize table (this is a simplified approach)
        // In a real implementation, you might want to update individual rows
        location.reload(); // Temporary solution
    }
}

// Template System Functions
function openMappingTemplatesModal() {
    const modal = document.getElementById('mappingTemplatesModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeMappingTemplatesModal() {
    const modal = document.getElementById('mappingTemplatesModal');
    if (modal) {
        modal.classList.add('hidden');
        document.getElementById('templatePreview').classList.add('hidden');
    }
}

function loadTemplate(templateType) {
    const templatePreview = document.getElementById('templatePreview');
    const templatePreviewContent = document.getElementById('templatePreviewContent');
    
    if (templatePreview && templatePreviewContent) {
        templatePreview.classList.remove('hidden');
        
        let templateData = {};
        let previewHtml = '';
        
        switch (templateType) {
            case 'ecommerce':
                templateData = {
                    name: 'E-commerce Template',
                    description: 'Ürün kataloğu için standart mapping',
                    mappings: [
                        { source: 'product_title', target: 'product_name', type: 'text', required: true },
                        { source: 'price_usd', target: 'product_price', type: 'number', required: true },
                        { source: 'category_name', target: 'product_category', type: 'text', required: true },
                        { source: 'brand_name', target: 'product_brand', type: 'text', required: false },
                        { source: 'stock_qty', target: 'product_stock', type: 'number', required: false },
                        { source: 'image_url', target: 'product_image', type: 'text', required: false },
                        { source: 'description', target: 'product_description', type: 'text', required: false }
                    ]
                };
                break;
                
            case 'faq':
                templateData = {
                    name: 'FAQ Template',
                    description: 'Sık sorulan sorular için mapping',
                    mappings: [
                        { source: 'question', target: 'question', type: 'text', required: true },
                        { source: 'answer', target: 'answer', type: 'text', required: true },
                        { source: 'category', target: 'category', type: 'text', required: false },
                        { source: 'tags', target: 'tags', type: 'array', required: false }
                    ]
                };
                break;
                
            case 'catalog':
                templateData = {
                    name: 'Product Catalog Template',
                    description: 'Detaylı ürün bilgileri için',
                    mappings: [
                        { source: 'product_name', target: 'product_name', type: 'text', required: true },
                        { source: 'product_description', target: 'product_description', type: 'text', required: false },
                        { source: 'product_sku', target: 'product_sku', type: 'text', required: true },
                        { source: 'product_stock', target: 'product_stock', type: 'number', required: false },
                        { source: 'product_price', target: 'product_price', type: 'number', required: true },
                        { source: 'product_category', target: 'product_category', type: 'text', required: false },
                        { source: 'product_brand', target: 'product_brand', type: 'text', required: false }
                    ]
                };
                break;
                
            case 'custom':
                templateData = {
                    name: 'Custom Template',
                    description: 'Boş template - manuel mapping',
                    mappings: []
                };
                break;
        }
        
        // Generate preview HTML
        previewHtml = `
            <div class="space-y-4">
                <div class="border-b border-gray-700 pb-3">
                    <h5 class="text-white font-semibold text-lg">${templateData.name}</h5>
                    <p class="text-gray-400 text-sm">${templateData.description}</p>
                </div>
                
                ${templateData.mappings.length > 0 ? `
                    <div>
                        <h6 class="text-white font-medium mb-2">Önerilen Mapping'ler:</h6>
                        <div class="space-y-2">
                            ${templateData.mappings.map(mapping => `
                                <div class="flex items-center justify-between p-2 bg-gray-700/30 rounded">
                                    <span class="text-gray-300 text-sm">${mapping.source} → ${mapping.target}</span>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 text-xs rounded-full ${getFieldTypeColor(mapping.type)}">${mapping.type}</span>
                                        ${mapping.required ? '<span class="px-2 py-1 text-xs rounded-full bg-red-500/20 text-red-400">Required</span>' : ''}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : `
                    <div class="text-center py-8">
                        <p class="text-gray-400">Bu template boş. Kendi mapping'inizi oluşturun.</p>
                    </div>
                `}
            </div>
        `;
        
        templatePreviewContent.innerHTML = previewHtml;
        
        // Store template data for later use
        document.getElementById('mappingTemplatesModal').setAttribute('data-template', JSON.stringify(templateData));
    }
}

function applyTemplate() {
    const templateData = JSON.parse(document.getElementById('mappingTemplatesModal').getAttribute('data-template') || '{}');
    
    if (templateData.mappings && templateData.mappings.length > 0) {
        // Apply template mappings to current mappings
        templateData.mappings.forEach(templateMapping => {
            // Find matching source field in current mappings
            const existingMapping = currentMappings.find(mapping => 
                mapping.source_field.toLowerCase().includes(templateMapping.source.toLowerCase()) ||
                templateMapping.source.toLowerCase().includes(mapping.source_field.toLowerCase())
            );
            
            if (existingMapping) {
                existingMapping.target_field = templateMapping.target;
                existingMapping.field_type = templateMapping.type;
                existingMapping.is_required = templateMapping.required;
            }
        });
        
        // Refresh the mappings table
        refreshMappingsTable();
        
        // Close template modal
        closeMappingTemplatesModal();
        
        // Show success message
        alert(`${templateData.name} başarıyla uygulandı!`);
    } else {
        alert('Uygulanacak template bulunamadı.');
    }
}

// Validation Rules Functions
function openValidationRulesModal() {
    const modal = document.getElementById('validationRulesModal');
    const fieldSelect = document.getElementById('validationFieldSelect');
    
    if (modal && fieldSelect) {
        // Populate field select
        fieldSelect.innerHTML = '<option value="">Field seçin...</option>';
        currentMappings.forEach((mapping, index) => {
            if (mapping.target_field) {
                const option = document.createElement('option');
                option.value = index;
                option.textContent = `${mapping.source_field} → ${mapping.target_field}`;
                fieldSelect.appendChild(option);
            }
        });
        
        modal.classList.remove('hidden');
    }
}

function closeValidationRulesModal() {
    const modal = document.getElementById('validationRulesModal');
    if (modal) {
        modal.classList.add('hidden');
        document.getElementById('validationRulesContent').classList.add('hidden');
    }
}

function loadFieldValidationRules() {
    const fieldIndex = document.getElementById('validationFieldSelect').value;
    const validationContent = document.getElementById('validationRulesContent');
    
    if (!fieldIndex) {
        validationContent.classList.add('hidden');
        return;
    }
    
    const mapping = currentMappings[fieldIndex];
    if (!mapping) return;
    
    validationContent.classList.remove('hidden');
    
    // Show/hide validation sections based on field type
    const textValidation = document.getElementById('textValidation');
    const numberValidation = document.getElementById('numberValidation');
    const dateValidation = document.getElementById('dateValidation');
    
    textValidation.classList.add('hidden');
    numberValidation.classList.add('hidden');
    dateValidation.classList.add('hidden');
    
    switch (mapping.field_type) {
        case 'text':
            textValidation.classList.remove('hidden');
            loadTextValidationRules(mapping.validation_rules || {});
            break;
        case 'number':
            numberValidation.classList.remove('hidden');
            loadNumberValidationRules(mapping.validation_rules || {});
            break;
        case 'date':
            dateValidation.classList.remove('hidden');
            loadDateValidationRules(mapping.validation_rules || {});
            break;
    }
}

function loadTextValidationRules(rules) {
    document.getElementById('textMinLength').value = rules.min_length || '';
    document.getElementById('textMaxLength').value = rules.max_length || '';
    document.getElementById('textRequired').checked = rules.required || false;
    document.getElementById('textEmail').checked = rules.email || false;
    document.getElementById('textUrl').checked = rules.url || false;
}

function loadNumberValidationRules(rules) {
    document.getElementById('numberMinValue').value = rules.min_value || '';
    document.getElementById('numberMaxValue').value = rules.max_value || '';
    document.getElementById('numberRequired').checked = rules.required || false;
    document.getElementById('numberInteger').checked = rules.integer || false;
}

function loadDateValidationRules(rules) {
    document.getElementById('dateMinValue').value = rules.min_date || '';
    document.getElementById('dateMaxValue').value = rules.max_date || '';
    document.getElementById('dateRequired').checked = rules.required || false;
    document.getElementById('dateFutureOnly').checked = rules.future_only || false;
}

function saveValidationRules() {
    const fieldIndex = document.getElementById('validationFieldSelect').value;
    if (!fieldIndex) return;
    
    const mapping = currentMappings[fieldIndex];
    if (!mapping) return;
    
    const validationRules = {};
    
    switch (mapping.field_type) {
        case 'text':
            validationRules.min_length = document.getElementById('textMinLength').value || null;
            validationRules.max_length = document.getElementById('textMaxLength').value || null;
            validationRules.required = document.getElementById('textRequired').checked;
            validationRules.email = document.getElementById('textEmail').checked;
            validationRules.url = document.getElementById('textUrl').checked;
            break;
        case 'number':
            validationRules.min_value = document.getElementById('numberMinValue').value || null;
            validationRules.max_value = document.getElementById('numberMaxValue').value || null;
            validationRules.required = document.getElementById('numberRequired').checked;
            validationRules.integer = document.getElementById('numberInteger').checked;
            break;
        case 'date':
            validationRules.min_date = document.getElementById('dateMinValue').value || null;
            validationRules.max_date = document.getElementById('dateMaxValue').value || null;
            validationRules.required = document.getElementById('dateRequired').checked;
            validationRules.future_only = document.getElementById('dateFutureOnly').checked;
            break;
    }
    
    // Remove null values
    Object.keys(validationRules).forEach(key => {
        if (validationRules[key] === null || validationRules[key] === '') {
            delete validationRules[key];
        }
    });
    
    // Update mapping
    mapping.validation_rules = Object.keys(validationRules).length > 0 ? validationRules : null;
    
    // Close modal
    closeValidationRulesModal();
    
    // Show success message
    alert('Validation rules başarıyla kaydedildi!');
}

// Batch Processing Functions
let batchProcessingState = {
    isRunning: false,
    isPaused: false,
    currentRow: 0,
    totalRows: 0,
    processedRows: 0,
    errorCount: 0,
    chunkSize: 100,
    processingInterval: null
};

function openBatchProcessingModal() {
    const modal = document.getElementById('batchProcessingModal');
    if (modal) {
        modal.classList.remove('hidden');
        initializeBatchProcessing();
    }
}

function closeBatchProcessingModal() {
    const modal = document.getElementById('batchProcessingModal');
    if (modal) {
        if (batchProcessingState.isRunning) {
            if (confirm('İşlem devam ediyor. Kapatmak istediğinizden emin misiniz?')) {
                stopBatchProcessing();
                modal.classList.add('hidden');
            }
        } else {
            modal.classList.add('hidden');
        }
    }
}

function initializeBatchProcessing() {
    // Reset state
    batchProcessingState = {
        isRunning: false,
        isPaused: false,
        currentRow: 0,
        totalRows: 1000, // Example total rows
        processedRows: 0,
        errorCount: 0,
        chunkSize: 100,
        processingInterval: null
    };
    
    // Update UI
    updateBatchProcessingUI();
    addProcessingLog('Batch processing hazırlandı');
}

function startBatchProcessing() {
    if (batchProcessingState.isRunning) return;
    
    batchProcessingState.isRunning = true;
    batchProcessingState.isPaused = false;
    
    updateBatchProcessingUI();
    addProcessingLog('Batch processing başlatıldı');
    
    // Start processing
    batchProcessingState.processingInterval = setInterval(() => {
        processBatchChunk();
    }, 100); // Process every 100ms
}

function pauseBatchProcessing() {
    if (!batchProcessingState.isRunning || batchProcessingState.isPaused) return;
    
    batchProcessingState.isPaused = true;
    clearInterval(batchProcessingState.processingInterval);
    
    updateBatchProcessingUI();
    addProcessingLog('Batch processing duraklatıldı');
}

function resumeBatchProcessing() {
    if (!batchProcessingState.isRunning || !batchProcessingState.isPaused) return;
    
    batchProcessingState.isPaused = false;
    
    // Resume processing
    batchProcessingState.processingInterval = setInterval(() => {
        processBatchChunk();
    }, 100);
    
    updateBatchProcessingUI();
    addProcessingLog('Batch processing devam ediyor');
}

function stopBatchProcessing() {
    batchProcessingState.isRunning = false;
    batchProcessingState.isPaused = false;
    
    if (batchProcessingState.processingInterval) {
        clearInterval(batchProcessingState.processingInterval);
        batchProcessingState.processingInterval = null;
    }
    
    updateBatchProcessingUI();
    addProcessingLog('Batch processing durduruldu');
}

function processBatchChunk() {
    if (batchProcessingState.currentRow >= batchProcessingState.totalRows) {
        // Processing completed
        stopBatchProcessing();
        addProcessingLog('Batch processing tamamlandı!');
        return;
    }
    
    // Process chunk
    const chunkEnd = Math.min(batchProcessingState.currentRow + batchProcessingState.chunkSize, batchProcessingState.totalRows);
    
    for (let i = batchProcessingState.currentRow; i < chunkEnd; i++) {
        // Simulate processing
        if (Math.random() < 0.05) { // 5% error rate
            batchProcessingState.errorCount++;
            addProcessingLog(`Satır ${i + 1} işlenirken hata oluştu`);
        } else {
            batchProcessingState.processedRows++;
        }
    }
    
    batchProcessingState.currentRow = chunkEnd;
    updateBatchProcessingUI();
    
    if (batchProcessingState.currentRow % 500 === 0) {
        addProcessingLog(`${batchProcessingState.currentRow} satır işlendi`);
    }
}

function updateBatchProcessingUI() {
    const status = document.getElementById('batchProcessingStatus');
    const progressBar = document.getElementById('batchProgressBar');
    const progressText = document.getElementById('batchProgressText');
    const processedRows = document.getElementById('processedRows');
    const totalRows = document.getElementById('totalRows');
    const errorCount = document.getElementById('errorCount');
    const startBtn = document.getElementById('startBatchProcessing');
    const pauseBtn = document.getElementById('pauseBatchProcessing');
    const resumeBtn = document.getElementById('resumeBatchProcessing');
    
    if (status && progressBar && progressText && processedRows && totalRows && errorCount) {
        // Update status
        if (!batchProcessingState.isRunning) {
            status.textContent = 'Hazır';
        } else if (batchProcessingState.isPaused) {
            status.textContent = 'Duraklatıldı';
        } else {
            status.textContent = 'İşleniyor...';
        }
        
        // Update progress
        const progress = (batchProcessingState.currentRow / batchProcessingState.totalRows) * 100;
        progressBar.style.width = `${progress}%`;
        progressText.textContent = `${Math.round(progress)}% tamamlandı`;
        
        // Update counters
        processedRows.textContent = batchProcessingState.processedRows;
        totalRows.textContent = batchProcessingState.totalRows;
        errorCount.textContent = batchProcessingState.errorCount;
        
        // Update buttons
        if (startBtn && pauseBtn && resumeBtn) {
            if (!batchProcessingState.isRunning) {
                startBtn.classList.remove('hidden');
                pauseBtn.classList.add('hidden');
                resumeBtn.classList.add('hidden');
            } else if (batchProcessingState.isPaused) {
                startBtn.classList.add('hidden');
                pauseBtn.classList.add('hidden');
                resumeBtn.classList.remove('hidden');
            } else {
                startBtn.classList.add('hidden');
                pauseBtn.classList.remove('hidden');
                resumeBtn.classList.add('hidden');
            }
        }
    }
}

function addProcessingLog(message) {
    const logContainer = document.getElementById('processingLog');
    if (logContainer) {
        const timestamp = new Date().toLocaleTimeString('tr-TR');
        const logEntry = document.createElement('div');
        logEntry.className = 'text-sm text-gray-300 mb-1';
        logEntry.textContent = `[${timestamp}] ${message}`;
        
        logContainer.appendChild(logEntry);
        logContainer.scrollTop = logContainer.scrollHeight;
        
        // Keep only last 50 log entries
        while (logContainer.children.length > 50) {
            logContainer.removeChild(logContainer.firstChild);
        }
    }
}

function previewTransformedData() {
    if (!currentKnowledgeBaseId || currentMappings.length === 0) {
        alert('Önce field mapping\'leri yapılandırın');
        return;
    }
    
    // Filter out mappings without target fields
    const validMappings = currentMappings.filter(mapping => mapping.target_field);
    if (validMappings.length === 0) {
        alert('En az bir hedef field seçin');
        return;
    }
    
    fetch(`/api/knowledge-base/${currentKnowledgeBaseId}/preview-data`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            mappings: validMappings
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayDataPreview(data.original_data, data.transformed_data);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Data preview error:', error);
        alert('Veri önizleme hatası: ' + error.message);
    });
}

function displayDataPreview(originalData, transformedData) {
    const container = document.getElementById('dataPreviewContent');
    
    let html = '<div class="space-y-4">';
    
    // Original data
    html += '<div><h5 class="text-white font-medium mb-2">Orijinal Veri:</h5>';
    html += '<div class="bg-gray-900/50 rounded p-3 overflow-x-auto">';
    html += '<pre class="text-sm text-gray-300">' + JSON.stringify(originalData, null, 2) + '</pre>';
    html += '</div></div>';
    
    // Transformed data
    html += '<div><h5 class="text-white font-medium mb-2">Dönüştürülmüş Veri:</h5>';
    html += '<div class="bg-gray-900/50 rounded p-3 overflow-x-auto">';
    html += '<pre class="text-sm text-gray-300">' + JSON.stringify(transformedData, null, 2) + '</pre>';
    html += '</div></div>';
    
    html += '</div>';
    container.innerHTML = html;
}

function saveFieldMappings() {
    if (!currentKnowledgeBaseId || currentMappings.length === 0) {
        alert('Kaydedilecek mapping bulunamadı');
        return;
    }
    
    // Filter out mappings without target fields
    const validMappings = currentMappings.filter(mapping => mapping.target_field);
    if (validMappings.length === 0) {
        alert('En az bir hedef field seçin');
        return;
    }
    
    fetch(`/api/knowledge-base/${currentKnowledgeBaseId}/save-mappings`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            mappings: validMappings
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Field mappings başarıyla kaydedildi!');
            
            // Get mapping statistics
            getMappingStatistics();
            
            closeFieldDetectionModal();
            location.reload(); // Refresh page to show updated status
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Save mappings error:', error);
        alert('Mapping kaydetme hatası: ' + error.message);
    });
}

function getMappingStatistics() {
    if (!currentKnowledgeBaseId) return;
    
    fetch(`/api/knowledge-base/${currentKnowledgeBaseId}/mapping-stats`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Mapping Statistics:', data.statistics);
        }
    })
    .catch(error => {
        console.error('Get mapping stats error:', error);
    });
}

function validateDataWithRules() {
    if (!currentKnowledgeBaseId || currentMappings.length === 0) {
        alert('Önce field mapping\'leri yapılandırın');
        return;
    }
    
    // Get sample data for validation
    const validMappings = currentMappings.filter(mapping => mapping.target_field);
    if (validMappings.length === 0) {
        alert('En az bir hedef field seçin');
        return;
    }
    
    // For demo purposes, use test data
    const testData = [
        {
            "product_title": "iPhone 15 Pro",
            "price_usd": "999",
            "category_name": "Smartphone",
            "brand_name": "Apple",
            "stock_qty": "50",
            "image_url": "https://example.com/iphone15.jpg",
            "description": "Latest iPhone with advanced features",
            "email": "test@example.com",
            "website": "https://apple.com",
            "release_date": "2024-09-15"
        }
    ];
    
    fetch(`/api/knowledge-base/${currentKnowledgeBaseId}/validate-data`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            mappings: validMappings,
            data: testData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayValidationResults(data);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Data validation error:', error);
        alert('Data validation hatası: ' + error.message);
    });
}

function displayValidationResults(validationData) {
    const container = document.getElementById('dataPreviewContent');
    
    let html = '<div class="space-y-4">';
    html += '<h5 class="text-white font-medium mb-2">Validation Results:</h5>';
    
    if (validationData.total_errors === 0) {
        html += '<div class="p-4 bg-green-500/20 border border-green-500/30 rounded-lg">';
        html += '<p class="text-green-400">✅ All data is valid!</p>';
        html += '</div>';
    } else {
        html += '<div class="p-4 bg-red-500/20 border border-red-500/30 rounded-lg">';
        html += `<p class="text-red-400">❌ ${validationData.total_errors} validation errors found</p>`;
        html += `<p class="text-gray-300 text-sm">Valid rows: ${validationData.valid_rows}/${validationData.total_rows}</p>`;
        html += '</div>';
        
        // Show detailed errors
        html += '<div class="space-y-2">';
        Object.entries(validationData.validation_results).forEach(([rowIndex, errors]) => {
            html += '<div class="p-3 bg-red-500/10 border border-red-500/30 rounded">';
            html += `<h6 class="text-red-400 font-medium">Row ${parseInt(rowIndex) + 1}:</h6>`;
            Object.entries(errors).forEach(([field, fieldErrors]) => {
                html += `<div class="ml-4 mb-2">`;
                html += `<span class="text-gray-300">${field}:</span>`;
                html += '<ul class="ml-4 text-red-300 text-sm">';
                fieldErrors.forEach(error => {
                    html += `<li>• ${error}</li>`;
                });
                html += '</ul>';
                html += '</div>';
            });
            html += '</div>';
        });
        html += '</div>';
    }
    
    html += '</div>';
    container.innerHTML = html;
}


</script>
    <!-- Field Detection Modal -->
    <div id="fieldDetectionModal" class="fixed inset-0 bg-black bg-opacity-85 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 p-6 border border-gray-700 w-4/5 h-4/5 shadow-2xl rounded-xl glass-effect overflow-y-auto custom-scrollbar" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-white">Field Detection & Mapping</h3>
                <button onclick="closeFieldDetectionModal()" class="text-gray-400 hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Loading State -->
            <div id="fieldDetectionLoading" class="text-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-glow mx-auto mb-4"></div>
                <p class="text-gray-400">Dosya analiz ediliyor...</p>
            </div>

            <!-- Field Detection Results -->
            <div id="fieldDetectionResults" class="hidden">
                <!-- Detected Fields -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-white mb-4">Tespit Edilen Field'lar</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="detectedFieldsGrid">
                        <!-- Detected fields will be populated here -->
                    </div>
                </div>

                <!-- Field Mapping Configuration -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-white mb-4">Field Mapping Konfigürasyonu</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-800/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Kaynak Field</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Hedef Field</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Field Tipi</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Zorunlu</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Varsayılan Değer</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="bg-transparent divide-y divide-gray-700" id="mappingTableBody">
                                <!-- Mapping rows will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Data Preview -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-white mb-4">Veri Önizlemesi</h4>
                    <div class="bg-gray-800/30 rounded-lg p-4">
                        <div id="dataPreviewContent">
                            <!-- Data preview will be populated here -->
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-between items-center">
                    <div class="flex space-x-3">
                        <button onclick="openMappingTemplatesModal()" class="px-6 py-3 bg-green-600 hover:bg-green-500 rounded-lg text-white font-semibold transition-colors duration-300">
                            Template Kullan
                        </button>
                        <button onclick="openValidationRulesModal()" class="px-6 py-3 bg-orange-600 hover:bg-orange-500 rounded-lg text-white font-semibold transition-colors duration-300">
                            Validation Rules
                        </button>
                        <button onclick="openBatchProcessingModal()" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 rounded-lg text-white font-semibold transition-colors duration-300">
                            Batch Processing
                        </button>
                        <button onclick="validateDataWithRules()" class="px-6 py-3 bg-yellow-600 hover:bg-yellow-500 rounded-lg text-white font-semibold transition-colors duration-300">
                            Validation Test
                        </button>
                        <button onclick="previewTransformedData()" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded-lg text-white font-semibold transition-colors duration-300">
                            Veri Önizle
                        </button>
                    </div>
                    <div class="flex space-x-4">
                        <button onclick="closeFieldDetectionModal()" class="px-6 py-3 bg-gray-600 hover:bg-gray-500 rounded-lg text-white font-semibold transition-colors duration-300">
                            İptal
                        </button>
                        <button onclick="saveFieldMappings()" class="px-6 py-3 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg text-white font-semibold hover:from-purple-dark hover:to-neon-purple transition-all duration-300">
                            Mapping'leri Kaydet
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Field Mapping Modal -->
    <div id="fieldMappingModal" class="fixed inset-0 bg-black bg-opacity-85 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 p-6 border border-gray-700 w-4/5 h-4/5 shadow-2xl rounded-xl glass-effect overflow-y-auto custom-scrollbar" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-white">Field Mapping Düzenle</h3>
                <button onclick="closeFieldMappingModal()" class="text-gray-400 hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column: Basic Settings -->
                <div class="space-y-6">
                    <h4 class="text-lg font-semibold text-white border-b border-gray-700 pb-2">Temel Ayarlar</h4>
                    
                    <!-- Source Field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Kaynak Field</label>
                        <input type="text" id="editSourceField" class="form-input w-full" readonly>
                    </div>

                    <!-- Target Field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Hedef Field</label>
                        <select id="editTargetField" class="form-input w-full">
                            <option value="">Seçiniz</option>
                            <option value="product_name">Product Name</option>
                            <option value="product_description">Product Description</option>
                            <option value="product_price">Product Price</option>
                            <option value="product_category">Product Category</option>
                            <option value="product_brand">Product Brand</option>
                            <option value="product_sku">Product SKU</option>
                            <option value="product_stock">Product Stock</option>
                            <option value="product_image">Product Image</option>
                            <option value="product_tags">Product Tags</option>
                            <option value="product_rating">Product Rating</option>
                            <option value="product_reviews">Product Reviews</option>
                        </select>
                    </div>

                    <!-- Field Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Field Tipi</label>
                        <select id="editFieldType" class="form-input w-full">
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="boolean">Boolean</option>
                            <option value="array">Array</option>
                        </select>
                    </div>

                    <!-- Required -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="editIsRequired" class="form-checkbox mr-2">
                            <span class="text-sm font-medium text-gray-400">Zorunlu Field</span>
                        </label>
                    </div>

                    <!-- Default Value -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Varsayılan Değer</label>
                        <input type="text" id="editDefaultValue" class="form-input w-full" placeholder="Opsiyonel">
                    </div>
                </div>

                <!-- Right Column: Transformation Rules -->
                <div class="space-y-6">
                    <h4 class="text-lg font-semibold text-white border-b border-gray-700 pb-2">Dönüşüm Kuralları</h4>
                    
                    <!-- Currency Conversion -->
                    <div class="p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <h5 class="text-white font-medium mb-3">Para Birimi Dönüşümü</h5>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" id="enableCurrencyConversion" class="form-checkbox">
                                <span class="text-sm text-gray-400">Para birimi dönüşümü etkinleştir</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Kaynak</label>
                                    <select id="currencyFrom" class="form-input text-sm">
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                        <option value="GBP">GBP</option>
                                        <option value="TRY">TRY</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Hedef</label>
                                    <select id="currencyTo" class="form-input text-sm">
                                        <option value="TRY" selected>TRY</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                        <option value="GBP">GBP</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Kur</label>
                                    <input type="number" id="currencyRate" class="form-input text-sm" value="30.5" step="0.01" placeholder="1.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date Format Conversion -->
                    <div class="p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <h5 class="text-white font-medium mb-3">Tarih Formatı Dönüşümü</h5>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" id="enableDateFormatConversion" class="form-checkbox">
                                <span class="text-sm text-gray-400">Tarih formatı dönüşümü etkinleştir</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Kaynak Format</label>
                                    <select id="dateFormatFrom" class="form-input text-sm">
                                        <option value="Y-m-d">YYYY-MM-DD</option>
                                        <option value="d/m/Y">DD/MM/YYYY</option>
                                        <option value="m/d/Y">MM/DD/YYYY</option>
                                        <option value="Y/m/d">YYYY/MM/DD</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Hedef Format</label>
                                    <select id="dateFormatTo" class="form-input text-sm">
                                        <option value="d/m/Y" selected>DD/MM/YYYY</option>
                                        <option value="Y-m-d">YYYY-MM-DD</option>
                                        <option value="m/d/Y">MM/DD/YYYY</option>
                                        <option value="Y/m/d">YYYY/MM/DD</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Text Processing -->
                    <div class="p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <h5 class="text-white font-medium mb-3">Metin İşleme</h5>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" id="enableTextProcessing" class="form-checkbox">
                                <span class="text-sm text-gray-400">Metin işleme etkinleştir</span>
                            </div>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" id="textUppercase" class="form-checkbox mr-2">
                                    <span class="text-sm text-gray-400">Büyük harfe çevir</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="textTrim" class="form-checkbox mr-2">
                                    <span class="text-sm text-gray-400">Boşlukları temizle</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="textRemoveSpecialChars" class="form-checkbox mr-2">
                                    <span class="text-sm text-gray-400">Özel karakterleri kaldır</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-700">
                <button onclick="closeFieldMappingModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-white font-medium transition-colors duration-300">
                    İptal
                </button>
                <button onclick="updateFieldMapping()" class="px-4 py-2 bg-purple-glow hover:bg-purple-dark rounded-lg text-white font-medium transition-colors duration-300">
                    Güncelle
                </button>
            </div>
        </div>
    </div>

    <!-- Validation Rules Modal -->
    <div id="validationRulesModal" class="fixed inset-0 bg-black bg-opacity-85 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 p-6 border border-gray-700 w-3/5 h-4/5 shadow-2xl rounded-xl glass-effect overflow-y-auto custom-scrollbar" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-white">Validation Rules</h3>
                <button onclick="closeValidationRulesModal()" class="text-gray-400 hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-6">
                <!-- Field Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Field Seçin</label>
                    <select id="validationFieldSelect" class="form-input w-full" onchange="loadFieldValidationRules()">
                        <option value="">Field seçin...</option>
                    </select>
                </div>

                <!-- Validation Rules -->
                <div id="validationRulesContent" class="hidden">
                    <h4 class="text-lg font-semibold text-white mb-4">Validation Rules</h4>
                    
                    <!-- Text Validation -->
                    <div id="textValidation" class="space-y-4 p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <h5 class="text-white font-medium">Text Validation</h5>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Min Length</label>
                                <input type="number" id="textMinLength" class="form-input text-sm" min="0" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Max Length</label>
                                <input type="number" id="textMaxLength" class="form-input text-sm" min="1" placeholder="255">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" id="textRequired" class="form-checkbox mr-2">
                                <span class="text-sm text-gray-400">Required</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="textEmail" class="form-checkbox mr-2">
                                <span class="text-sm text-gray-400">Email format</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="textUrl" class="form-checkbox mr-2">
                                <span class="text-sm text-gray-400">URL format</span>
                            </label>
                        </div>
                    </div>

                    <!-- Number Validation -->
                    <div id="numberValidation" class="space-y-4 p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <h5 class="text-white font-medium">Number Validation</h5>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Min Value</label>
                                <input type="number" id="numberMinValue" class="form-input text-sm" placeholder="-∞">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Max Value</label>
                                <input type="number" id="numberMaxValue" class="form-input text-sm" placeholder="+∞">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" id="numberRequired" class="form-checkbox mr-2">
                                <span class="text-sm text-gray-400">Required</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="numberInteger" class="form-checkbox mr-2">
                                <span class="text-sm text-gray-400">Integer only</span>
                            </label>
                        </div>
                    </div>

                    <!-- Date Validation -->
                    <div id="dateValidation" class="space-y-4 p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <h5 class="text-white font-medium">Date Validation</h5>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Min Date</label>
                                <input type="date" id="dateMinValue" class="form-input text-sm">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Max Date</label>
                                <input type="date" id="dateMaxValue" class="form-input text-sm">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" id="dateRequired" class="form-checkbox mr-2">
                                <span class="text-sm text-gray-400">Required</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="dateFutureOnly" class="form-checkbox mr-2">
                                <span class="text-sm text-gray-400">Future dates only</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-4 pt-4 border-t border-gray-700">
                    <button onclick="closeValidationRulesModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-white font-medium transition-colors duration-300">
                        İptal
                    </button>
                    <button onclick="saveValidationRules()" class="px-4 py-2 bg-purple-glow hover:bg-purple-dark rounded-lg text-white font-medium transition-colors duration-300">
                        Kaydet
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Processing Modal -->
    <div id="batchProcessingModal" class="fixed inset-0 bg-black bg-opacity-85 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 p-6 border border-gray-700 w-2/3 h-2/3 shadow-2xl rounded-xl glass-effect overflow-y-auto custom-scrollbar" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-white">Batch Processing</h3>
                <button onclick="closeBatchProcessingModal()" class="text-gray-400 hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-6">
                <!-- Processing Status -->
                <div class="text-center">
                    <div id="batchProcessingStatus" class="text-lg text-white mb-4">Hazırlanıyor...</div>
                    <div class="w-full bg-gray-700 rounded-full h-3 mb-2">
                        <div id="batchProgressBar" class="bg-gradient-to-r from-purple-glow to-neon-purple h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <div id="batchProgressText" class="text-sm text-gray-400">0% tamamlandı</div>
                </div>

                <!-- Processing Details -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <div id="processedRows" class="text-2xl font-bold text-green-400 mb-1">0</div>
                        <div class="text-sm text-gray-400">İşlenen Satır</div>
                    </div>
                    <div class="text-center p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <div id="totalRows" class="text-2xl font-bold text-blue-400 mb-1">0</div>
                        <div class="text-sm text-gray-400">Toplam Satır</div>
                    </div>
                    <div class="text-center p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <div id="errorCount" class="text-2xl font-bold text-red-400 mb-1">0</div>
                        <div class="text-sm text-gray-400">Hata Sayısı</div>
                    </div>
                </div>

                <!-- Processing Log -->
                <div>
                    <h4 class="text-lg font-semibold text-white mb-3">İşlem Log'u</h4>
                    <div id="processingLog" class="bg-gray-800/30 rounded-lg p-4 h-32 overflow-y-auto custom-scrollbar">
                        <div class="text-gray-400 text-sm">Log başlatılıyor...</div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-center space-x-4 pt-4 border-t border-gray-700">
                    <button id="startBatchProcessing" onclick="startBatchProcessing()" class="px-6 py-3 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg text-white font-semibold hover:from-purple-dark hover:to-neon-purple transition-all duration-300">
                        İşlemi Başlat
                    </button>
                    <button id="pauseBatchProcessing" onclick="pauseBatchProcessing()" class="px-6 py-3 bg-yellow-600 hover:bg-yellow-500 rounded-lg text-white font-semibold transition-colors duration-300 hidden">
                        Duraklat
                    </button>
                    <button id="resumeBatchProcessing" onclick="resumeBatchProcessing()" class="px-6 py-3 bg-green-600 hover:bg-green-500 rounded-lg text-white font-semibold transition-colors duration-300 hidden">
                        Devam Et
                    </button>
                    <button onclick="closeBatchProcessingModal()" class="px-6 py-3 bg-gray-600 hover:bg-gray-500 rounded-lg text-white font-semibold transition-colors duration-300">
                        Kapat
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mapping Templates Modal -->
    <div id="mappingTemplatesModal" class="fixed inset-0 bg-black bg-opacity-85 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 p-6 border border-gray-700 w-4/5 h-4/5 shadow-2xl rounded-xl glass-effect overflow-y-auto custom-scrollbar" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-white">Mapping Template'leri</h3>
                <button onclick="closeMappingTemplatesModal()" class="text-gray-400 hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-6">
                <!-- Predefined Templates -->
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">Önceden Tanımlanmış Template'ler</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- E-commerce Template -->
                        <div class="p-4 bg-gray-800/30 rounded-lg border border-gray-700 hover:border-purple-glow transition-colors duration-300 cursor-pointer" onclick="loadTemplate('ecommerce')">
                            <div class="flex items-center justify-between mb-2">
                                <h5 class="text-white font-medium">E-commerce</h5>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-500/20 text-green-400">Popüler</span>
                            </div>
                            <p class="text-gray-400 text-sm mb-3">Ürün kataloğu için standart mapping</p>
                            <div class="text-xs text-gray-500">
                                <span class="block">• product_name</span>
                                <span class="block">• product_price</span>
                                <span class="block">• product_category</span>
                                <span class="block">• product_brand</span>
                            </div>
                        </div>

                        <!-- FAQ Template -->
                        <div class="p-4 bg-gray-800/30 rounded-lg border border-gray-700 hover:border-purple-glow transition-colors duration-300 cursor-pointer" onclick="loadTemplate('faq')">
                            <div class="flex items-center justify-between mb-2">
                                <h5 class="text-white font-medium">FAQ</h5>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-500/20 text-blue-400">Soru-Cevap</span>
                            </div>
                            <p class="text-gray-400 text-sm mb-3">Sık sorulan sorular için mapping</p>
                            <div class="text-xs text-gray-500">
                                <span class="block">• question</span>
                                <span class="block">• answer</span>
                                <span class="block">• category</span>
                                <span class="block">• tags</span>
                            </div>
                        </div>

                        <!-- Product Catalog Template -->
                        <div class="p-4 bg-gray-800/30 rounded-lg border border-gray-700 hover:border-purple-glow transition-colors duration-300 cursor-pointer" onclick="loadTemplate('catalog')">
                            <div class="flex items-center justify-between mb-2">
                                <h5 class="text-white font-medium">Product Catalog</h5>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-500/20 text-purple-400">Detaylı</span>
                            </div>
                            <p class="text-gray-400 text-sm mb-3">Detaylı ürün bilgileri için</p>
                            <div class="text-xs text-gray-500">
                                <span class="block">• product_name</span>
                                <span class="block">• product_description</span>
                                <span class="block">• product_sku</span>
                                <span class="block">• product_stock</span>
                            </div>
                        </div>

                        <!-- Custom Template -->
                        <div class="p-4 bg-gray-800/30 rounded-lg border border-gray-700 hover:border-purple-glow transition-colors duration-300 cursor-pointer" onclick="loadTemplate('custom')">
                            <div class="flex items-center justify-between mb-2">
                                <h5 class="text-white font-medium">Custom</h5>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-500/20 text-yellow-400">Özel</span>
                            </div>
                            <p class="text-gray-400 text-sm mb-3">Kendi mapping'inizi oluşturun</p>
                            <div class="text-xs text-gray-500">
                                <span class="block">• Boş template</span>
                                <span class="block">• Manuel mapping</span>
                                <span class="block">• Özel field'lar</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Template Preview -->
                <div id="templatePreview" class="hidden">
                    <h4 class="text-lg font-semibold text-white mb-4">Template Önizlemesi</h4>
                    <div class="bg-gray-800/30 rounded-lg p-4">
                        <div id="templatePreviewContent">
                            <!-- Template preview will be populated here -->
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-4 pt-4 border-t border-gray-700">
                    <button onclick="closeMappingTemplatesModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-white font-medium transition-colors duration-300">
                        İptal
                    </button>
                    <button onclick="applyTemplate()" class="px-4 py-2 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg text-white font-medium hover:from-purple-dark hover:to-neon-purple transition-all duration-300">
                        Template'i Uygula
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

