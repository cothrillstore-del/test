<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get date range filter
        $period = $request->get('period', '7days');
        $dateRange = $this->getDateRange($period);
        
        // Overview Statistics - Simplified
        $stats = [
            'products' => [
                'total' => Product::count(),
                'active' => Product::where('status', 'active')->count(),
                'growth' => 12.5, // Mock data
                'icon' => 'fa-golf-club',
                'color' => 'blue'
            ],
            'reviews' => [
                'total' => Review::count(),
                'pending' => Review::where('status', 'pending')->count(),
                'growth' => 8.3, // Mock data
                'icon' => 'fa-star',
                'color' => 'yellow'
            ],
            'users' => [
                'total' => User::where('role', 'user')->count(),
                'active' => User::count(), // Simplified
                'growth' => 5.2, // Mock data
                'icon' => 'fa-users',
                'color' => 'green'
            ],
            'revenue' => [
                'total' => 15000, // Mock data
                'monthly' => 5000, // Mock data
                'growth' => 15.7, // Mock data
                'icon' => 'fa-dollar-sign',
                'color' => 'purple'
            ]
        ];

        // Charts Data - Simplified
        $charts = [
            'reviewTrends' => $this->getSimpleReviewTrendsData(),
            'productViews' => $this->getSimpleProductViewsData(),
            'userActivity' => $this->getSimpleUserActivityData(),
            'categoryDistribution' => $this->getSimpleCategoryDistributionData(),
            'ratingDistribution' => $this->getSimpleRatingDistributionData(),
            'topBrands' => $this->getSimpleTopBrandsData()
        ];

        // Top Lists - Simplified
        $topLists = [
            'products' => Product::with(['brand', 'category'])
                ->orderBy('view_count', 'desc')
                ->take(5)
                ->get(),
            'reviewers' => User::select('users.*')
                ->selectRaw('(SELECT COUNT(*) FROM reviews WHERE user_id = users.id) as reviews_count')
                ->having('reviews_count', '>', 0)
                ->orderBy('reviews_count', 'desc')
                ->take(5)
                ->get(),
            'trending' => Product::with(['brand', 'category'])
                ->latest()
                ->take(5)
                ->get(),
            'recentReviews' => Review::with(['product', 'user'])
                ->latest()
                ->take(10)
                ->get()
        ];

        // Activity Feed - Simplified
        $activities = $this->getSimpleRecentActivities();

        // Performance Metrics - Simplified
        $metrics = [
            'avgRating' => round(Review::where('status', 'approved')->avg('rating') ?? 4.2, 1),
            'avgReviewLength' => 350, // Mock data
            'reviewApprovalRate' => 85.5, // Mock data
            'avgTimeToApproval' => 24, // Mock data
            'userEngagement' => 65.8, // Mock data
            'conversionRate' => 3.2 // Mock data
        ];

        return view('admin.dashboard.index', compact(
            'stats', 
            'charts', 
            'topLists', 
            'activities', 
            'metrics',
            'period'
        ));
    }

    private function getDateRange($period)
    {
        switch ($period) {
            case 'today':
                return [
                    'start' => now()->startOfDay(),
                    'end' => now()->endOfDay()
                ];
            case '7days':
                return [
                    'start' => now()->subDays(6)->startOfDay(),
                    'end' => now()->endOfDay()
                ];
            case '30days':
                return [
                    'start' => now()->subDays(29)->startOfDay(),
                    'end' => now()->endOfDay()
                ];
            default:
                return [
                    'start' => now()->subDays(6)->startOfDay(),
                    'end' => now()->endOfDay()
                ];
        }
    }

    private function getSimpleReviewTrendsData()
    {
        $days = [];
        $total = [];
        $approved = [];
        $pending = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $days[] = now()->subDays($i)->format('M d');
            $total[] = rand(10, 30);
            $approved[] = rand(5, 20);
            $pending[] = rand(2, 10);
        }

        return [
            'labels' => $days,
            'datasets' => [
                [
                    'label' => 'Total',
                    'data' => $total,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)'
                ],
                [
                    'label' => 'Approved',
                    'data' => $approved,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)'
                ],
                [
                    'label' => 'Pending',
                    'data' => $pending,
                    'borderColor' => 'rgb(251, 191, 36)',
                    'backgroundColor' => 'rgba(251, 191, 36, 0.1)'
                ]
            ]
        ];
    }

    private function getSimpleProductViewsData()
    {
        $products = Product::orderBy('view_count', 'desc')->take(10)->get();
        
        return [
            'labels' => $products->pluck('name')->map(function($name) {
                return Str::limit($name, 20);
            }),
            'datasets' => [
                [
                    'label' => 'Views',
                    'data' => $products->pluck('view_count'),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(20, 184, 166, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(163, 163, 163, 0.8)',
                        'rgba(99, 102, 241, 0.8)'
                    ]
                ]
            ]
        ];
    }

    private function getSimpleUserActivityData()
    {
        $days = [];
        $registrations = [];
        $reviews = [];
        $logins = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $days[] = now()->subDays($i)->format('M d');
            $registrations[] = rand(5, 15);
            $reviews[] = rand(10, 25);
            $logins[] = rand(20, 50);
        }

        return [
            'labels' => $days,
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $registrations,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)'
                ],
                [
                    'label' => 'Reviews',
                    'data' => $reviews,
                    'borderColor' => 'rgb(251, 191, 36)',
                    'backgroundColor' => 'rgba(251, 191, 36, 0.1)'
                ],
                [
                    'label' => 'Logins',
                    'data' => $logins,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)'
                ]
            ]
        ];
    }

    private function getSimpleCategoryDistributionData()
    {
        $categories = Category::withCount('products')
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->take(8)
            ->get();

        return [
            'labels' => $categories->pluck('name'),
            'datasets' => [
                [
                    'data' => $categories->pluck('products_count'),
                    'backgroundColor' => [
                        '#3B82F6',
                        '#22C55E', 
                        '#FBBf24',
                        '#EF4444',
                        '#A855F7',
                        '#EC4899',
                        '#14B8A6',
                        '#FB923C'
                    ]
                ]
            ]
        ];
    }

    private function getSimpleRatingDistributionData()
    {
        $ratings = [];
        for ($i = 5; $i >= 1; $i--) {
            $ratings[] = Review::where('status', 'approved')
                ->where('rating', $i)
                ->count();
        }

        return [
            'labels' => ['5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'],
            'datasets' => [
                [
                    'data' => $ratings,
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(132, 204, 22, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ]
                ]
            ]
        ];
    }

    private function getSimpleTopBrandsData()
    {
        $brands = Brand::withCount('products')
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->take(6)
            ->get();

        return [
            'labels' => $brands->pluck('name'),
            'datasets' => [
                [
                    'label' => 'Products Count',
                    'data' => $brands->pluck('products_count'),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(236, 72, 153, 0.8)'
                    ]
                ]
            ]
        ];
    }

    private function getSimpleRecentActivities()
    {
        $activities = collect();

        // Recent reviews
        $recentReviews = Review::with(['product', 'user'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($review) {
                return [
                    'type' => 'review',
                    'icon' => 'fa-star',
                    'color' => 'yellow',
                    'title' => ($review->user->name ?? 'User') . ' reviewed ' . ($review->product->name ?? 'Product'),
                    'description' => Str::limit($review->content, 100),
                    'time' => $review->created_at,
                    'url' => '#'
                ];
            });

        // New users
        $newUsers = User::latest()
            ->take(5)
            ->get()
            ->map(function($user) {
                return [
                    'type' => 'user',
                    'icon' => 'fa-user',
                    'color' => 'green',
                    'title' => 'New user registered',
                    'description' => $user->name . ' (' . $user->email . ')',
                    'time' => $user->created_at,
                    'url' => '#'
                ];
            });

        return $activities
            ->merge($recentReviews)
            ->merge($newUsers)
            ->sortByDesc('time')
            ->take(10);
    }

    public function getAnalyticsData(Request $request)
    {
        // Simplified response
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    public function exportReport(Request $request)
    {
        return response()->json([
            'message' => 'Export functionality will be implemented soon'
        ]);
    }
}