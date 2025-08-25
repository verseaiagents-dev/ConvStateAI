@extends('layouts.dashboard')

@section('title', 'Ayarlar')

@section('content')
<div class="space-y-6">
    <!-- Settings Header -->
    <div class="glass-effect rounded-2xl p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-glow rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-neon-purple rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        
        <div class="relative z-10">
            <h1 class="text-4xl font-bold mb-4">
                <span class="gradient-text">Hesap Ayarları</span>
            </h1>
            <p class="text-xl text-gray-300">
                Güvenlik ve hesap ayarlarınızı buradan yönetebilirsiniz.
            </p>
        </div>
    </div>

    <!-- Password Change -->
    <div class="glass-effect rounded-2xl p-8">
        <h2 class="text-2xl font-bold mb-6 text-white">Şifre Değiştir</h2>
        
        <form method="POST" action="{{ route('dashboard.password.update') }}" class="space-y-6">
            @csrf
            
            <!-- Current Password -->
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-300 mb-2">
                    Mevcut Şifre
                </label>
                <input type="password" 
                       id="current_password" 
                       name="current_password" 
                       class="form-input w-full px-4 py-3 rounded-lg text-white placeholder-gray-400" 
                       placeholder="Mevcut şifrenizi girin"
                       required>
                @error('current_password')
                    <p class="mt-1 text-red-400 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- New Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                    Yeni Şifre
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-input w-full px-4 py-3 rounded-lg text-white placeholder-gray-400" 
                       placeholder="En az 8 karakter"
                       required>
                @error('password')
                    <p class="mt-1 text-red-400 text-sm">{{ $message }}</p>
                @enderror
                <div class="mt-2 space-y-1">
                    <div class="flex items-center space-x-2">
                        <div id="length-check" class="w-2 h-2 rounded-full bg-gray-600"></div>
                        <span class="text-xs text-gray-400">En az 8 karakter</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div id="uppercase-check" class="w-2 h-2 rounded-full bg-gray-600"></div>
                        <span class="text-xs text-gray-400">Büyük harf</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div id="number-check" class="w-2 h-2 rounded-full bg-gray-600"></div>
                        <span class="text-xs text-gray-400">Rakam</span>
                    </div>
                </div>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">
                    Yeni Şifre Tekrar
                </label>
                <input type="password" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       class="form-input w-full px-4 py-3 rounded-lg text-white placeholder-gray-400" 
                       placeholder="Yeni şifrenizi tekrar girin"
                       required>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
                <button type="submit" 
                        class="px-8 py-3 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg text-white font-semibold hover:from-purple-dark hover:to-neon-purple transition-all duration-300 transform hover:scale-105">
                    Şifreyi Güncelle
                </button>
            </div>
        </form>
    </div>

    <!-- Notification Settings -->
    <div class="glass-effect rounded-2xl p-8">
        <h2 class="text-2xl font-bold mb-6 text-white">Bildirim Ayarları</h2>
        
        <div class="space-y-6">
            <!-- Email Notifications -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-4">E-posta Bildirimleri</h3>
                <div class="space-y-3">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-purple-glow bg-gray-800 border-gray-600 rounded focus:ring-purple-glow focus:ring-2" checked>
                        <span class="text-gray-300">Hesap güvenliği bildirimleri</span>
                    </label>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-purple-glow bg-gray-800 border-gray-600 rounded focus:ring-purple-glow focus:ring-2" checked>
                        <span class="text-gray-300">Yeni özellik duyuruları</span>
                    </label>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-purple-glow bg-gray-800 border-gray-600 rounded focus:ring-purple-glow focus:ring-2">
                        <span class="text-gray-300">Haftalık raporlar</span>
                    </label>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-purple-glow bg-gray-800 border-gray-600 rounded focus:ring-purple-glow focus:ring-2" checked>
                        <span class="text-gray-300">Önemli güncellemeler</span>
                    </label>
                </div>
            </div>

            <!-- Push Notifications -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-4">Push Bildirimleri</h3>
                <div class="space-y-3">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-purple-glow bg-gray-800 border-gray-600 rounded focus:ring-purple-glow focus:ring-2">
                        <span class="text-gray-300">Tarayıcı push bildirimleri</span>
                    </label>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-purple-glow bg-gray-800 border-gray-600 rounded focus:ring-purple-glow focus:ring-2" checked>
                        <span class="text-gray-300">Sistem bildirimleri</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Privacy Settings -->
    <div class="glass-effect rounded-2xl p-8">
        <h2 class="text-2xl font-bold mb-6 text-white">Gizlilik Ayarları</h2>
        
        <div class="space-y-6">
            <!-- Profile Visibility -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-4">Profil Görünürlüğü</h3>
                <div class="space-y-3">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="radio" name="profile_visibility" class="w-4 h-4 text-purple-glow bg-gray-800 border-gray-600 focus:ring-purple-glow focus:ring-2" checked>
                        <span class="text-gray-300">Herkese açık</span>
                    </label>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="radio" name="profile_visibility" class="w-4 h-4 text-purple-glow bg-gray-800 border-gray-600 focus:ring-purple-glow focus:ring-2">
                        <span class="text-gray-300">Sadece arkadaşlar</span>
                    </label>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="radio" name="profile_visibility" class="w-4 h-4 text-purple-glow bg-gray-800 border-gray-600 focus:ring-purple-glow focus:ring-2">
                        <span class="text-gray-300">Gizli</span>
                    </label>
                </div>
            </div>

            <!-- Data Collection -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-4">Veri Toplama</h3>
                <div class="space-y-3">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-purple-glow bg-gray-800 border-gray-600 rounded focus:ring-purple-glow focus:ring-2" checked>
                        <span class="text-gray-300">Kullanım analitikleri</span>
                    </label>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-purple-glow bg-gray-800 border-gray-600 rounded focus:ring-purple-glow focus:ring-2" checked>
                        <span class="text-gray-300">Hata raporları</span>
                    </label>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-purple-glow bg-gray-800 border-gray-600 rounded focus:ring-purple-glow focus:ring-2">
                        <span class="text-gray-300">Kişiselleştirilmiş reklamlar</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="glass-effect rounded-2xl p-8">
        <h2 class="text-2xl font-bold mb-6 text-white">Güvenlik Ayarları</h2>
        
        <div class="space-y-6">
            <!-- Two-Factor Authentication -->
            <div class="flex items-center justify-between p-4 bg-gray-800/30 rounded-lg">
                <div>
                    <h3 class="text-lg font-semibold text-white">İki Faktörlü Doğrulama</h3>
                    <p class="text-gray-400 text-sm">Hesabınızı ekstra güvenlik katmanı ile koruyun</p>
                </div>
                <button class="px-4 py-2 bg-purple-glow hover:bg-purple-dark rounded-lg text-white font-medium transition-colors duration-200">
                    Etkinleştir
                </button>
            </div>

            <!-- Login History -->
            <div class="flex items-center justify-between p-4 bg-gray-800/30 rounded-lg">
                <div>
                    <h3 class="text-lg font-semibold text-white">Giriş Geçmişi</h3>
                    <p class="text-gray-400 text-sm">Hesabınıza yapılan girişleri görüntüleyin</p>
                </div>
                <button class="px-4 py-2 glass-effect rounded-lg text-white font-medium hover:bg-white hover:text-black transition-all duration-200">
                    Görüntüle
                </button>
            </div>

            <!-- Active Sessions -->
            <div class="flex items-center justify-between p-4 bg-gray-800/30 rounded-lg">
                <div>
                    <h3 class="text-lg font-semibold text-white">Aktif Oturumlar</h3>
                    <p class="text-gray-400 text-sm">Şu anda aktif olan oturumları yönetin</p>
                </div>
                <button class="px-4 py-2 glass-effect rounded-lg text-white font-medium hover:bg-white hover:text-black transition-all duration-200">
                    Yönet
                </button>
            </div>
        </div>
    </div>

    <!-- Save Settings -->
    <div class="glass-effect rounded-2xl p-8">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-white">Ayarları Kaydet</h3>
                <p class="text-gray-400 text-sm">Tüm değişiklikleri kaydetmek için aşağıdaki butona tıklayın</p>
            </div>
            <button class="px-8 py-3 bg-gradient-to-r from-green-500 to-green-600 rounded-lg text-white font-semibold hover:from-green-600 hover:to-green-700 transition-all duration-300 transform hover:scale-105">
                Tüm Ayarları Kaydet
            </button>
        </div>
    </div>
</div>

<script>
    // Password strength checker
    const passwordInput = document.getElementById('password');
    
    function checkPasswordStrength(password) {
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            number: /\d/.test(password)
        };

        // Update visual indicators
        document.getElementById('length-check').className = `w-2 h-2 rounded-full ${checks.length ? 'bg-green-400' : 'bg-gray-600'}`;
        document.getElementById('uppercase-check').className = `w-2 h-2 rounded-full ${checks.uppercase ? 'bg-green-400' : 'bg-gray-600'}`;
        document.getElementById('number-check').className = `w-2 h-2 rounded-full ${checks.number ? 'bg-green-400' : 'bg-gray-600'}`;
    }

    passwordInput.addEventListener('input', function() {
        checkPasswordStrength(this.value);
    });
</script>
@endsection
