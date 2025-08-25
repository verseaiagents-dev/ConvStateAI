@extends('layouts.dashboard')

@section('title', 'Chat Sessions Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold gradient-text">Chat Sessions Dashboard</h1>
            <p class="mt-2 text-gray-400">Chat session istatistikleri ve yönetimi</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('dashboard.chat-sessions.export') }}" class="inline-flex items-center px-4 py-2 bg-purple-glow hover:bg-purple-dark text-white font-medium rounded-lg transition-all duration-200 hover:shadow-lg hover:shadow-purple-glow/25">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                CSV Export
            </a>
        </div>
    </div>

    <!-- Stats Cards Row 1 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Sessions -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-purple-glow/50 transition-all duration-300 hover:shadow-lg hover:shadow-purple-glow/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Total Sessions</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ number_format($stats['overview']['total_sessions']) }}</p>
                </div>
                <div class="p-3 bg-purple-glow/20 rounded-full">
                    <svg class="w-8 h-8 text-purple-glow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Today -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-green-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-green-500/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Active Today</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ number_format($stats['overview']['active_today']) }}</p>
                </div>
                <div class="p-3 bg-green-500/20 rounded-full">
                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Interactions Today -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-blue-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Interactions Today</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ number_format($stats['overview']['total_interactions_today']) }}</p>
                </div>
                <div class="p-3 bg-blue-500/20 rounded-full">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.122 2.122"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row 2 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Conversion Rate Today -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-yellow-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-yellow-500/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Conversion Rate Today</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $stats['overview']['conversion_rate_today'] }}%</p>
                </div>
                <div class="p-3 bg-yellow-500/20 rounded-full">
                    <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Weekly Sessions -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-indigo-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-indigo-500/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Weekly Sessions</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ number_format($stats['trends']['weekly_sessions']) }}</p>
                </div>
                <div class="p-3 bg-indigo-500/20 rounded-full">
                    <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Monthly Sessions -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-pink-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-pink-500/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Monthly Sessions</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ number_format($stats['trends']['monthly_sessions']) }}</p>
                </div>
                <div class="p-3 bg-pink-500/20 rounded-full">
                    <svg class="w-8 h-8 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Intent Distribution Chart -->
        <div class="glass-effect rounded-xl border border-gray-700">
            <div class="p-6 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-white">Top Intents</h3>
            </div>
            <div class="p-6">
                <div class="chart-pie pt-4 pb-2" style="height: 300px;">
                    <canvas id="intentChart"></canvas>
                </div>
                <div class="mt-6 flex flex-wrap justify-center gap-2">
                    @foreach(array_slice($stats['intent_distribution'], 0, 5) as $intent => $count)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-glow/20 text-purple-glow border border-purple-glow/30">
                            <span class="w-2 h-2 bg-purple-glow rounded-full mr-2"></span>
                            {{ ucfirst($intent) }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Action Distribution Chart -->
        <div class="glass-effect rounded-xl border border-gray-700">
            <div class="p-6 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-white">Action Distribution</h3>
            </div>
            <div class="p-6">
                <div class="chart-pie pt-4 pb-2" style="height: 300px;">
                    <canvas id="actionChart"></canvas>
                </div>
                <div class="mt-6 flex flex-wrap justify-center gap-2">
                    @foreach(array_slice($stats['action_distribution'], 0, 5) as $action => $count)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                            <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                            {{ ucfirst($action) }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions Table -->
    <div class="glass-effect rounded-xl border border-gray-700">
        <div class="p-6 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Chat Sessions</h3>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Session ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Last Activity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Daily Usage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Total Interactions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-transparent divide-y divide-gray-700">
                        @foreach($sessions as $session)
                        <tr class="hover:bg-gray-800/30 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="text-xs bg-gray-800 px-2 py-1 rounded text-purple-glow">{{ Str::limit($session->session_id, 20) }}</code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($session->user)
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-purple-glow/20 rounded-full flex items-center justify-center">
                                            <span class="text-sm font-medium text-purple-glow">{{ substr($session->user->name, 0, 1) }}</span>
                                        </div>
                                        <span class="ml-3 text-sm text-white">{{ $session->user->name }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">Guest</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($session->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                                        Active
                                    </span>
                                @elseif($session->status === 'expired')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                                        Expired
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-500/20 text-gray-400 border border-gray-500/30">
                                        {{ ucfirst($session->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                @if($session->last_activity)
                                    {{ $session->last_activity->diffForHumans() }}
                                @else
                                    <span class="text-gray-500">Never</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full bg-gray-700 rounded-full h-2">
                                    @php
                                        $usagePercent = ($session->daily_view_count / $session->daily_view_limit) * 100;
                                        $progressClass = $usagePercent > 80 ? 'bg-red-500' : ($usagePercent > 60 ? 'bg-yellow-500' : 'bg-green-500');
                                    @endphp
                                    <div class="h-2 rounded-full transition-all duration-300 {{ $progressClass }}" 
                                         style="width: {{ $usagePercent }}%"></div>
                                </div>
                                <div class="text-xs text-gray-400 mt-1">{{ $session->daily_view_count }}/{{ $session->daily_view_limit }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                    {{ $session->productInteractions->count() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('dashboard.chat-sessions.show', $session->session_id) }}" 
                                   class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-purple-glow hover:bg-purple-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-glow transition-all duration-200">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 flex justify-center">
                {{ $sessions->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Intent Distribution Chart
    const intentCtx = document.getElementById('intentChart').getContext('2d');
    const intentData = @json($stats['intent_distribution']);
    
    new Chart(intentCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(intentData),
            datasets: [{
                data: Object.values(intentData),
                backgroundColor: [
                    '#8b5cf6', '#a855f7', '#ec4899', '#f59e0b', '#10b981',
                    '#3b82f6', '#6366f1', '#8b5cf6', '#06b6d4', '#84cc16'
                ],
                hoverBackgroundColor: [
                    '#7c3aed', '#9333ea', '#db2777', '#d97706', '#059669',
                    '#2563eb', '#4f46e5', '#7c3aed', '#0891b2', '#65a30d'
                ],
                borderWidth: 2,
                borderColor: '#1f2937'
            }],
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(31, 41, 55, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#d1d5db',
                    borderColor: '#4b5563',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            cutout: '70%',
            elements: {
                arc: {
                    borderWidth: 0
                }
            }
        },
    });

    // Action Distribution Chart
    const actionCtx = document.getElementById('actionChart').getContext('2d');
    const actionData = @json($stats['action_distribution']);
    
    new Chart(actionCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(actionData),
            datasets: [{
                data: Object.values(actionData),
                backgroundColor: [
                    '#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6',
                    '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#6366f1'
                ],
                hoverBackgroundColor: [
                    '#059669', '#2563eb', '#d97706', '#dc2626', '#7c3aed',
                    '#0891b2', '#65a30d', '#ea580c', '#db2777', '#4f46e5'
                ],
                borderWidth: 2,
                borderColor: '#1f2937'
            }],
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(31, 41, 55, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#d1d5db',
                    borderColor: '#4b5563',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            cutout: '70%',
            elements: {
                arc: {
                    borderWidth: 0
                }
            }
        },
    });
});
</script>
@endsection
