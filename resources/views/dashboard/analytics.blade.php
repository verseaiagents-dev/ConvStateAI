@extends('layouts.dashboard')

@section('title', __('dashboard.real_time_analytics'))

@section('content')
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="space-y-6">
    <!-- Analytics Dashboard Header -->
    <div class="glass-effect rounded-2xl p-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-glow rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-neon-purple rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
        
        <div class="relative z-10">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-4xl font-bold mb-4">
                        <span class="gradient-text">{{ __('dashboard.real_time_analytics_dashboard') }}</span>
                    </h1>
                    <p class="text-xl text-gray-300">
                        {{ __('dashboard.real_time_data') }}
                    </p>
                </div>
                
                <div class="flex space-x-3">
                    <button onclick="refreshAnalytics()" class="px-6 py-3 bg-gradient-to-r from-purple-glow to-neon-purple rounded-lg text-white font-semibold hover:from-purple-dark hover:to-neon-purple transition-all duration-300 transform hover:scale-105 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>{{ __('dashboard.refresh') }}</span>
                    </button>
                    <button onclick="exportAnalytics()" class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 rounded-lg text-white font-semibold hover:from-green-700 hover:to-green-800 transition-all duration-300 transform hover:scale-105 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>{{ __('dashboard.export') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Stats Row 1 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Active Sessions -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-purple-glow/50 transition-all duration-300 hover:shadow-lg hover:shadow-purple-glow/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">{{ __('dashboard.active_sessions') }}</p>
                    <p class="mt-2 text-3xl font-bold text-white" id="activeSessions">-</p>
                </div>
                <div class="p-3 bg-purple-glow/20 rounded-full">
                    <svg class="w-8 h-8 text-purple-glow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400" id="activeSessionsChange">{{ __('dashboard.loading') }}</span>
            </div>
        </div>

        <!-- Interactions Last Hour -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-green-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-green-500/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">{{ __('dashboard.interactions_last_hour') }}</p>
                    <p class="mt-2 text-3xl font-bold text-white" id="interactionsLastHour">-</p>
                </div>
                <div class="p-3 bg-green-500/20 rounded-full">
                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400" id="interactionsChange">{{ __('dashboard.loading') }}</span>
            </div>
        </div>

        <!-- Conversion Rate -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-blue-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">{{ __('dashboard.conversion_rate') }}</p>
                    <p class="mt-2 text-3xl font-bold text-white" id="conversionRate">-</p>
                </div>
                <div class="p-3 bg-blue-500/20 rounded-full">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400" id="conversionChange">{{ __('dashboard.loading') }}</span>
            </div>
        </div>

        <!-- Avg Session Duration -->
        <div class="glass-effect rounded-xl p-6 border border-gray-700 hover:border-yellow-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">{{ __('dashboard.avg_session_duration') }}</p>
                    <p class="mt-2 text-3xl font-bold text-white" id="avgSessionDuration">-</p>
                </div>
                <div class="p-3 bg-yellow-500/20 rounded-full">
                    <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400" id="durationChange">Loading...</span>
            </div>
        </div>
    </div>



    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Real-time Activity Chart -->
        <div class="glass-effect rounded-xl border border-gray-700">
            <div class="p-6 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-white">Real-time Activity</h3>
            </div>
            <div class="p-6">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="realTimeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- User Engagement Chart -->
        <div class="glass-effect rounded-xl border border-gray-700">
            <div class="p-6 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-white">User Engagement</h3>
            </div>
            <div class="p-6">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="engagementChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="glass-effect rounded-xl border border-gray-700">
        <div class="p-6 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Performance Metrics</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Response Time -->
                <div class="text-center">
                    <div class="text-3xl font-bold text-white mb-2" id="avgResponseTime">-</div>
                    <div class="text-sm text-gray-400">Average Response Time (ms)</div>
                    <div class="mt-2 flex items-center justify-center">
                        <span class="text-gray-400 text-sm" id="responseTimeChange">Loading...</span>
                    </div>
                </div>

                <!-- Error Rate -->
                <div class="text-center">
                    <div class="text-3xl font-bold text-white mb-2" id="errorRate">-</div>
                    <div class="text-sm text-gray-400">Success Rate (%)</div>
                    <div class="mt-2 flex items-center justify-center">
                        <span class="text-gray-400 text-sm" id="errorRateChange">Loading...</span>
                    </div>
                </div>

                <!-- User Satisfaction -->
                <div class="text-center">
                    <div class="text-3xl font-bold text-white mb-2" id="userSatisfaction">-</div>
                    <div class="text-sm text-gray-400">User Satisfaction Score</div>
                    <div class="mt-2 flex items-center justify-center">
                        <span class="text-gray-400 text-sm" id="satisfactionChange">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Sessions -->
    <div class="glass-effect rounded-xl border border-gray-700">
        <div class="p-6 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Live Sessions</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4" id="liveSessionsContainer">
                <div class="text-center text-gray-400 py-8">
                    <svg class="w-8 h-8 mx-auto mb-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Loading live sessions...
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Interactions -->
    <div class="glass-effect rounded-xl border border-gray-700">
        <div class="p-6 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Recent Interactions</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4" id="recentInteractionsContainer">
                <div class="text-center text-gray-400 py-8">
                    <svg class="w-8 h-8 mx-auto mb-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Loading recent interactions...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let realTimeChart, engagementChart;
let previousData = {};
let activeRequests = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    initializeCharts();
    
    // Load initial data
    loadAnalyticsData();
    
    // Set up real-time updates
    setInterval(function() {
        loadAnalyticsData();
    }, 30000); // Update every 30 seconds
    

});

function initializeCharts() {
    // Real-time Activity Chart
    const realTimeCtx = document.getElementById('realTimeChart').getContext('2d');
    realTimeChart = new Chart(realTimeCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Active Sessions',
                data: [],
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }, {
                label: 'Interactions',
                data: [],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: '#9ca3af'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(31, 41, 55, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#d1d5db',
                    borderColor: '#4b5563',
                    borderWidth: 1,
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(75, 85, 99, 0.3)'
                    },
                    ticks: {
                        color: '#9ca3af'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(75, 85, 99, 0.3)'
                    },
                    ticks: {
                        color: '#9ca3af'
                    }
                }
            }
        }
    });

    // User Engagement Chart
    const engagementCtx = document.getElementById('engagementChart').getContext('2d');
    engagementChart = new Chart(engagementCtx, {
        type: 'doughnut',
        data: {
            labels: ['Chat', 'Search', 'Browse', 'Purchase'],
            datasets: [{
                data: [0, 0, 0, 0],
                backgroundColor: [
                    '#8b5cf6',
                    '#10b981',
                    '#3b82f6',
                    '#f59e0b'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#9ca3af',
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(31, 41, 55, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#d1d5db',
                    borderColor: '#4b5563',
                    borderWidth: 1,
                    cornerRadius: 8
                }
            },
            cutout: '60%'
        }
    });
}

async function loadAnalyticsData() {
    try {
        activeRequests++;
        const response = await fetch('/api/analytics/real-time', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        
        if (response.ok) {
            updateDashboard(data);
            updateCharts(data);
            updateLiveSessions(data.live_sessions || []);
            updateRecentInteractions(data.recent_interactions || []);
        } else {
            console.error('Failed to load analytics data:', data.error);
        }
    } catch (error) {
        console.error('Error loading analytics data:', error);
    } finally {
        activeRequests--;
    }
}



function updateDashboard(data) {
    // Update main metrics
    document.getElementById('activeSessions').textContent = data.active_sessions || 0;
    document.getElementById('interactionsLastHour').textContent = data.interactions_last_hour || 0;
    document.getElementById('conversionRate').textContent = (data.conversion_rate || 0).toFixed(1) + '%';
    document.getElementById('avgSessionDuration').textContent = (data.avg_session_duration || 0).toFixed(1) + 'm';
    
    // Update performance metrics
    if (data.performance_metrics) {
        document.getElementById('avgResponseTime').textContent = data.performance_metrics.avg_response_time || 0;
        document.getElementById('errorRate').textContent = data.performance_metrics.success_rate || 0;
        document.getElementById('userSatisfaction').textContent = data.performance_metrics.user_satisfaction || 0;
    }
    
    // Calculate changes
    calculateChanges(data);
}

function calculateChanges(currentData) {
    if (!previousData.active_sessions) {
        // First load, no changes to show
        previousData = currentData;
        return;
    }
    
    // Calculate percentage changes
    const activeSessionsChange = calculatePercentageChange(previousData.active_sessions, currentData.active_sessions);
    const interactionsChange = calculatePercentageChange(previousData.interactions_last_hour, currentData.interactions_last_hour);
    const conversionChange = calculatePercentageChange(previousData.conversion_rate, currentData.conversion_rate);
    const durationChange = calculatePercentageChange(previousData.avg_session_duration, currentData.avg_session_duration);
    
    // Update change indicators
    updateChangeIndicator('activeSessionsChange', activeSessionsChange, 'Active Sessions');
    updateChangeIndicator('interactionsChange', interactionsChange, 'Interactions');
    updateChangeIndicator('conversionChange', conversionChange, 'Conversion Rate');
    updateChangeIndicator('durationChange', durationChange, 'Session Duration');
    
    previousData = currentData;
}

function calculatePercentageChange(oldValue, newValue) {
    if (oldValue === 0) return newValue > 0 ? 100 : 0;
    return ((newValue - oldValue) / oldValue) * 100;
}

function updateChangeIndicator(elementId, change, label) {
    const element = document.getElementById(elementId);
    if (change > 0) {
        element.innerHTML = `<span class="text-green-400">+${change.toFixed(1)}%</span> <span class="text-gray-400 ml-2">vs previous</span>`;
    } else if (change < 0) {
        element.innerHTML = `<span class="text-red-400">${change.toFixed(1)}%</span> <span class="text-gray-400 ml-2">vs previous</span>`;
    } else {
        element.innerHTML = `<span class="text-gray-400">No change</span>`;
    }
}

function updateCharts(data) {
    if (data.hourly_data && realTimeChart) {
        const labels = Object.keys(data.hourly_data);
        const sessionsData = labels.map(key => data.hourly_data[key].sessions || 0);
        const interactionsData = labels.map(key => data.hourly_data[key].interactions || 0);
        
        realTimeChart.data.labels = labels;
        realTimeChart.data.datasets[0].data = sessionsData;
        realTimeChart.data.datasets[1].data = interactionsData;
        realTimeChart.update();
    }
    
    if (data.intent_distribution && engagementChart) {
        const labels = Object.keys(data.intent_distribution);
        const values = Object.values(data.intent_distribution);
        
        engagementChart.data.labels = labels;
        engagementChart.data.datasets[0].data = values;
        engagementChart.update();
    }
}

function updateLiveSessions(sessions) {
    const container = document.getElementById('liveSessionsContainer');
    
    if (sessions.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-400 py-8">No active sessions at the moment</div>';
        return;
    }
    
    const sessionsHtml = sessions.map(session => `
        <div class="flex items-center justify-between p-4 bg-gray-800/30 rounded-lg">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-purple-glow/20 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-glow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-white font-medium">Session ${session.session_id}</p>
                    <p class="text-gray-400 text-sm">${session.intent_count} intents, ${session.interaction_count} interactions</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-400">${session.last_activity}</div>
                <div class="text-xs text-green-400">${session.status}</div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = sessionsHtml;
}

function updateRecentInteractions(interactions) {
    const container = document.getElementById('recentInteractionsContainer');
    
    if (interactions.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-400 py-8">No recent interactions</div>';
        return;
    }
    
    const interactionsHtml = interactions.map(interaction => `
        <div class="flex items-center justify-between p-4 bg-gray-800/30 rounded-lg">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-green-500/20 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-white font-medium">${interaction.action}</p>
                    <p class="text-gray-400 text-sm">${interaction.product_name || 'N/A'} - ${interaction.timestamp}</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-400">Session ${interaction.session_id}</div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = interactionsHtml;
}

function refreshAnalytics() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Refreshing...';
    button.disabled = true;
    
    loadAnalyticsData().finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

async function exportAnalytics() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Exporting...';
    button.disabled = true;
    
    try {
        activeRequests++;
        const response = await fetch('/api/analytics/export?format=csv', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        
        if (response.ok) {
            // Create and download CSV file
            const csvContent = convertToCSV(data.data);
            downloadCSV(csvContent, data.filename);
        } else {
            alert('Failed to export analytics data: ' + data.error);
        }
    } catch (error) {
        console.error('Export error:', error);
        alert('Failed to export analytics data');
    } finally {
        activeRequests--;
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

function convertToCSV(data) {
    if (!data.summary || !data.sessions || !data.interactions) {
        return 'No data available';
    }
    
    let csv = 'Summary\n';
    csv += 'Total Sessions,Total Interactions,Conversion Rate,Avg Session Duration,Unique Users\n';
    csv += `${data.summary.total_sessions},${data.summary.total_interactions},${data.summary.conversion_rate}%,${data.summary.avg_session_duration}m,${data.summary.unique_users}\n\n`;
    
    csv += 'Sessions\n';
    csv += 'Session ID,User ID,Status,Created At,Last Activity,Intent Count,Interaction Count,Daily View Count,Daily View Limit\n';
    data.sessions.forEach(session => {
        csv += `${session.session_id},${session.user_id || 'N/A'},${session.status},${session.created_at},${session.last_activity || 'N/A'},${session.intent_count},${session.interaction_count},${session.daily_view_count},${session.daily_view_limit}\n`;
    });
    
    csv += '\nInteractions\n';
    csv += 'Session ID,Product ID,Action,Timestamp,Source,Product Name,Metadata\n';
    data.interactions.forEach(interaction => {
        csv += `${interaction.session_id},${interaction.product_id || 'N/A'},${interaction.action},${interaction.timestamp},${interaction.source || 'N/A'},${interaction.product_name || 'N/A'},${JSON.stringify(interaction.metadata || {})}\n`;
    });
    
    return csv;
}

function downloadCSV(csvContent, filename) {
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endsection
