<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ConvStateAI - Yapay Zeka Çözümleri</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('imgs/ai-conversion-logo.svg') }}">
    <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('imgs/ai-conversion-logo.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'purple-glow': '#8B5CF6',
                        'purple-dark': '#4C1D95',
                        'neon-purple': '#A855F7'
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate'
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' }
                        },
                        glow: {
                            '0%': { boxShadow: '0 0 20px #8B5CF6' },
                            '100%': { boxShadow: '0 0 40px #8B5CF6, 0 0 60px #8B5CF6' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #8B5CF6, #A855F7, #EC4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="bg-black text-white">
    <!-- Navigation -->
    <nav class="fixed w-full z-50 glass-effect">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                                            <img src="{{ asset('imgs/ai-conversion-logo.svg') }}" alt="ConvStateAI Logo" class="w-10 h-10">
                        <span class="ml-3 text-xl font-bold">ConvStateAI</span>
                </div>
             
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="px-4 py-2 text-purple-glow hover:text-white transition-colors">Giriş Yap</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section (Awareness Stage) -->
    <section class="min-h-screen flex items-center justify-center relative overflow-hidden">
        <!-- Background Effects -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-20 w-72 h-72 bg-purple-glow rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-float"></div>
            <div class="absolute top-40 right-20 w-96 h-96 bg-neon-purple rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-float" style="animation-delay: -2s;"></div>
            <div class="absolute bottom-20 left-1/2 w-80 h-80 bg-purple-dark rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-float" style="animation-delay: -4s;"></div>
        </div>
        
        <div class="relative z-10 text-center max-w-4xl mx-auto px-4">
            <h1 class="text-5xl md:text-7xl font-bold mb-6">
                <span class="gradient-text">Yapay Zeka</span> ile
                <br>Geleceği Şekillendirin
            </h1>
            <p class="text-xl md:text-2xl text-gray-300 mb-8 max-w-3xl mx-auto">
                ConvStateAI ile işletmenizi dijital dönüşüm yolculuğunda destekliyoruz. 
                Gelişmiş yapay zeka çözümleri ile verimliliğinizi artırın.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <button class="px-8 py-4 bg-gradient-to-r from-purple-glow to-neon-purple rounded-xl text-lg font-semibold hover:from-purple-dark hover:to-purple-glow transition-all duration-300 transform hover:scale-105 animate-glow">
                    Hemen Başlayın
                </button>
                <button class="px-8 py-4 glass-effect rounded-xl text-lg font-semibold hover:bg-white hover:text-black transition-all duration-300">
                    Demo İzleyin
                </button>
            </div>
            <div class="mt-12 flex items-center justify-center space-x-8 text-sm text-gray-400">
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                    <span>7 gün ücretsiz deneme</span>
                </div>
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                    <span>Kredi kartı gerekmez</span>
                </div>
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                    <span>Anında erişim</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section (Interest Stage) -->
    <section id="features" class="py-20 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-6">
                    Neden <span class="gradient-text">ConvStateAI</span>?
                </h2>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                    Yapay zeka teknolojilerini kullanarak işletmenizi bir üst seviyeye taşıyın
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass-effect rounded-2xl p-8 hover:transform hover:scale-105 transition-all duration-300 group">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-glow to-neon-purple rounded-2xl flex items-center justify-center mb-6 group-hover:animate-pulse-slow">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Akıllı Otomasyon</h3>
                    <p class="text-gray-300">Tekrarlayan görevleri otomatikleştirin ve ekibinizi daha değerli işlere odaklayın.</p>
                </div>

                <!-- Feature 2 -->
                <div class="glass-effect rounded-2xl p-8 hover:transform hover:scale-105 transition-all duration-300 group">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-glow to-neon-purple rounded-2xl flex items-center justify-center mb-6 group-hover:animate-pulse-slow">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Hızlı Entegrasyon</h3>
                    <p class="text-gray-300">Mevcut sistemlerinizle kolayca entegre olun, kurulum süresi sadece dakikalar.</p>
                </div>

                <!-- Feature 3 -->
                <div class="glass-effect rounded-2xl p-8 hover:transform hover:scale-105 transition-all duration-300 group">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-glow to-neon-purple rounded-2xl flex items-center justify-center mb-6 group-hover:animate-pulse-slow">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Gerçek Zamanlı Analiz</h3>
                    <p class="text-gray-300">Verilerinizi anlık olarak analiz edin ve hızlı kararlar alın.</p>
                </div>

                <!-- Feature 4 -->
                <div class="glass-effect rounded-2xl p-8 hover:transform hover:scale-105 transition-all duration-300 group">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-glow to-neon-purple rounded-2xl flex items-center justify-center mb-6 group-hover:animate-pulse-slow">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Güvenli Altyapı</h3>
                    <p class="text-gray-300">En yüksek güvenlik standartları ile verileriniz her zaman korunur.</p>
                </div>

                <!-- Feature 5 -->
                <div class="glass-effect rounded-2xl p-8 hover:transform hover:scale-105 transition-all duration-300 group">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-glow to-neon-purple rounded-2xl flex items-center justify-center mb-6 group-hover:animate-pulse-slow">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">7/24 Destek</h3>
                    <p class="text-gray-300">Uzman ekibimiz her zaman yanınızda, sorularınızı yanıtlamaya hazır.</p>
                </div>

                <!-- Feature 6 -->
                <div class="glass-effect rounded-2xl p-8 hover:transform hover:scale-105 transition-all duration-300 group">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-glow to-neon-purple rounded-2xl flex items-center justify-center mb-6 group-hover:animate-pulse-slow">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Sürekli Güncelleme</h3>
                    <p class="text-gray-300">En son AI teknolojileri ile platformumuz sürekli gelişiyor.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Proof Section -->
    <section class="py-16 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <p class="text-gray-400 text-lg">Binlerce şirket bizi tercih ediyor</p>
            </div>
            <div class="flex flex-wrap justify-center items-center gap-8 opacity-60">
                <div class="text-2xl font-bold text-gray-300">TechCorp</div>
                <div class="text-2xl font-bold text-gray-300">InnovateLab</div>
                <div class="text-2xl font-bold text-gray-300">FutureSoft</div>
                <div class="text-2xl font-bold text-gray-300">DataFlow</div>
                <div class="text-2xl font-bold text-gray-300">SmartBiz</div>
            </div>
        </div>
    </section>

    <!-- Pricing Section (Decision Stage) -->
    <section id="pricing" class="py-20 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-6">
                    <span class="gradient-text">Fiyatlandırma</span> Planları
                </h2>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                    İhtiyaçlarınıza uygun planı seçin ve hemen başlayın
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Starter Plan -->
                <div class="glass-effect rounded-2xl p-6 relative">
                    <div class="text-center">
                        <h3 class="text-xl font-bold mb-3">Starter</h3>
                        <div class="text-3xl font-bold mb-2">
                            <span class="gradient-text">$15</span>
                            <span class="text-lg text-gray-400">/ay</span>
                        </div>
                        <p class="text-gray-400 mb-6 text-sm">Küçük butik mağazalar için ideal</p>
                        
                        <ul class="text-left space-y-2 mb-6 text-sm">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Knowledge Base: 100 ürün</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Chat: Temel + ürün önerisi</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Kargo Takip & Sipariş</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Kampanya: 1 aktif</span>
                            </li>
                            <li class="flex items-start group relative">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="cursor-help">SSS: 20 kayıt</span>
                                <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded-lg py-2 px-3 w-48 z-10">
                                    <div class="relative">
                                        <div class="bg-gray-800 w-2 h-2 transform rotate-45 absolute -bottom-1 left-4"></div>
                                        SSS (Sık Sorulan Sorular) sistemi ile müşterilerinizin en çok sorduğu soruları otomatik olarak yanıtlayın
                                    </div>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Chat Forms: 1 form</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Widget: Standart tema</span>
                            </li>
                        </ul>
                        
                        <button class="w-full py-3 glass-effect rounded-lg hover:bg-white hover:text-black transition-all duration-300 text-sm">
                            Planı Seç
                        </button>
                    </div>
                </div>

                <!-- Pro Plan -->
                <div class="glass-effect rounded-2xl p-6 relative">
                    <div class="text-center">
                        <h3 class="text-xl font-bold mb-3">Pro</h3>
                        <div class="text-3xl font-bold mb-2">
                            <span class="gradient-text">$29</span>
                            <span class="text-lg text-gray-400">/ay</span>
                        </div>
                        <p class="text-gray-400 mb-6 text-sm">Orta ölçekli e-ticaret için</p>
                        
                        <ul class="text-left space-y-2 mb-6 text-sm">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Knowledge Base: 200 ürün</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Chat: Gelişmiş öneri</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Kargo + basit rapor</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Kampanya: 3 aktif</span>
                            </li>
                            <li class="flex items-start group relative">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="cursor-help">SSS: 50 kayıt</span>
                                <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded-lg py-2 px-3 w-48 z-10">
                                    <div class="relative">
                                        <div class="bg-gray-800 w-2 h-2 transform rotate-45 absolute -bottom-1 left-4"></div>
                                        SSS (Sık Sorulan Sorular) sistemi ile müşterilerinizin en çok sorduğu soruları otomatik olarak yanıtlayın
                                    </div>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Chat Forms: 3 form</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Widget: Tema + logo</span>
                            </li>
                        </ul>
                        
                        <button class="w-full py-3 glass-effect rounded-lg hover:bg-white hover:text-black transition-all duration-300 text-sm">
                            Planı Seç
                        </button>
                    </div>
                </div>

                <!-- Premium Plan (Featured) -->
                <div class="glass-effect rounded-2xl p-6 relative border-2 border-purple-glow transform scale-105">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-gradient-to-r from-purple-glow to-neon-purple text-white px-4 py-2 rounded-full text-sm font-semibold">
                            En Popüler
                        </span>
                    </div>
                    
                    <div class="text-center">
                        <h3 class="text-xl font-bold mb-3">Premium</h3>
                        <div class="text-3xl font-bold mb-2">
                            <span class="gradient-text">$59</span>
                            <span class="text-lg text-gray-400">/ay</span>
                        </div>
                        <p class="text-gray-400 mb-6 text-sm">Ajanslar ve büyük mağazalar için</p>
                        
                        <ul class="text-left space-y-2 mb-6 text-sm">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Knowledge Base: 500 ürün</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Chat: Fiyat karşılaştırmalı</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Kargo + detaylı rapor</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Kampanya: 10 aktif</span>
                            </li>
                            <li class="flex items-start group relative">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="cursor-help">SSS: 100 kayıt</span>
                                <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded-lg py-2 px-3 w-48 z-10">
                                    <div class="relative">
                                        <div class="bg-gray-800 w-2 h-2 transform rotate-45 absolute -bottom-1 left-4"></div>
                                        SSS (Sık Sorulan Sorular) sistemi ile müşterilerinizin en çok sorduğu soruları otomatik olarak yanıtlayın
                                    </div>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Chat Forms: Sınırsız</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Widget: Tema + logo</span>
                            </li>
                        </ul>
                        
                        <button class="w-full py-3 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg hover:from-purple-dark hover:to-purple-glow transition-all duration-300 transform hover:scale-105 text-sm">
                            Planı Seç
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enterprise Plan - Full Width Row -->
            <div class="w-full">
                <div class="glass-effect rounded-2xl p-8 relative transform scale-125">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold mb-4">Enterprise</h3>
                        <div class="text-4xl font-bold mb-3">
                            <span class="gradient-text">$499</span>
                            <span class="text-xl text-gray-400">/yıl</span>
                        </div>
                        <p class="text-gray-400 mb-8 text-base">Early Bird - Sonra $999/yıl</p>
                        
                        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                            <div class="text-left">
                                <h4 class="font-semibold mb-3 text-purple-glow">Knowledge Base</h4>
                                <ul class="space-y-2 text-sm">
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>Sınırsız ürün</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>Gelişmiş kategoriler</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="text-left">
                                <h4 class="font-semibold mb-3 text-purple-glow">Chat & AI</h4>
                                <ul class="space-y-2 text-sm">
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>Sınırsız + ERP/CRM</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>Özel AI modelleri</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="text-left">
                                <h4 class="font-semibold mb-3 text-purple-glow">Kargo & API</h4>
                                <ul class="space-y-2 text-sm">
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>API desteği</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>Detaylı raporlar</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="text-left">
                                <h4 class="font-semibold mb-3 text-purple-glow">Kampanya & Widget</h4>
                                <ul class="space-y-2 text-sm">
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>Sınırsız + A/B test</span>
                                    </li>
                                    
                                </ul>
                            </div>
                        </div>
                        
                        <button class="px-12 py-4 glass-effect rounded-xl text-lg font-semibold hover:bg-white hover:text-black transition-all duration-300">
                            İletişime Geç
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

   

    <!-- CTA Section (Action Stage) -->
    <section class="py-20 relative">
        <div class="max-w-4xl mx-auto text-center px-4">
            <div class="glass-effect rounded-3xl p-12 relative overflow-hidden">
                <!-- Background Effects -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-purple-glow rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
                <div class="absolute bottom-0 left-0 w-40 h-40 bg-neon-purple rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
                
                <div class="relative z-10">
                    <h2 class="text-4xl md:text-5xl font-bold mb-6">
                        Geleceği <span class="gradient-text">Bugün</span> Başlatın
                    </h2>
                    <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
                        7 gün ücretsiz deneme ile ConvStateAI'ın gücünü keşfedin. 
                        Kredi kartı gerekmez, anında başlayın.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <a href="{{ route('register') }}" class="px-8 py-4 bg-gradient-to-r from-purple-glow to-neon-purple rounded-xl text-lg font-semibold hover:from-purple-dark hover:to-neon-purple transition-all duration-300 transform hover:scale-105 animate-glow">
                            Hemen Başla
                        </a>
                    </div>
                    <p class="text-sm text-gray-400 mt-4">
                        Zaten hesabınız var mı? <a href="{{ route('login') }}" class="text-purple-glow hover:text-neon-purple">Giriş yapın</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="py-16 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center mb-4">
                        <img src="{{ asset('imgs/ai-conversion-logo.svg') }}" alt="ConvStateAI Logo" class="w-10 h-10">
                        <span class="ml-3 text-xl font-bold">ConvStateAI</span>
                    </div>
                    <p class="text-gray-400 mb-4">Yapay zeka ile geleceği şekillendiriyoruz.</p>
                    <div class="flex space-x-4">
                         
                        <a href="#" class="text-gray-400 hover:text-purple-glow transition-colors">
                         <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                          </svg>
                      
                        </a>
                        <a href="#" class="text-gray-400 hover:text-purple-glow transition-colors">
                         <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                          </svg>
                        </a>
                      
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Ürün</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-purple-glow transition-colors">Özellikler</a></li>
 
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Şirket</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-purple-glow transition-colors">Kariyer</a></li>
                        <li><a href="#" class="hover:text-purple-glow transition-colors">Blog</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Destek</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-purple-glow transition-colors">Yardım Merkezi</a></li>
     
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm mb-4 md:mb-0">
                    © <span id="current-year"></span> ConvStateAI. Tüm hakları saklıdır.
                </p>
             
                <div class="flex space-x-6 text-sm text-gray-400">
                    <a href="{{ route('privacy-policy') }}" class="hover:text-purple-glow transition-colors">Gizlilik Politikası</a>
                    <a href="{{ route('terms-of-service') }}" class="hover:text-purple-glow transition-colors">Kullanım Şartları</a>
                    <a href="{{ route('cookies') }}" class="hover:text-purple-glow transition-colors">Çerezler</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Smooth Scrolling -->
    <script>
     document.getElementById('current-year').textContent = new Date().getFullYear();

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all glass-effect elements
        document.querySelectorAll('.glass-effect').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>
