@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="glass-effect rounded-2xl p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-glow rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-neon-purple rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        
        <div class="relative z-10">
            <h1 class="text-4xl font-bold mb-4">
                Hoş geldin, <span class="gradient-text">{{ $user->getDisplayName() }}</span>! 👋
            </h1>
            <p class="text-xl text-gray-300 mb-6">
                ConvStateAI dashboard'una hoş geldin. Buradan tüm işlemlerini yönetebilirsin.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('dashboard.profile') }}" class="px-6 py-3 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg text-white font-semibold hover:from-purple-dark hover:to-neon-purple transition-all duration-300 transform hover:scale-105">
                    Profili Düzenle
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="glass-effect rounded-2xl p-6">
        <h2 class="text-2xl font-bold mb-6 text-white">Hızlı İşlemler</h2>
        <div class="grid md:grid-cols-2 gap-4">
            <a href="{{ route('dashboard.profile') }}" class="p-4 bg-gray-800/30 rounded-lg hover:bg-gray-800/50 transition-all duration-200 group">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-purple-glow/20 rounded-lg flex items-center justify-center group-hover:bg-purple-glow/30 transition-all duration-200">
                        <svg class="w-5 h-5 text-purple-glow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">Profil Düzenle</p>
                        <p class="text-gray-400 text-sm">Kişisel bilgilerini güncelle</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('dashboard.settings') }}" class="p-4 bg-gray-800/30 rounded-lg hover:bg-gray-800/50 transition-all duration-200 group">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-neon-purple/20 rounded-lg flex items-center justify-center group-hover:bg-neon-purple/30 transition-all duration-200">
                        <svg class="w-5 h-5 text-neon-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">Ayarlar</p>
                        <p class="text-gray-400 text-sm">Hesap ayarlarını yönet</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
