<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Product;

class CheckProductOwnership
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $productId = $request->route('product');
        
        if ($productId) {
            $product = Product::find($productId);
            
            if (!$product) {
                abort(404, 'Product not found');
            }

            // Check if user owns this product or is admin
            if (auth()->user()->role !== 'admin' && $product->user_id !== auth()->id()) {
                abort(403, 'You do not have permission to modify this product.');
            }
        }

        return $next($request);
    }
}