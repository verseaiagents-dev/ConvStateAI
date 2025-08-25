@extends('layouts.admin')

@section('title', 'Kullanıcı Yönetimi - Admin Panel')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="glass-effect rounded-2xl p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-glow rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-neon-purple rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        
        <div class="relative z-10">
            <h1 class="text-4xl font-bold mb-4">
                <span class="gradient-text">Kullanıcı Yönetimi</span> 👥
            </h1>
            <p class="text-xl text-gray-300 mb-6">
                Sistem kullanıcılarını görüntüleyin, yönetin ve takip edin.
            </p>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="glass-effect rounded-xl p-6">
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Kullanıcı ara..." 
                           class="w-full px-4 py-2 bg-gray-800/50 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20">
                    <svg class="absolute right-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="flex gap-2">
                <select id="statusFilter" class="px-4 py-2 bg-gray-800/50 border border-gray-600 rounded-lg text-white focus:border-purple-glow focus:outline-none">
                    <option value="">Tüm Durumlar</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Pasif</option>
                </select>
                
                <select id="roleFilter" class="px-4 py-2 bg-gray-800/50 border border-gray-600 rounded-lg text-white focus:border-purple-glow focus:outline-none">
                    <option value="">Tüm Roller</option>
                    <option value="admin">Admin</option>
                    <option value="user">Kullanıcı</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="glass-effect rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Kullanıcı</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">E-posta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Kayıt Tarihi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Son Giriş</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-800/30 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-500/20 rounded-full flex items-center justify-center">
                                    <span class="text-purple-400 font-medium">{{ substr($user->name, 0, 2) }}</span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white">{{ $user->name }}</div>
                                    @if($user->bio)
                                    <div class="text-sm text-gray-400">{{ Str::limit($user->bio, 30) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-300">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->is_admin)
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-500/20 text-purple-400">
                                Admin
                            </span>
                            @else
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-500/20 text-gray-400">
                                Kullanıcı
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            {{ $user->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            @if($user->last_login_at)
                            {{ $user->last_login_at->diffForHumans() }}
                            @else
                            <span class="text-gray-500">Hiç giriş yapmamış</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->email_verified_at)
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-400">
                                Doğrulanmış
                            </span>
                            @else
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-500/20 text-yellow-400">
                                Beklemede
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button class="text-blue-400 hover:text-blue-300 transition-colors" title="Düzenle">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                
                                @if($user->is_admin)
                                <button class="text-yellow-400 hover:text-yellow-300 transition-colors" title="Admin Yetkisini Kaldır">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636"></path>
                                    </svg>
                                </button>
                                @else
                                <button class="text-purple-400 hover:text-purple-300 transition-colors" title="Admin Yap">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                                @endif
                                
                                <button class="text-red-400 hover:text-red-300 transition-colors" title="Sil">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-400">
                            Henüz kullanıcı bulunamadı
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
    <div class="glass-effect rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-400">
                Toplam {{ $users->total() }} kullanıcıdan {{ $users->firstItem() }}-{{ $users->lastItem() }} arası gösteriliyor
            </div>
            
            <div class="flex space-x-2">
                @if($users->onFirstPage())
                <span class="px-3 py-2 text-gray-500 bg-gray-800/50 rounded-lg cursor-not-allowed">Önceki</span>
                @else
                <a href="{{ $users->previousPageUrl() }}" class="px-3 py-2 text-gray-300 bg-gray-800/50 rounded-lg hover:bg-gray-700/50 transition-colors">Önceki</a>
                @endif
                
                @foreach($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                <a href="{{ $url }}" class="px-3 py-2 text-gray-300 bg-gray-800/50 rounded-lg hover:bg-gray-700/50 transition-colors {{ $page == $users->currentPage() ? 'bg-purple-500/20 text-purple-400' : '' }}">
                    {{ $page }}
                </a>
                @endforeach
                
                @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="px-3 py-2 text-gray-300 bg-gray-800/50 rounded-lg hover:bg-gray-700/50 transition-colors">Sonraki</a>
                @else
                <span class="px-3 py-2 text-gray-500 bg-gray-800/50 rounded-lg cursor-not-allowed">Sonraki</span>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const name = row.querySelector('td:first-child').textContent.toLowerCase();
        const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        
        if (name.includes(searchTerm) || email.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Filter functionality
document.getElementById('statusFilter').addEventListener('change', filterUsers);
document.getElementById('roleFilter').addEventListener('change', filterUsers);

function filterUsers() {
    const statusFilter = document.getElementById('statusFilter').value;
    const roleFilter = document.getElementById('roleFilter').value;
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        let showRow = true;
        
        // Status filter
        if (statusFilter) {
            const status = row.querySelector('td:nth-child(6)').textContent;
            if (statusFilter === 'active' && !status.includes('Doğrulanmış')) showRow = false;
            if (statusFilter === 'inactive' && !status.includes('Beklemede')) showRow = false;
        }
        
        // Role filter
        if (roleFilter) {
            const role = row.querySelector('td:nth-child(3)').textContent;
            if (roleFilter === 'admin' && !role.includes('Admin')) showRow = false;
            if (roleFilter === 'user' && !role.includes('Kullanıcı')) showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
    });
}
</script>
@endsection
