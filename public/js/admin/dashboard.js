// Dashboard JavaScript Helpers

class DashboardManager {
    constructor() {
        this.charts = {};
        this.refreshInterval = 300000; // 5 minutes
        this.init();
    }

    init() {
        this.initCharts();
        this.initEventListeners();
        this.startAutoRefresh();
    }

    initCharts() {
        // Initialize all charts
        this.initReviewTrendsChart();
        this.initUserActivityChart();
        this.initCategoryChart();
        this.initRatingChart();
    }

    initEventListeners() {
        // Period selector
        document.getElementById('periodSelector')?.addEventListener('change', (e) => {
            this.changePeriod(e.target.value);
        });

        // Export button
        document.getElementById('exportBtn')?.addEventListener('click', () => {
            this.exportReport();
        });

        // Refresh buttons
        document.querySelectorAll('.refresh-chart').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const chartId = e.target.dataset.chart;
                this.refreshChart(chartId);
            });
        });
    }

    changePeriod(period) {
        window.location.href = `/admin/dashboard?period=${period}`;
    }

    refreshChart(chartId) {
        const period = document.getElementById('periodSelector').value;
        
        fetch(`/admin/dashboard/analytics?type=${chartId}&period=${period}`)
            .then(response => response.json())
            .then(data => {
                if (this.charts[chartId]) {
                    this.charts[chartId].data = data;
                    this.charts[chartId].update();
                }
            })
            .catch(error => console.error('Error refreshing chart:', error));
    }

    exportReport() {
        const period = document.getElementById('periodSelector').value;
        const format = document.getElementById('exportFormat')?.value || 'pdf';
        
        window.location.href = `/admin/dashboard/export?period=${period}&format=${format}`;
    }

    startAutoRefresh() {
        setInterval(() => {
            this.refreshDashboard();
        }, this.refreshInterval);
    }

    refreshDashboard() {
        // Refresh key metrics without full page reload
        fetch('/admin/dashboard/metrics')
            .then(response => response.json())
            .then(data => {
                this.updateMetrics(data);
            })
            .catch(error => console.error('Error refreshing dashboard:', error));
    }

    updateMetrics(data) {
        // Update metric values in the DOM
        Object.keys(data).forEach(key => {
            const element = document.getElementById(`metric-${key}`);
            if (element) {
                element.textContent = data[key];
            }
        });
    }

    initReviewTrendsChart() {
        const ctx = document.getElementById('reviewTrendsChart');
        if (!ctx) return;

        this.charts.reviewTrends = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: window.reviewTrendsData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y;
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Count'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Additional chart initialization methods...
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardManager = new DashboardManager();
});