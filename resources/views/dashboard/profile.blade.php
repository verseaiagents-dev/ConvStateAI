@extends('layouts.dashboard')

@section('title', 'Profil')

@section('content')
<div class="space-y-6">
    <!-- Profile Header -->
    <div class="glass-effect rounded-2xl p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-glow rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-neon-purple rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        
        <div class="relative z-10">
            <div class="text-center">
                                        <img src="{{ asset('imgs/ai-conversion-logo.svg') }}" alt="ConvStateAI Logo" class="w-24 h-24 mx-auto border-4 border-purple-glow/30">
                <h2 class="text-2xl font-bold text-white mt-4">{{ $user->getDisplayName() }}</h2>
                <p class="text-gray-400">{{ $user->email }}</p>
                @if($user->isAdmin())
                    <span class="inline-block px-3 py-1 text-sm bg-purple-glow/20 text-purple-glow rounded-full mt-2">Admin</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Profile Form -->
    <div class="glass-effect rounded-2xl p-8">
        <h2 class="text-2xl font-bold mb-6 text-white">Profil Bilgileri</h2>
        
        <form method="POST" action="{{ route('dashboard.profile.update') }}" class="space-y-6">
            @csrf
            
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                    Ad Soyad
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $user->name) }}"
                       class="form-input w-full px-4 py-3 rounded-lg text-white placeholder-gray-400" 
                       placeholder="Adınızı ve soyadınızı girin"
                       required>
                @error('name')
                    <p class="mt-1 text-red-400 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email (Read-only) -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                    E-posta Adresi
                </label>
                <input type="email" 
                       id="email" 
                       value="{{ $user->email }}"
                       class="form-input w-full px-4 py-3 rounded-lg text-gray-500 bg-gray-800/50 cursor-not-allowed" 
                       readonly>
                <p class="mt-1 text-gray-400 text-sm">E-posta adresi değiştirilemez</p>
            </div>

            <!-- Bio -->
            <div>
                <label for="bio" class="block text-sm font-medium text-gray-300 mb-2">
                    Hakkımda
                </label>
                <textarea id="bio" 
                          name="bio" 
                          rows="4" 
                          class="form-input w-full px-4 py-3 rounded-lg text-white placeholder-gray-400 resize-none" 
                          placeholder="Kendiniz hakkında kısa bir açıklama yazın...">{{ old('bio', $user->bio) }}</textarea>
                @error('bio')
                    <p class="mt-1 text-red-400 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
                <button type="submit" 
                        class="px-8 py-3 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg text-white font-semibold hover:from-purple-dark hover:to-neon-purple transition-all duration-300 transform hover:scale-105">
                    Profili Güncelle
                </button>
            </div>
        </form>
    </div>

    <!-- Account Info -->
    <div class="glass-effect rounded-2xl p-8">
        <h2 class="text-2xl font-bold mb-6 text-white">Hesap Bilgileri</h2>
        
        <div class="grid md:grid-cols-2 gap-6">
            <!-- Member Since -->
            <div class="p-4 bg-gray-800/30 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-purple-glow/20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-glow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Üye Olma Tarihi</p>
                        <p class="text-white font-medium">{{ $user->created_at->format('d.m.Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Last Login -->
            <div class="p-4 bg-gray-800/30 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-neon-purple/20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-neon-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Son Giriş</p>
                        <p class="text-white font-medium">{{ $user->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>

            <!-- User ID -->
            <div class="p-4 bg-gray-800/30 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-purple-dark/20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Kullanıcı ID</p>
                        <p class="text-white font-medium">#{{ $user->id }}</p>
                    </div>
                </div>
            </div>

            <!-- Account Status -->
            <div class="p-4 bg-gray-800/30 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Hesap Durumu</p>
                        <p class="text-green-400 font-medium">Aktif</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="glass-effect rounded-2xl p-8 border border-red-500/20">
        <h2 class="text-2xl font-bold mb-6 text-red-400">Tehlikeli Bölge</h2>
        
        <div class="space-y-4">
            <div class="p-4 bg-red-500/10 rounded-lg border border-red-500/20">
                <h3 class="text-lg font-semibold text-red-400 mb-2">Hesabı Sil</h3>
                <p class="text-gray-300 mb-4">
                    Hesabınızı kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
                </p>
                <button class="px-4 py-2 bg-red-500 hover:bg-red-600 rounded-lg text-white font-medium transition-colors duration-200">
                    Hesabı Sil
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
