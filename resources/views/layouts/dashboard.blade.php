<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - ConvStateAI</title>
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
        .form-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .form-input:focus {
            border-color: #8B5CF6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
            background: rgba(255, 255, 255, 0.1);
        }
        .form-input:hover {
            border-color: rgba(255, 255, 255, 0.3);
        }
        .form-checkbox {
            appearance: none;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.375rem;
            width: 1.25rem;
            height: 1.25rem;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .form-checkbox:checked {
            background: linear-gradient(135deg, #8B5CF6, #A855F7);
            border-color: #8B5CF6;
        }
        .form-checkbox:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .sidebar {
            transition: transform 0.3s ease;
        }
        .sidebar.closed {
            transform: translateX(-100%);
        }
        
        /* Sidebar Scrollbar Styles */
        .sidebar .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar .overflow-y-auto::-webkit-scrollbar-track {
            background: rgba(75, 85, 99, 0.3);
            border-radius: 3px;
        }
        
        .sidebar .overflow-y-auto::-webkit-scrollbar-thumb {
            background: rgba(139, 92, 246, 0.6);
            border-radius: 3px;
            transition: background-color 0.2s ease;
        }
        
        .sidebar .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: rgba(139, 92, 246, 0.8);
        }
        
        /* Firefox Scrollbar */
        .sidebar .overflow-y-auto {
            scrollbar-width: thin;
            scrollbar-color: rgba(139, 92, 246, 0.6) rgba(75, 85, 99, 0.3);
        }

        /* Custom scrollbar for modals */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(31, 41, 55, 0.8);
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #8b5cf6, #a855f7);
            border-radius: 3px;
            border: 1px solid rgba(139, 92, 246, 0.3);
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #7c3aed, #9333ea);
            border-color: rgba(139, 92, 246, 0.5);
        }

        /* Firefox scrollbar for modals */
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #8b5cf6 #1f2937;
        }

        /* Tooltip Styles */
        .tooltip {
            position: relative;
            display: inline-block;
        }
        
        .tooltip .tooltip-content {
            visibility: hidden;
            opacity: 0;
            position: absolute;
            z-index: 1000;
            background: rgba(17, 24, 39, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(75, 85, 99, 0.5);
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            transition: all 0.2s ease;
            white-space: nowrap;
            font-size: 14px;
            color: white;
        }
        
        .tooltip:hover .tooltip-content {
            visibility: visible;
            opacity: 1;
        }
        
        .tooltip .tooltip-arrow {
            position: absolute;
            width: 0;
            height: 0;
            border: 5px solid transparent;
        }
        
        .tooltip .tooltip-arrow.top {
            border-top-color: rgba(17, 24, 39, 0.95);
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
        }
        
        @media (min-width: 768px) {
            .sidebar.closed {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-black text-white">
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar fixed inset-y-0 left-0 z-50 w-64 bg-gray-900/95 backdrop-blur-xl border-r border-gray-800 transform transition-transform duration-300 ease-in-out flex flex-col">
        <!-- Logo -->
        <div class="flex items-center justify-between p-6 border-b border-gray-800 flex-shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center">
                                        <img src="{{ asset('imgs/ai-conversion-logo.svg') }}" alt="ConvStateAI Logo" class="w-10 h-10">
                        <span class="ml-3 text-xl font-bold">ConvStateAI</span>
            </a>
            <button id="close-sidebar" class="md:hidden text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- User Info -->
        @auth
        <div class="p-6 border-b border-gray-800 flex-shrink-0">
            <div class="flex items-center space-x-3">
                                    <img src="{{ asset('imgs/ai-conversion-logo.svg') }}" alt="ConvStateAI Logo" class="w-12 h-12">
                <div>
                    <h3 class="font-semibold text-white">{{ auth()->user()->getDisplayName() }}</h3>
                    <p class="text-sm text-gray-400">{{ auth()->user()->email }}</p>
                    @if(auth()->user()->isAdmin())
                        <span class="inline-block px-2 py-1 text-xs bg-purple-glow/20 text-purple-glow rounded-full mt-1">Admin</span>
                    @endif
                </div>
            </div>
        </div>
        @endauth

        <!-- Scrollable Navigation Container -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden">
            <nav class="p-6 space-y-2">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-purple-glow/10 hover:text-purple-glow transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-purple-glow/20 text-purple-glow' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('dashboard.profile') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-purple-glow/10 hover:text-purple-glow transition-all duration-200 {{ request()->routeIs('dashboard.profile*') ? 'bg-purple-glow/20 text-purple-glow' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span>Profil</span>
            </a>

            <a href="{{ route('dashboard.knowledge-base') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-purple-glow/10 hover:text-purple-glow transition-all duration-200 {{ request()->routeIs('dashboard.knowledge-base*') ? 'bg-purple-glow/20 text-purple-glow' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span>Bilgi Tabanı</span>
            </a>

            <a href="{{ route('dashboard.chat-sessions') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-purple-glow/10 hover:text-purple-glow transition-all duration-200 {{ request()->routeIs('dashboard.chat-sessions*') ? 'bg-purple-glow/20 text-purple-glow' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <span>Chat Sessions</span>
            </a>
            <a href="{{ route('dashboard.campaigns.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-purple-glow/10 hover:text-purple-glow transition-all duration-200 {{ request()->routeIs('dashboard.campaigns*') ? 'bg-purple-glow/20 text-purple-glow' : '' }}">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
               </svg>
               <span>Kampanya Yönetimi</span>
           </a>
           
           <a href="{{ route('dashboard.faqs.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-purple-glow/10 hover:text-purple-glow transition-all duration-200 {{ request()->routeIs('dashboard.faqs*') ? 'bg-purple-glow/20 text-purple-glow' : '' }}">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
               </svg>
               <span>SSS Yönetimi</span>
           </a>
           
           <a href="{{ route('dashboard.widget-design') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-purple-glow/10 hover:text-purple-glow transition-all duration-200 {{ request()->routeIs('dashboard.widget-design*') ? 'bg-purple-glow/20 text-purple-glow' : '' }}">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
               </svg>
               <span>Widget Tasarımı</span>
           </a>
            <a href="{{ route('dashboard.analytics') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-purple-glow/10 hover:text-purple-glow transition-all duration-200 {{ request()->routeIs('dashboard.analytics*') ? 'bg-purple-glow/20 text-purple-glow' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span>Analytics</span>
            </a>

            <a href="{{ route('dashboard.api-settings') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-purple-glow/10 hover:text-purple-glow transition-all duration-200 {{ request()->routeIs('dashboard.api-settings*') ? 'bg-purple-glow/20 text-purple-glow' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>API Ayarları</span>
            </a>

            <a href="{{ route('dashboard.settings') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-purple-glow/10 hover:text-purple-glow transition-all duration-200 {{ request()->routeIs('dashboard.settings*') ? 'bg-purple-glow/20 text-purple-glow' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>Ayarlar</span>
            </a>

            <!-- Management Section -->
          
            </nav>
        </div>

        <!-- Logout -->
        <div class="p-6 border-t border-gray-800 flex-shrink-0">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-red-500/10 hover:text-red-400 transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Çıkış Yap</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Bar -->
        <div class="bg-gray-900/95 backdrop-blur-xl border-b border-gray-800 p-4">
            <div class="flex items-center justify-between">
                <button id="open-sidebar" class="md:hidden text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.dashboard') }}" class="text-blue-400 hover:text-blue-300 transition-colors">Admin Paneline Geç</a>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <main class="p-6 overflow-y-auto">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-500/20 border border-green-500/30 rounded-lg text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 rounded-lg text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 md:hidden hidden"></div>

    <script>
        // Sidebar toggle for mobile
        const sidebar = document.getElementById('sidebar');
        const openSidebarBtn = document.getElementById('open-sidebar');
        const closeSidebarBtn = document.getElementById('close-sidebar');
        const mobileOverlay = document.getElementById('mobile-overlay');

        function openSidebar() {
            sidebar.classList.remove('closed');
            mobileOverlay.classList.remove('hidden');
        }

        function closeSidebar() {
            sidebar.classList.add('closed');
            mobileOverlay.classList.add('hidden');
        }

        openSidebarBtn.addEventListener('click', openSidebar);
        closeSidebarBtn.addEventListener('click', closeSidebar);
        mobileOverlay.addEventListener('click', closeSidebar);

        // Close sidebar on window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('closed');
                mobileOverlay.classList.add('hidden');
            } else {
                sidebar.classList.add('closed');
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
