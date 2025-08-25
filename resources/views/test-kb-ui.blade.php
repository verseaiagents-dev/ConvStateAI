<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Knowledge Base UI Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-effect {
            background: rgba(17, 25, 40, 0.75);
            backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.125);
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .purple-glow {
            color: #a855f7;
        }
        .neon-purple {
            color: #8b5cf6;
        }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen p-6">
    <div class="max-w-6xl mx-auto space-y-6">
        <h1 class="text-4xl font-bold text-center mb-8">
            <span class="gradient-text">Knowledge Base UI Test</span>
        </h1>

        <!-- File Upload Container -->
        <div class="glass-effect rounded-2xl p-8">
            <h2 class="text-2xl font-bold mb-6 text-white">Dosya Yükleme Testi</h2>
            
            <!-- Upload Area -->
            <div id="upload-area" class="border-2 border-dashed border-gray-600 rounded-2xl p-12 text-center hover:border-purple-glow transition-colors duration-300 cursor-pointer">
                <div class="space-y-4">
                    <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <div>
                        <p class="text-xl font-semibold text-white mb-2">Dosya seçin veya sürükleyin</p>
                        <p class="text-gray-400 mb-4">Desteklenen formatlar: CSV, TXT, XML, JSON, Excel</p>
                        <p class="text-sm text-gray-500">Maksimum dosya boyutu: 10MB</p>
                    </div>
                    <button id="select-file-btn" class="px-6 py-3 bg-gradient-to-r from-purple-500 to-blue-500 rounded-lg text-white font-semibold hover:from-purple-600 hover:to-blue-600 transition-all duration-300 transform hover:scale-105">
                        Dosya Seç
                    </button>
                </div>
            </div>

            <!-- Hidden File Input -->
            <input type="file" id="file-input" accept=".csv,.txt,.xml,.json,.xlsx,.xls" class="hidden">
            
            <!-- Upload Progress -->
            <div id="upload-progress" class="hidden mt-6">
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-4 h-4 border-2 border-purple-500 border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-purple-500">Dosya yükleniyor ve işleniyor...</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2">
                    <div id="progress-bar" class="bg-gradient-to-r from-purple-500 to-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <!-- Search Container -->
        <div class="glass-effect rounded-2xl p-8">
            <h2 class="text-2xl font-bold mb-6 text-white">AI Arama Testi</h2>
            
            <div class="space-y-4">
                <div class="flex space-x-4">
                    <input type="text" id="search-query" placeholder="Ürün arama, kategori bilgisi, yardım..." class="flex-1 px-4 py-3 bg-gray-800/50 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                    <button id="search-btn" class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg text-white font-semibold hover:from-green-600 hover:to-emerald-600 transition-all duration-300 transform hover:scale-105">
                        AI ile Ara
                    </button>
                </div>
                
                <p class="text-sm text-gray-400">
                    AI destekli intent detection ile knowledge base'de arama yapın.
                </p>
            </div>
            
            <!-- Search Results -->
            <div id="search-results" class="hidden mt-6">
                <div id="search-content" class="space-y-4">
                    <!-- Search results will be populated here -->
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

        <!-- Console Log -->
        <div class="glass-effect rounded-2xl p-8">
            <h2 class="text-2xl font-bold mb-6 text-white">Console Log</h2>
            <div id="console-log" class="bg-gray-800 p-4 rounded-lg font-mono text-sm text-green-400 max-h-64 overflow-y-auto">
                <!-- Console messages will appear here -->
            </div>
        </div>
    </div>

    <script>
    function log(message) {
        const consoleLog = document.getElementById('console-log');
        const timestamp = new Date().toLocaleTimeString();
        consoleLog.innerHTML += `[${timestamp}] ${message}\n`;
        consoleLog.scrollTop = consoleLog.scrollHeight;
        console.log(message);
    }

    document.addEventListener('DOMContentLoaded', function() {
        log('DOM yüklendi, event listener\'lar kuruluyor...');
        
        const uploadArea = document.getElementById('upload-area');
        const fileInput = document.getElementById('file-input');
        const selectFileBtn = document.getElementById('select-file-btn');
        const uploadProgress = document.getElementById('upload-progress');
        const progressBar = document.getElementById('progress-bar');
        const resultsContainer = document.getElementById('results-container');
        const resultsContent = document.getElementById('results-content');
        
        // Search elements
        const searchQuery = document.getElementById('search-query');
        const searchBtn = document.getElementById('search-btn');
        const searchResults = document.getElementById('search-results');
        const searchContent = document.getElementById('search-content');

        log('Elementler bulundu:');
        log(`- uploadArea: ${!!uploadArea}`);
        log(`- fileInput: ${!!fileInput}`);
        log(`- selectFileBtn: ${!!selectFileBtn}`);
        log(`- searchBtn: ${!!searchBtn}`);

        // File selection - Event listener'ları düzelt
        if (selectFileBtn) {
            selectFileBtn.addEventListener('click', () => {
                log('Dosya Seç butonuna tıklandı');
                if (fileInput) {
                    fileInput.click();
                    log('File input açıldı');
                } else {
                    log('ERROR: fileInput bulunamadı');
                }
            });
            log('selectFileBtn event listener eklendi');
        } else {
            log('ERROR: selectFileBtn bulunamadı');
        }
        
        if (uploadArea) {
            uploadArea.addEventListener('click', () => {
                log('Upload area\'ya tıklandı');
                if (fileInput) {
                    fileInput.click();
                    log('File input açıldı');
                } else {
                    log('ERROR: fileInput bulunamadı');
                }
            });
            log('uploadArea event listener eklendi');
        } else {
            log('ERROR: uploadArea bulunamadı');
        }

        // Search - Event listener'ları düzelt
        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                log('AI ile Ara butonuna tıklandı');
                handleSearch();
            });
            log('searchBtn event listener eklendi');
        } else {
            log('ERROR: searchBtn bulunamadı');
        }
        
        if (searchQuery) {
            searchQuery.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    log('Enter tuşuna basıldı, arama başlatılıyor');
                    handleSearch();
                }
            });
            log('searchQuery event listener eklendi');
        } else {
            log('ERROR: searchQuery bulunamadı');
        }

        // File input change
        if (fileInput) {
            fileInput.addEventListener('change', (e) => {
                log('File input değişti');
                if (e.target.files.length > 0) {
                    log(`Seçilen dosya: ${e.target.files[0].name}`);
                    handleFileUpload(e.target.files[0]);
                }
            });
            log('fileInput event listener eklendi');
        } else {
            log('ERROR: fileInput bulunamadı');
        }

        function handleSearch() {
            const query = searchQuery.value.trim();
            log(`Arama sorgusu: "${query}"`);
            
            if (!query) {
                log('Hata: Arama sorgusu boş');
                alert('Lütfen arama sorgusu girin');
                return;
            }

            // Show loading
            searchResults.classList.remove('hidden');
            searchContent.innerHTML = `
                <div class="flex items-center space-x-3">
                    <div class="w-4 h-4 border-2 border-green-500 border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-green-400">AI ile arama yapılıyor...</span>
                </div>
            `;

            log('Arama başlatılıyor...');

            // Simulate search (since we don't have a real knowledge base)
            setTimeout(() => {
                log('Arama simülasyonu tamamlandı');
                searchContent.innerHTML = `
                    <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-lg">
                        <h4 class="text-lg font-semibold text-green-400 mb-2">Test Arama Sonucu</h4>
                        <p class="text-white">Bu bir test arama sonucudur. Gerçek knowledge base entegrasyonu için API endpoint'leri gerekli.</p>
                        <div class="mt-3 text-sm text-gray-400">
                            <p>• Sorgu: "${query}"</p>
                            <p>• Intent: test_search</p>
                            <p>• Güven: 0.95</p>
                        </div>
                    </div>
                `;
            }, 2000);
        }

        function handleFileUpload(file) {
            log(`Dosya yükleme başlatılıyor: ${file.name}`);
            
            // Validate file type
            const allowedTypes = ['csv', 'txt', 'xml', 'json', 'xlsx', 'xls'];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            
            if (!allowedTypes.includes(fileExtension)) {
                log(`Hata: Desteklenmeyen dosya formatı: ${fileExtension}`);
                alert('Desteklenmeyen dosya formatı. Lütfen CSV, TXT, XML, JSON veya Excel dosyası seçin.');
                return;
            }

            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                log(`Hata: Dosya boyutu çok büyük: ${(file.size / 1024 / 1024).toFixed(2)}MB`);
                alert('Dosya boyutu çok büyük. Maksimum 10MB olmalıdır.');
                return;
            }

            log(`Dosya validasyonu başarılı: ${fileExtension}, ${(file.size / 1024).toFixed(2)}KB`);

            // Get knowledge base name
            const kbName = prompt('Knowledge Base için bir isim girin:');
            if (!kbName) {
                log('Knowledge base adı girilmedi, işlem iptal edildi');
                return;
            }

            log(`Knowledge base adı: ${kbName}`);

            // Show progress
            uploadProgress.classList.remove('hidden');
            resultsContainer.classList.add('hidden');
            
            // Simulate progress
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                progressBar.style.width = progress + '%';
                log(`Progress: ${progress.toFixed(1)}%`);
            }, 200);

            // Simulate upload (since we don't have a real API)
            setTimeout(() => {
                clearInterval(progressInterval);
                progressBar.style.width = '100%';
                log('Dosya yükleme simülasyonu tamamlandı');
                
                setTimeout(() => {
                    uploadProgress.classList.add('hidden');
                    showResults({
                        success: true,
                        message: 'Dosya başarıyla yüklendi ve işlendi',
                        knowledge_base_id: 'TEST_' + Date.now(),
                        chunk_count: Math.floor(Math.random() * 50) + 10,
                        file_name: file.name
                    });
                }, 500);
            }, 3000);
        }

        function showResults(data) {
            log('Sonuçlar gösteriliyor');
            if (data.success) {
                let html = '';

                // Success info
                html += `
                    <div class="p-6 bg-green-500/10 border border-green-500/30 rounded-lg mb-6">
                        <h3 class="text-lg font-semibold text-green-400 mb-2">✓ ${data.message || 'İşlem başarılı'}</h3>
                        <div class="grid md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-gray-400">Knowledge Base ID:</span>
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
                log('Sonuçlar başarıyla gösterildi');
            } else {
                log(`Hata: ${data.message || 'Bilinmeyen hata'}`);
                alert('Hata: ' + (data.message || 'Bilinmeyen hata'));
            }
        }

        log('Tüm event listener\'lar kuruldu!');
    });


    </script>
</body>
</html>
