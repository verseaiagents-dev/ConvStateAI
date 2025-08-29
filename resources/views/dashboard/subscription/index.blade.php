@extends('layouts.dashboard')

@section('title', 'Plan Seçimi')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="glass-effect rounded-2xl p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-glow rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-neon-purple rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        
        <div class="relative z-10">
            <h1 class="text-4xl font-bold mb-4 text-white">Plan Seçimi</h1>
            <p class="text-xl text-gray-300">
                Size en uygun planı seçin ve tüm özellikleri kullanmaya başlayın
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-green-300">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <p class="text-red-300">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Current Subscription Status -->
    @if($currentSubscription)
        <div class="glass-effect rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-white">Mevcut Plan</h3>
                <div class="flex space-x-3">
                    <form action="{{ route('dashboard.subscription.renew') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors duration-200">
                            Planı Yenile
                        </button>
                    </form>
                    <form action="{{ route('dashboard.subscription.cancel') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition-colors duration-200">
                            İptal Et
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-gray-800/30 rounded-lg">
                    <h4 class="text-white font-medium mb-2">Plan Adı</h4>
                    <p class="text-gray-300">{{ $currentSubscription->plan->name }}</p>
                </div>
                <div class="p-4 bg-gray-800/30 rounded-lg">
                    <h4 class="text-white font-medium mb-2">Bitiş Tarihi</h4>
                    <p class="text-gray-300">{{ $currentSubscription->end_date->format('d.m.Y') }}</p>
                </div>
                <div class="p-4 bg-gray-800/30 rounded-lg">
                    <h4 class="text-white font-medium mb-2">Kalan Süre</h4>
                    <p class="text-gray-300">{{ $currentSubscription->days_remaining }} gün</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($plans as $plan)
            <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-purple-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-purple-500/10">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-white mb-2">{{ $plan->name }}</h3>
                    <div class="text-4xl font-bold text-purple-400 mb-1">
                        @if($plan->price == 0)
                            Ücretsiz
                        @else
                            ${{ number_format($plan->price, 2) }}
                        @endif
                    </div>
                    <p class="text-gray-400">{{ $plan->billing_cycle === 'monthly' ? 'Aylık' : 'Yıllık' }}</p>
                    @if($plan->name === 'Freemium')
                        <span class="inline-block mt-2 px-3 py-1 bg-yellow-500/20 text-yellow-400 text-sm rounded-full">
                            1 Hafta
                        </span>
                    @endif
                </div>

                <div class="space-y-3 mb-6">
                    @foreach($plan->features as $feature => $value)
                        @if($feature !== 'duration_days')
                            <div class="flex items-center space-x-3">
                                @if($value === true || $value === -1)
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @elseif($value === false)
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @endif
                                
                                <span class="text-gray-300">
                                    @switch($feature)
                                        @case('max_projects')
                                            @if($value == -1)
                                                Sınırsız Proje
                                            @else
                                                {{ $value }} Proje
                                            @endif
                                            @break
                                        @case('max_knowledge_bases')
                                            @if($value == -1)
                                                Sınırsız Knowledge Base
                                            @else
                                                {{ $value }} Knowledge Base
                                            @endif
                                            @break
                                        @case('max_chat_sessions')
                                            @if($value == -1)
                                                Sınırsız Chat Oturumu
                                            @else
                                                {{ $value }} Chat Oturumu
                                            @endif
                                            @break
                                        @case('ai_analysis')
                                            @if($value)
                                                AI Analiz
                                            @else
                                                AI Analiz Yok
                                            @endif
                                            @break
                                        @case('support')
                                            {{ $value }} Destek
                                            @break
                                        @default
                                            {{ $feature }}: {{ $value }}
                                    @endswitch
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>

                <form action="{{ route('dashboard.subscription.subscribe') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <button type="submit" class="w-full py-3 px-4 rounded-lg font-medium transition-all duration-200 
                        @if($currentSubscription && $currentSubscription->plan_id === $plan->id)
                            bg-gray-600 text-gray-300 cursor-not-allowed
                        @else
                            bg-purple-glow hover:bg-purple-dark text-white
                        @endif">
                        @if($currentSubscription && $currentSubscription->plan_id === $plan->id)
                            Mevcut Plan
                        @else
                            Plan Seç
                        @endif
                    </button>
                </form>
            </div>
        @endforeach
    </div>

    <!-- Features Comparison -->
    <div class="glass-effect rounded-xl p-6">
        <h3 class="text-xl font-semibold text-white mb-6">Özellik Karşılaştırması</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="py-3 px-4 text-gray-300 font-medium">Özellik</th>
                        @foreach($plans as $plan)
                            <th class="py-3 px-4 text-center text-gray-300 font-medium">{{ $plan->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <tr>
                        <td class="py-3 px-4 text-gray-300">Proje Limiti</td>
                        @foreach($plans as $plan)
                            <td class="py-3 px-4 text-center text-white">
                                @if($plan->features['max_projects'] == -1)
                                    Sınırsız
                                @else
                                    {{ $plan->features['max_projects'] }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-gray-300">Knowledge Base Limiti</td>
                        @foreach($plans as $plan)
                            <td class="py-3 px-4 text-center text-white">
                                @if($plan->features['max_knowledge_bases'] == -1)
                                    Sınırsız
                                @else
                                    {{ $plan->features['max_knowledge_bases'] }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-gray-300">Chat Oturumu Limiti</td>
                        @foreach($plans as $plan)
                            <td class="py-3 px-4 text-center text-white">
                                @if($plan->features['max_chat_sessions'] == -1)
                                    Sınırsız
                                @else
                                    {{ $plan->features['max_chat_sessions'] }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-gray-300">AI Analiz</td>
                        @foreach($plans as $plan)
                            <td class="py-3 px-4 text-center">
                                @if($plan->features['ai_analysis'])
                                    <span class="text-green-400">✓</span>
                                @else
                                    <span class="text-gray-500">✗</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-gray-300">Destek</td>
                        @foreach($plans as $plan)
                            <td class="py-3 px-4 text-center text-white">{{ $plan->features['support'] }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
