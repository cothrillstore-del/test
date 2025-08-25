@extends('admin.layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    .activity-item {
        transition: background-color 0.2s;
    }
    .activity-item:hover {
        background-color: #f9fafb;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header with Date Filter -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex flex-wrap justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard Analytics</h1>
                <p class="text-gray-600 mt-1">Welcome back, {{ auth()->user()->name }}!</p>
            </div>
            <div class="flex items-center space-x-3 mt-4 md:mt-0">
                <!-- Period Selector -->
                <select id="periodSelector" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="7days" {{ $period == '7days' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30days" {{ $period == '30days' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90days" {{ $period == '90days' ? 'selected' : '' }}>Last 90 Days</option>
                    <option value="year" {{ $period == 'year' ? 'selected' : '' }}>This Year</option>
                </select>
                
                <!-- Export Button -->
                <button onclick="exportReport()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">
                    <i class="fas fa-download mr-2"></i>Export Report
                </button>
                
                <!-- Refresh Button -->
                <button onclick="location.reload()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Products Card -->
        <div class="stat-card bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Products</p>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['products']['total']) }}</p>
                    <p class="text-sm mt-2">
                        <span class="{{ $stats['products']['growth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            <i class="fas {{ $stats['products']['growth'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                            {{ abs($stats['products']['growth']) }}%
                        </span>
                        <span class="text-gray-500 ml-1">vs previous period</span>
                    </p>
                </div>
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="fas {{ $stats['products']['icon'] }} text-blue-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Active</span>
                    <span class="font-semibold">{{ number_format($stats['products']['active']) }}</span>
                </div>
            </div>
        </div>

        <!-- Reviews Card -->
        <div class="stat-card bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Reviews</p>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['reviews']['total']) }}</p>
                    <p class="text-sm mt-2">
                        <span class="{{ $stats['reviews']['growth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            <i class="fas {{ $stats['reviews']['growth'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                            {{ abs($stats['reviews']['growth']) }}%
                        </span>
                        <span class="text-gray-500 ml-1">vs previous period</span>
                    </p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-full">
                    <i class="fas {{ $stats['reviews']['icon'] }} text-yellow-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Pending</span>
                    <span class="font-semibold text-yellow-600">{{ number_format($stats['reviews']['pending']) }}</span>
                </div>
            </div>
        </div>

        <!-- Users Card -->
        <div class="stat-card bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Users</p>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['users']['total']) }}</p>
                    <p class="text-sm mt-2">
                        <span class="{{ $stats['users']['growth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            <i class="fas {{ $stats['users']['growth'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                            {{ abs($stats['users']['growth']) }}%
                        </span>
                        <span class="text-gray-500 ml-1">vs previous period</span>
                    </p>
                </div>
                <div class="bg-green-100 p-4 rounded-full">
                    <i class="fas {{ $stats['users']['icon'] }} text-green-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Active (30d)</span>
                    <span class="font-semibold">{{ number_format($stats['users']['active']) }}</span>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="stat-card bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Affiliate Revenue</p>
                    <p class="text-3xl font-bold text-gray-800">${{ number_format($stats['revenue']['total']) }}</p>
                    <p class="text-sm mt-2">
                        <span class="{{ $stats['revenue']['growth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            <i class="fas {{ $stats['revenue']['growth'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                            {{ abs($stats['revenue']['growth']) }}%
                        </span>
                        <span class="text-gray-500 ml-1">vs previous period</span>
                    </p>
                </div>
                <div class="bg-purple-100 p-4 rounded-full">
                    <i class="fas {{ $stats['revenue']['icon'] }} text-purple-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">This Month</span>
                    <span class="font-semibold">${{ number_format($stats['revenue']['monthly']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <p class="text-gray-500 text-xs uppercase tracking-wider">Avg Rating</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($metrics['avgRating'], 1) }}</p>
            <div class="flex justify-center mt-1">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star {{ $i <= round($metrics['avgRating']) ? 'text-yellow-400' : 'text-gray-300' }} text-xs"></i>
                @endfor
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <p class="text-gray-500 text-xs uppercase tracking-wider">Approval Rate</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $metrics['reviewApprovalRate'] }}%</p>
            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                <div class="bg-green-600 h-1.5 rounded-full" style="width: {{ $metrics['reviewApprovalRate'] }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <p class="text-gray-500 text-xs uppercase tracking-wider">Avg Approval Time</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $metrics['avgTimeToApproval'] }}h</p>
            <p class="text-xs text-gray-500 mt-1">hours</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <p class="text-gray-500 text-xs uppercase tracking-wider">User Engagement</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $metrics['userEngagement'] }}%</p>
            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $metrics['userEngagement'] }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <p class="text-gray-500 text-xs uppercase tracking-wider">Conversion Rate</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $metrics['conversionRate'] }}%</p>
            <p class="text-xs text-green-600 mt-1">
                <i class="fas fa-arrow-up"></i> +0.5%
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <p class="text-gray-500 text-xs uppercase tracking-wider">Avg Review Length</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($metrics['avgReviewLength']) }}</p>
            <p class="text-xs text-gray-500 mt-1">characters</p>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Review Trends Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Review Trends</h3>
                <button onclick="updateChart('reviewTrends')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="chart-container">
                <canvas id="reviewTrendsChart"></canvas>
            </div>
        </div>

        <!-- User Activity Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">User Activity</h3>
                <button onclick="updateChart('userActivity')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="chart-container">
                <canvas id="userActivityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Category Distribution -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Category Distribution</h3>
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Rating Distribution -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Rating Distribution</h3>
            <div class="chart-container">
                <canvas id="ratingChart"></canvas>
            </div>
        </div>

        <!-- Top Brands -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Brands</h3>
            <div class="chart-container">
                <canvas id="brandsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Lists and Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Products -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Products</h3>
            <div class="space-y-3">
                @foreach($topLists['products'] as $index => $product)
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <span class="text-sm font-semibold text-gray-600">{{ $index + 1 }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</p>
                        <p class="text-xs text-gray-500">{{ $product->brand->name }} • {{ number_format($product->view_count) }} views</p>
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm font-semibold">{{ number_format($product->reviews_avg_rating, 1) }}</span>
                        <i class="fas fa-star text-yellow-400 text-xs ml-1"></i>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Top Reviewers -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Reviewers</h3>
            <div class="space-y-3">
                @foreach($topLists['reviewers'] as $index => $reviewer)
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($reviewer->name) }}" 
                             alt="{{ $reviewer->name }}"
                             class="w-8 h-8 rounded-full">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $reviewer->name }}</p>
                        <p class="text-xs text-gray-500">{{ $reviewer->reviews_count }} reviews</p>
                    </div>
                    <div class="flex items-center">
                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                            {{ number_format($reviewer->reviews_avg_helpful_count) }} helpful
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h3>
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @foreach($activities as $activity)
                <div class="activity-item flex items-start space-x-3 p-2 rounded">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-{{ $activity['color'] }}-100 rounded-full flex items-center justify-center">
                            <i class="fas {{ $activity['icon'] }} text-{{ $activity['color'] }}-600 text-xs"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $activity['description'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $activity['time']->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart configurations
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
        }
    }
};

// Initialize Review Trends Chart
const reviewTrendsCtx = document.getElementById('reviewTrendsChart').getContext('2d');
const reviewTrendsChart = new Chart(reviewTrendsCtx, {
    type: 'line',
    data: @json($charts['reviewTrends']),
    options: {
        ...chartOptions,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Initialize User Activity Chart
const userActivityCtx = document.getElementById('userActivityChart').getContext('2d');
const userActivityChart = new Chart(userActivityCtx, {
    type: 'line',
    data: @json($charts['userActivity']),
    options: {
        ...chartOptions,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Initialize Category Distribution Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryChart = new Chart(categoryCtx, {
    type: 'doughnut',
    data: @json($charts['categoryDistribution']),
    options: chartOptions
});

// Initialize Rating Distribution Chart
const ratingCtx = document.getElementById('ratingChart').getContext('2d');
const ratingChart = new Chart(ratingCtx, {
    type: 'bar',
    data: @json($charts['ratingDistribution']),
    options: {
        ...chartOptions,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Initialize Brands Chart
const brandsCtx = document.getElementById('brandsChart').getContext('2d');
const brandsChart = new Chart(brandsCtx, {
    type: 'bar',
    data: @json($charts['topBrands']),
    options: {
        ...chartOptions,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Period selector change handler
document.getElementById('periodSelector').addEventListener('change', function() {
    window.location.href = '{{ route("admin.dashboard") }}?period=' + this.value;
});

// Update chart function
function updateChart(chartType) {
    fetch('{{ route("admin.dashboard.analytics") }}?type=' + chartType + '&period=' + document.getElementById('periodSelector').value)
        .then(response => response.json())
        .then(data => {
            switch(chartType) {
                case 'reviewTrends':
                    reviewTrendsChart.data = data;
                    reviewTrendsChart.update();
                    break;
                case 'userActivity':
                    userActivityChart.data = data;
                    userActivityChart.update();
                    break;
            }
        });
}

// Export report function
function exportReport() {
    const period = document.getElementById('periodSelector').value;
    window.location.href = '{{ route("admin.dashboard.export") }}?period=' + period + '&format=pdf';
}

// Auto-refresh every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);
</script>
@endpush