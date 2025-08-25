<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Article;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'total_products' => Product::count(),
            'total_reviews' => Review::count(),
            'total_users' => User::where('role', 'user')->count(),
            'total_articles' => Article::count(),
            'pending_reviews' => Review::where('status', 'pending')->count(),
            'recent_reviews' => Review::with(['product', 'author'])
                ->latest()
                ->take(5)
                ->get(),
            'recent_users' => User::latest()
                ->take(5)
                ->get(),
        ];

        return view('admin.dashboard.index', $data);
    }
}