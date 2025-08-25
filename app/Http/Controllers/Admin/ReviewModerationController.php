<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use App\Models\ReviewMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReviewModerationController extends Controller
{
    /**
     * Display listing of reviews
     */
    public function index(Request $request)
    {
        $query = Review::with(['product', 'user', 'media']);

        // Apply filters
        $query->withFilters($request->all());

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $reviews = $query->paginate(20)->withQueryString();

        // Get filter options
        $products = Product::orderBy('name')->get();
        $statuses = ['pending', 'approved', 'rejected', 'flagged'];

        // Get statistics
        $stats = [
            'total' => Review::count(),
            'pending' => Review::pending()->count(),
            'approved' => Review::approved()->count(),
            'rejected' => Review::rejected()->count(),
            'flagged' => Review::flagged()->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'products', 'statuses', 'stats'));
    }

    /**
     * Display review details
     */
    public function show(Review $review)
    {
        $review->load(['product', 'user', 'media', 'comments.user', 'approver']);
        
        return view('admin.reviews.show', compact('review'));
    }

    /**
     * Show form for editing review
     */
    public function edit(Review $review)
    {
        $review->load(['product', 'user', 'media']);
        $products = Product::orderBy('name')->get();
        
        return view('admin.reviews.edit', compact('review', 'products'));
    }

    /**
     * Update review
     */
    public function update(Request $request, Review $review)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:50',
            'rating' => 'required|integer|min:1|max:5',
            'pros' => 'nullable|array',
            'pros.*' => 'string|max:255',
            'cons' => 'nullable|array',
            'cons.*' => 'string|max:255',
            'verified_purchase' => 'boolean',
            'is_featured' => 'boolean',
            'skill_level' => 'nullable|string|in:beginner,intermediate,advanced,pro',
            'remove_media' => 'nullable|array',
            'remove_media.*' => 'exists:review_media,id'
        ]);

        DB::beginTransaction();
        try {
            // Update review
            $review->update($request->except(['remove_media', 'new_media']));

            // Remove selected media
            if ($request->has('remove_media')) {
                $mediaToRemove = ReviewMedia::whereIn('id', $request->remove_media)
                    ->where('review_id', $review->id)
                    ->get();

                foreach ($mediaToRemove as $media) {
                    Storage::disk('public')->delete($media->media_url);
                    if ($media->thumbnail_url) {
                        Storage::disk('public')->delete($media->thumbnail_url);
                    }
                    $media->delete();
                }
            }

            // Handle new media uploads
            if ($request->hasFile('new_media')) {
                foreach ($request->file('new_media') as $index => $file) {
                    $path = $file->store('reviews/' . $review->id, 'public');
                    
                    ReviewMedia::create([
                        'review_id' => $review->id,
                        'media_type' => 'image',
                        'media_url' => $path,
                        'sort_order' => $review->media()->max('sort_order') + $index + 1
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.reviews.show', $review)
                ->with('success', 'Review updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating review: ' . $e->getMessage());
        }
    }

    /**
     * Approve review
     */
    public function approve(Request $request, Review $review)
    {
        if ($review->status === 'approved') {
            return redirect()->back()
                ->with('info', 'Review is already approved.');
        }

        $review->approve(auth()->id());

        // Send notification to review author
        // $review->user->notify(new ReviewApprovedNotification($review));

        return redirect()->back()
            ->with('success', 'Review approved successfully!');
    }

    /**
     * Reject review
     */
    public function reject(Request $request, Review $review)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        if ($review->status === 'rejected') {
            return redirect()->back()
                ->with('info', 'Review is already rejected.');
        }

        $review->reject($request->reason, auth()->id());

        // Send notification to review author
        // $review->user->notify(new ReviewRejectedNotification($review, $request->reason));

        return redirect()->back()
            ->with('success', 'Review rejected successfully!');
    }

    /**
     * Flag review for additional review
     */
    public function flag(Request $request, Review $review)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $review->flag($request->reason);

        return redirect()->back()
            ->with('success', 'Review flagged for additional review!');
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Review $review)
    {
        $review->update(['is_featured' => !$review->is_featured]);
        
        return response()->json([
            'success' => true,
            'is_featured' => $review->is_featured
        ]);
    }

    /**
     * Delete review
     */
    public function destroy(Review $review)
    {
        try {
            // Delete media files
            foreach ($review->media as $media) {
                Storage::disk('public')->delete($media->media_url);
                if ($media->thumbnail_url) {
                    Storage::disk('public')->delete($media->thumbnail_url);
                }
            }

            $review->delete();

            // Update product rating
            $review->product->updateRating();

            return redirect()->route('admin.reviews.index')
                ->with('success', 'Review deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting review: ' . $e->getMessage());
        }
    }

    /**
     * Bulk approve reviews
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'review_ids' => 'required|array',
            'review_ids.*' => 'exists:reviews,id'
        ]);

        $reviews = Review::whereIn('id', $request->review_ids)
            ->where('status', 'pending')
            ->get();

        foreach ($reviews as $review) {
            $review->approve(auth()->id());
        }

        return response()->json([
            'success' => true,
            'message' => count($reviews) . ' reviews approved successfully!'
        ]);
    }

    /**
     * Bulk reject reviews
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'review_ids' => 'required|array',
            'review_ids.*' => 'exists:reviews,id',
            'reason' => 'required|string|max:500'
        ]);

        $reviews = Review::whereIn('id', $request->review_ids)
            ->whereIn('status', ['pending', 'flagged'])
            ->get();

        foreach ($reviews as $review) {
            $review->reject($request->reason, auth()->id());
        }

        return response()->json([
            'success' => true,
            'message' => count($reviews) . ' reviews rejected successfully!'
        ]);
    }

    /**
     * Get review statistics
     */
    public function statistics()
    {
        $stats = [
            'by_status' => Review::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            
            'by_rating' => Review::selectRaw('rating, count(*) as count')
                ->where('status', 'approved')
                ->groupBy('rating')
                ->orderBy('rating', 'desc')
                ->pluck('count', 'rating'),
            
            'recent_week' => Review::where('created_at', '>=', now()->subWeek())
                ->count(),
            
            'pending_time' => Review::pending()
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, NOW())) as avg_hours')
                ->value('avg_hours'),
            
            'top_reviewers' => User::withCount(['reviews' => function ($query) {
                    $query->where('status', 'approved');
                }])
                ->orderBy('reviews_count', 'desc')
                ->take(5)
                ->get(),
            
            'top_products' => Product::withCount(['reviews' => function ($query) {
                    $query->where('status', 'approved');
                }])
                ->orderBy('reviews_count', 'desc')
                ->take(5)
                ->get()
        ];

        return view('admin.reviews.statistics', compact('stats'));
    }

    /**
     * Export reviews
     */
    public function export(Request $request)
    {
        $query = Review::with(['product', 'user']);
        $query->withFilters($request->all());

        $reviews = $query->get();

        // Generate CSV or Excel export
        // Implementation depends on your export package

        return response()->json(['message' => 'Export functionality to be implemented']);
    }
}