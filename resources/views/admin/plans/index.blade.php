@extends('layouts.admin')

@section('title', 'Planlar - Admin Panel')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold gradient-text">Planlar</h1>
            <p class="mt-2 text-gray-400">Abonelik planlarını yönetin</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button onclick="openCreatePlanModal()" class="inline-flex items-center px-4 py-2 bg-purple-glow hover:bg-purple-dark text-white font-medium rounded-lg transition-all duration-200 hover:shadow-lg hover:shadow-purple-glow/25">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Plan Ekle
            </button>
        </div>
    </div>

    <!-- Plans List -->
    <div class="glass-effect rounded-xl border border-gray-700">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-300">Plan Adı</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-300">Fiyat</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-300">Döngü</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-300">Durum</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-300">Abonelik Sayısı</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-300">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @forelse($plans as $plan)
                        <tr class="hover:bg-gray-800/50 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 rounded-full {{ $plan->is_active ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                    <span class="font-medium text-white">{{ $plan->name }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-gray-300">
                                {{ $plan->formatted_price }}
                            </td>
                            <td class="py-4 px-4 text-gray-300">
                                {{ $plan->billing_cycle_text }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $plan->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-gray-300">
                                {{ $plan->subscriptions->count() }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.plans.edit', $plan) }}" class="text-blue-400 hover:text-blue-300 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="inline" onsubmit="return confirm('Bu planı silmek istediğinizden emin misiniz?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center text-gray-400">
                                Henüz plan bulunmuyor
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Plan Modal -->
<div id="createPlanModal" class="fixed inset-0 bg-black/50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-gray-700">
                <h3 class="text-xl font-semibold text-white">Yeni Plan Oluştur</h3>
                <button onclick="closeCreatePlanModal()" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[70vh]">
                <form action="{{ route('admin.plans.store') }}" method="POST" id="createPlanForm">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Plan Name -->
                        <div>
                            <label for="modal_name" class="block text-sm font-medium text-gray-300 mb-2">Plan Adı</label>
                            <input type="text" id="modal_name" name="name" required 
                                   class="form-input w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20"
                                   placeholder="Örn: Pro Plan">
                        </div>

                        <!-- Price -->
                        <div>
                            <label for="modal_price" class="block text-sm font-medium text-gray-300 mb-2">Fiyat</label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-gray-400">$</span>
                                <input type="number" id="modal_price" name="price" step="0.01" min="0" required 
                                       class="form-input w-full pl-8 pr-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20"
                                       placeholder="29.99">
                            </div>
                        </div>

                        <!-- Billing Cycle -->
                        <div>
                            <label for="modal_billing_cycle" class="block text-sm font-medium text-gray-300 mb-2">Faturalama Döngüsü</label>
                            <select id="modal_billing_cycle" name="billing_cycle" required 
                                    class="form-input w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20">
                                <option value="">Seçiniz</option>
                                <option value="monthly">Aylık</option>
                                <option value="yearly">Yıllık</option>
                            </select>
                        </div>

                        <!-- Is Active -->
                        <div>
                            <label for="modal_is_active" class="block text-sm font-medium text-gray-300 mb-2">Durum</label>
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" id="modal_is_active" name="is_active" value="1" checked
                                       class="form-checkbox w-5 h-5 text-purple-glow bg-gray-700 border-gray-600 rounded focus:ring-purple-glow focus:ring-2">
                                <span class="text-gray-300">Aktif</span>
                            </div>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Plan Özellikleri</label>
                        <div class="space-y-3" id="modalFeaturesContainer">
                            <div class="flex items-center space-x-3">
                                <input type="text" name="features[max_projects]" placeholder="Maksimum proje sayısı" 
                                       class="form-input flex-1 px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20">
                                <input type="text" name="features[max_knowledge_bases]" placeholder="Maksimum KB sayısı" 
                                       class="form-input flex-1 px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20">
                            </div>
                            <div class="flex items-center space-x-3">
                                <input type="text" name="features[max_chat_sessions]" placeholder="Maksimum chat session sayısı" 
                                       class="form-input flex-1 px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20">
                                <input type="text" name="features[ai_analysis]" placeholder="AI analizi (true/false)" 
                                       class="form-input flex-1 px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20">
                            </div>
                            <div class="flex items-center space-x-3">
                                <input type="text" name="features[support]" placeholder="Destek türü" 
                                       class="form-input flex-1 px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20">
                                <button type="button" onclick="addModalFeatureField()" class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-8 flex justify-end space-x-3">
                        <button type="button" onclick="closeCreatePlanModal()" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                            İptal
                        </button>
                        <button type="submit" class="px-6 py-3 bg-purple-glow hover:bg-purple-dark text-white font-medium rounded-lg transition-all duration-200 hover:shadow-lg hover:shadow-purple-glow/25">
                            Plan Oluştur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openCreatePlanModal() {
    document.getElementById('createPlanModal').classList.remove('hidden');
}

function closeCreatePlanModal() {
    document.getElementById('createPlanModal').classList.add('hidden');
    // Reset form
    document.getElementById('createPlanForm').reset();
}

function addModalFeatureField() {
    const container = document.getElementById('modalFeaturesContainer');
    const newField = document.createElement('div');
    newField.className = 'flex items-center space-x-3';
    newField.innerHTML = `
        <input type="text" name="features[custom_${Date.now()}]" placeholder="Özel özellik" 
               class="form-input flex-1 px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:border-purple-glow focus:outline-none focus:ring-2 focus:ring-purple-glow/20">
        <button type="button" onclick="this.parentElement.remove()" class="px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L6 6l12 12"></path>
            </svg>
        </button>
    `;
    container.appendChild(newField);
}

// Close modal when clicking outside
document.addEventListener('click', (e) => {
    if (e.target.id === 'createPlanModal') {
        closeCreatePlanModal();
    }
});

// Close modal on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeCreatePlanModal();
    }
});
</script>
@endsection
