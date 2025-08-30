@extends('layouts.dashboard')

@section('title', __('dashboard.dashboard'))

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="glass-effect rounded-2xl p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-glow rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-neon-purple rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        
        <div class="relative z-10">
            <h1 class="text-4xl font-bold mb-4">
                {{ __('dashboard.welcome') }}, <span class="gradient-text">{{ $user->getDisplayName() }}</span>! 👋
            </h1>
            <p class="text-xl text-gray-300 mb-6">
                {{ __('dashboard.welcome_description') }}
            </p>

            @if($user->hasActiveSubscription())
            @php
            $subscription = $user->activeSubscription;
            $daysRemaining = $subscription->days_remaining;
        @endphp 
     
             
                
                @if($daysRemaining <= 7)
                    <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-4">
                        <div class="flex items-center space-x-3">
                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div>
                                <h3 class="text-lg font-semibold text-red-400">{{ __('dashboard.plan_expiring_soon') }}</h3>
                                <p class="text-red-300">{{ __('dashboard.plan_expires_in_days', ['days' => $daysRemaining]) }}</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('dashboard.subscription.index') }}" class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition-colors duration-200">
                                {{ __('dashboard.renew_plan') }}
                            </a>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    @if($user->hasActiveSubscription())
        <!-- Project Overview -->
        <div class="glass-effect rounded-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-white">{{ __('dashboard.project_overview') }}</h3>
                <a href="{{ route('dashboard.projects') }}" class="px-4 py-2 bg-purple-glow hover:bg-purple-dark text-white font-medium rounded-lg transition-all duration-200">
                    {{ __('dashboard.view_all_projects') }}
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($projectStats as $project)
                    <div class="p-4 bg-gray-800/30 rounded-lg border border-gray-700 hover:border-purple-500/50 transition-all duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-white font-medium">{{ $project->name ?? __('dashboard.unnamed_project') }}</h4>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ ($project->status ?? '') === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                                {{ ($project->status ?? '') === 'active' ? __('dashboard.active') : __('dashboard.inactive') }}
                            </span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-400">{{ __('dashboard.knowledge_base_count') }}:</span>
                                <span class="text-white">{{ $project->knowledge_bases_count ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">{{ __('dashboard.chat_sessions_count') }}:</span>
                                <span class="text-white">{{ $project->chat_sessions_count ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-8">
                        <p class="text-gray-400 mb-4">{{ __('dashboard.no_projects_yet') }}</p>
                        <a href="{{ route('dashboard.projects') }}" class="px-6 py-3 bg-purple-glow hover:bg-purple-dark text-white font-medium rounded-lg transition-all duration-200">
                            {{ __('dashboard.create_first_project') }}
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Total Projects -->
            <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-purple-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-purple-500/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">{{ __('dashboard.total_projects') }}</p>
                        <p class="mt-2 text-3xl font-bold text-white">{{ $stats['total_projects'] }}</p>
                    </div>
                    <div class="p-3 bg-purple-500/20 rounded-full">
                        <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Knowledge Bases -->
            <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-blue-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">{{ __('dashboard.total_knowledge_bases') }}</p>
                        <p class="mt-2 text-3xl font-bold text-white">{{ $stats['total_knowledge_bases'] }}</p>
                    </div>
                    <div class="p-3 bg-blue-500/20 rounded-full">
                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Chat Sessions -->
            <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-green-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-green-500/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">{{ __('dashboard.total_chat_sessions') }}</p>
                        <p class="mt-2 text-3xl font-bold text-white">{{ $stats['total_chat_sessions'] }}</p>
                    </div>
                    <div class="p-3 bg-green-500/20 rounded-full">
                        <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Intents -->
            <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-yellow-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-yellow-500/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">{{ __('dashboard.total_intents') }}</p>
                        <p class="mt-2 text-3xl font-bold text-white">{{ $stats['total_intents'] }}</p>
                    </div>
                    <div class="p-3 bg-yellow-500/20 rounded-full">
                        <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Products -->
            <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-pink-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-pink-500/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">{{ __('dashboard.total_products') }}</p>
                        <p class="mt-2 text-3xl font-bold text-white">{{ $stats['total_products'] }}</p>
                    </div>
                    <div class="p-3 bg-pink-500/20 rounded-full">
                        <svg class="w-8 h-8 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Interactions -->
            <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-teal-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-teal-500/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">{{ __('dashboard.total_interactions') }}</p>
                        <p class="mt-2 text-3xl font-bold text-white">{{ $stats['total_interactions'] }}</p>
                    </div>
                    <div class="p-3 bg-teal-500/20 rounded-full">
                        <svg class="w-8 h-8 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Chat Session Trend Chart -->
            <div class="glass-effect rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">{{ __('dashboard.chat_trend_last_7_days') }}</h3>
                <div class="h-64 flex items-end justify-between space-x-2">
                    @php
                        $maxCount = !empty($chatTrend) ? max(array_values($chatTrend)) : 1;
                    @endphp
                    @foreach(range(6, 0) as $daysAgo)
                        @php
                            $date = now()->subDays($daysAgo)->format('Y-m-d');
                            $count = $chatTrend[$date] ?? 0;
                            $height = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                        @endphp
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-full bg-gradient-to-t from-purple-500/50 to-purple-500/20 rounded-t transition-all duration-300 hover:from-purple-500/70 hover:to-purple-500/40" style="height: {{ $height }}%"></div>
                            <div class="text-xs text-gray-400 mt-2 text-center">
                                <div>{{ $count }}</div>
                                <div class="text-xs">{{ now()->subDays($daysAgo)->format('d/m') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Knowledge Base Status -->
            <div class="glass-effect rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">{{ __('dashboard.knowledge_base_statuses') }}</h3>
                <div class="space-y-3">
                    @foreach($kbStatuses as $status => $count)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-300 capitalize">
                                @switch($status)
                                    @case('pending')
                                        Beklemede
                                        @break
                                    @case('processing')
                                        İşleniyor
                                        @break
                                    @case('completed')
                                        Tamamlandı
                                        @break
                                    @case('failed')
                                        Başarısız
                                        @break
                                    @case('mapped')
                                        Eşleştirildi
                                        @break
                                    @default
                                        {{ $status ?: 'Bilinmiyor' }}
                                @endswitch
                            </span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-700 rounded-full h-2">
                                    @php
                                        $total = !empty($kbStatuses) ? array_sum($kbStatuses) : 0;
                                        $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                                    @endphp
                                    <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-white font-medium">{{ $count }}</span>
                            </div>
                        </div>
                    @endforeach
                    @if(empty($kbStatuses))
                        <p class="text-gray-400 text-center py-4">{{ __('dashboard.no_knowledge_base_yet') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Intents -->
            <div class="glass-effect rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">{{ __('dashboard.top_intents') }}</h3>
                <div class="space-y-3">
                    @forelse($topIntents as $intent)
                        <div class="flex items-center justify-between p-3 bg-gray-800/30 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center">
                                    <span class="text-blue-400 text-sm font-medium">{{ $loop->iteration }}</span>
                                </div>
                                <div>
                                    <p class="text-white font-medium">{{ $intent->name ?? __('dashboard.unnamed_intent') }}</p>
                                    <p class="text-gray-400 text-sm">{{ $intent->keywords_count ?? 0 }} {{ __('dashboard.keywords_count') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">{{ $intent->created_at ? $intent->created_at->diffForHumans() : __('dashboard.unknown') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-400 text-center py-4">{{ __('dashboard.no_intents_yet') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Chat Sessions -->
            <div class="glass-effect rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">{{ __('dashboard.recent_chat_sessions') }}</h3>
                <div class="space-y-3">
                    @forelse($recentChatSessions as $session)
                        <div class="flex items-center space-x-3 p-3 bg-gray-800/30 rounded-lg">
                            <div class="w-10 h-10 bg-teal-500/20 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-white font-medium">{{ __('dashboard.session_id') }}: {{ $session->session_id ?? __('dashboard.unknown') }}</p>
                                <p class="text-gray-400 text-sm">{{ $session->created_at ? $session->created_at->diffForHumans() : __('dashboard.unknown') }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ ($session->status ?? '') === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                                    {{ ($session->status ?? '') === 'active' ? __('dashboard.active') : __('dashboard.inactive') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-400 text-center py-4">{{ __('dashboard.no_chat_sessions_yet') }}</p>
                    @endforelse
                </div>
            </div>
        </div>



        <!-- Quick Actions -->
        <div class="glass-effect rounded-2xl p-6">
            <h2 class="text-2xl font-bold mb-6 text-white">{{ __('dashboard.quick_actions') }}</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('dashboard.projects') }}" class="p-4 bg-gray-800/30 rounded-lg hover:bg-gray-800/50 transition-all duration-200 group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-purple-glow/20 rounded-lg flex items-center justify-center group-hover:bg-purple-glow/30 transition-all duration-200">
                            <svg class="w-5 h-5 text-purple-glow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-medium">{{ __('dashboard.projects') }}</p>
                            <p class="text-gray-400 text-sm">{{ __('dashboard.manage_projects') }}</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('dashboard.knowledge-base') }}" class="p-4 bg-gray-800/30 rounded-lg hover:bg-gray-800/50 transition-all duration-200 group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center group-hover:bg-blue-500/30 transition-all duration-200">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-medium">{{ __('dashboard.knowledge_base') }}</p>
                            <p class="text-gray-400 text-sm">{{ __('dashboard.manage_knowledge_base') }}</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('dashboard.chat-sessions') }}" class="p-4 bg-gray-800/30 rounded-lg hover:bg-gray-800/50 transition-all duration-200 group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center group-hover:bg-green-500/30 transition-all duration-200">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-medium">{{ __('dashboard.chat_sessions') }}</p>
                            <p class="text-gray-400 text-sm">{{ __('dashboard.track_conversations') }}</p>
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
                            <p class="text-white font-medium">{{ __('dashboard.settings') }}</p>
                            <p class="text-gray-400 text-sm">{{ __('dashboard.manage_account_settings') }}</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    @else
        <!-- Subscription Required Message -->
        <div class="glass-effect rounded-xl p-8 text-center">
            <div class="max-w-md mx-auto">
                <div class="w-20 h-20 bg-gray-600/30 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">{{ __('dashboard.dashboard_features_locked') }}</h3>
                <p class="text-gray-300 mb-6">
                    {{ __('dashboard.dashboard_features_locked_description') }}
                </p>
                <a href="{{ route('dashboard.subscription.index') }}" class="inline-flex items-center px-6 py-3 bg-purple-glow hover:bg-purple-dark text-white font-medium rounded-lg transition-all duration-200">
                    {{ __('dashboard.choose_plan_and_start') }}
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
