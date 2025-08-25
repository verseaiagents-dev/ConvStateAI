@extends('layouts.admin')

@section('title', 'Sistem Analytics - Admin Panel')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="glass-effect rounded-2xl p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-glow rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-neon-purple rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        
        <div class="relative z-10">
            <h1 class="text-4xl font-bold mb-4">
                <span class="gradient-text">Sistem Analytics</span> 📊
            </h1>
            <p class="text-xl text-gray-300 mb-6">
                Sistem performansı ve kullanıcı aktivitelerini detaylı olarak takip edin.
            </p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Users -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-purple-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-purple-500/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Toplam Kullanıcı</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $stats['total_users'] }}</p>
                </div>
                <div class="p-3 bg-purple-500/20 rounded-full">
                    <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400">↗️ +12% from last week</span>
            </div>
        </div>

        <!-- Active Users -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-green-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-green-500/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Aktif Kullanıcı</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $stats['active_users'] }}</p>
                </div>
                <div class="p-3 bg-green-500/20 rounded-full">
                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400">↗️ +8% from last week</span>
            </div>
        </div>

        <!-- Total Campaigns -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-blue-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Toplam Kampanya</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $stats['total_campaigns'] }}</p>
                </div>
                <div class="p-3 bg-blue-500/20 rounded-full">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400">↗️ +15% from last week</span>
            </div>
        </div>

        <!-- Active Campaigns -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-yellow-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-yellow-500/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Aktif Kampanya</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $stats['active_campaigns'] }}</p>
                </div>
                <div class="p-3 bg-yellow-500/20 rounded-full">
                    <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400">↗️ +22% from last week</span>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total FAQs -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Toplam SSS</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $stats['total_faqs'] }}</p>
                </div>
                <div class="p-3 bg-indigo-500/20 rounded-full">
                    <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Knowledge Bases -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Bilgi Tabanı</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $stats['total_knowledge_bases'] }}</p>
                </div>
                <div class="p-3 bg-pink-500/20 rounded-full">
                    <svg class="w-8 h-8 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Chat Sessions -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Chat Oturumu</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $stats['total_chat_sessions'] }}</p>
                </div>
                <div class="p-3 bg-teal-500/20 rounded-full">
                    <svg class="w-8 h-8 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Chat Sessions -->
    <div class="glass-effect rounded-xl p-6">
        <h3 class="text-xl font-semibold text-white mb-4">Son Chat Oturumları</h3>
        <div class="space-y-3">
            @forelse($stats['recent_chat_sessions'] as $session)
            <div class="flex items-center space-x-3 p-3 bg-gray-800/30 rounded-lg">
                <div class="w-10 h-10 bg-teal-500/20 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-white font-medium">{{ $session->user->name ?? 'Anonim' }}</p>
                    <p class="text-gray-400 text-sm">Session ID: {{ $session->session_id }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">{{ $session->created_at->diffForHumans() }}</p>
                    <p class="text-xs text-gray-400">{{ $session->created_at->format('H:i') }}</p>
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-center py-4">Henüz chat oturumu yok</p>
            @endforelse
        </div>
    </div>

    <!-- System Health -->
    <div class="glass-effect rounded-xl p-6">
        <h3 class="text-xl font-semibold text-white mb-4">Sistem Durumu</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                    <span class="text-green-400 font-medium">Veritabanı Bağlantısı</span>
                </div>
                <p class="text-green-300 text-sm mt-2">Aktif ve stabil</p>
            </div>
            
            <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                    <span class="text-green-400 font-medium">API Servisleri</span>
                </div>
                <p class="text-green-300 text-sm mt-2">Çalışıyor</p>
            </div>
            
            <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                    <span class="text-green-400 font-medium">Cache Sistemi</span>
                </div>
                <p class="text-green-300 text-sm mt-2">Optimize edilmiş</p>
            </div>
            
            <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                    <span class="text-green-400 font-medium">Güvenlik</span>
                </div>
                <p class="text-green-300 text-sm mt-2">Güncel ve güvenli</p>
            </div>
        </div>
    </div>
</div>
@endsection
