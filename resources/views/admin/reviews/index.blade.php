@extends('admin.layouts.app')

@section('title', 'Reviews Management')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Reviews</p>
                    <p class="text-2xl font-bold">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="bg-blue-100 p-2 rounded-full">
                    <i class="fas fa-star text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['pending']) }}</p>
                </div>
                <div class="bg-yellow-100 p-2 rounded-full">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Approved</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($stats['approved']) }}</p>
                </div>
                <div class="bg-green-100 p-2 rounded-full">
                    <i class="fas fa-check text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Rejected</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($stats['rejected']) }}</p>
                </div>
                <div class="bg-red-100 p-2 rounded-full">
                    <i class="fas fa-times text-red-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Flagged</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['flagged']) }}</p>
                </div>
                <div class="bg-purple-100 p-2 rounded-full">
                    <i class="fas fa-flag text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Reviews Management</h1>
                <p class="text-gray-600 mt-1">Moderate and manage product reviews</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.reviews.statistics') }}" 
                   class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg">
                    <i class="fas fa-chart-bar mr-2"></i>Statistics
                </a>
                <button onclick="exportReviews()" 
                        class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg">
                    <i class="fas fa-download mr-2"></i>Export
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search reviews..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                    <select name="product_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                    <select name="rating" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">All Ratings</option>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                                {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" 
                           name="date_from" 
                           value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" 
                           name="date_to" 
                           value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="verified_purchase" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">All Types</option>
                        <option value="1" {{ request('verified_purchase') == '1' ? 'selected' : '' }}>Verified Purchase</option>
                        <option value="0" {{ request('verified_purchase') == '0' ? 'selected' : '' }}>Not Verified</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg mr-2">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <a href="{{ route('admin.reviews.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Reviews Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-6 border-b">
            <div class="flex justify-between items-center">
                <p class="text-gray-600">Showing {{ $reviews->firstItem() ?? 0 }} to {{ $reviews->lastItem() ?? 0 }} of {{ $reviews->total() }} reviews</p>
                <div class="space-x-2">
                    <button id="bulkApproveBtn" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg hidden">
                        <i class="fas fa-check mr-2"></i>Approve Selected
                    </button>
                    <button id="bulkRejectBtn" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg hidden">
                        <i class="fas fa-times mr-2"></i>Reject Selected
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Review
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Author
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Rating
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reviews as $review)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <input type="checkbox" class="review-checkbox rounded border-gray-300" value="{{ $review->id }}">
                        </td>
                        <td class="px-6 py-4">
                            <div class="max-w-xs">
                                <div class="text-sm font-medium text-gray-900">{{ Str::limit($review->title, 40) }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($review->content, 60) }}</div>
                                <div class="flex items-center mt-1 space-x-2">
                                    @if($review->verified_purchase)
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded">Verified</span>
                                    @endif
                                    @if($review->is_featured)
                                        <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded">Featured</span>
                                    @endif
                                    @if($review->media->count() > 0)
                                        <span class="text-xs text-gray-500">
                                            <i class="fas fa-image"></i> {{ $review->media->count() }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <div class="font-medium text-gray-900">{{ $review->product->name }}</div>
                                <div class="text-gray-500">{{ $review->product->brand->name }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($review->user->name) }}" 
                                     alt="{{ $review->user->name }}"
                                     class="w-8 h-8 rounded-full mr-2">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $review->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $review->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }} text-sm"></i>
                                @endfor
                                <span class="ml-2 text-sm text-gray-600">{{ $review->rating }}.0</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'flagged' => 'bg-purple-100 text-purple-800'
                                ];
                            @endphp
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$review->status] }}">
                                {{ ucfirst($review->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $review->created_at->format('M d, Y') }}
                            <br>
                            <span class="text-xs">{{ $review->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('admin.reviews.show', $review) }}" 
                                   class="text-blue-600 hover:text-blue-900"
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($review->status === 'pending' || $review->status === 'flagged')
                                    <button onclick="quickApprove({{ $review->id }})"
                                            class="text-green-600 hover:text-green-900"
                                            title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button onclick="showRejectModal({{ $review->id }})"
                                            class="text-red-600 hover:text-red-900"
                                            title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif

                                <button onclick="toggleFeatured({{ $review->id }})"
                                        class="toggle-featured"
                                        data-id="{{ $review->id }}">
                                    <i class="fas fa-star {{ $review->is_featured ? 'text-yellow-500' : 'text-gray-300' }}"></i>
                                </button>

                                <a href="{{ route('admin.reviews.edit', $review) }}" 
                                   class="text-green-600 hover:text-green-900"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('admin.reviews.destroy', $review) }}" 
                                      method="POST" 
                                      class="inline-block"
                                      onsubmit="return confirm('Are you sure you want to delete this review?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            No reviews found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t">
            {{ $reviews->links() }}
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Review</h3>
            <form id="rejectForm" method="POST">
                @csrf
                <input type="hidden" id="rejectReviewId" name="review_id">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection</label>
                    <textarea name="reason" 
                              rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                              required></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" 
                            onclick="closeRejectModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Reject Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select all checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.review-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    toggleBulkActions();
});

// Individual checkbox
document.querySelectorAll('.review-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', toggleBulkActions);
});

function toggleBulkActions() {
    const checkedBoxes = document.querySelectorAll('.review-checkbox:checked');
    const bulkApproveBtn = document.getElementById('bulkApproveBtn');
    const bulkRejectBtn = document.getElementById('bulkRejectBtn');
    
    if (checkedBoxes.length > 0) {
        bulkApproveBtn.classList.remove('hidden');
        bulkRejectBtn.classList.remove('hidden');
    } else {
        bulkApproveBtn.classList.add('hidden');
        bulkRejectBtn.classList.add('hidden');
    }
}

// Quick approve
function quickApprove(reviewId) {
    if (confirm('Are you sure you want to approve this review?')) {
        fetch(`/admin/reviews/${reviewId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}

// Show reject modal
function showRejectModal(reviewId) {
    document.getElementById('rejectReviewId').value = reviewId;
    document.getElementById('rejectForm').action = `/admin/reviews/${reviewId}/reject`;
    document.getElementById('rejectModal').classList.remove('hidden');
}

// Close reject modal
function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectForm').reset();
}

// Toggle featured
function toggleFeatured(reviewId) {
    fetch(`/admin/reviews/${reviewId}/toggle-featured`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const star = document.querySelector(`[data-id="${reviewId}"] i`);
            if (data.is_featured) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-500');
            } else {
                star.classList.remove('text-yellow-500');
                star.classList.add('text-gray-300');
            }
        }
    });
}

// Bulk approve
document.getElementById('bulkApproveBtn').addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.review-checkbox:checked');
    const reviewIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (reviewIds.length === 0) return;
    
    if (confirm(`Are you sure you want to approve ${reviewIds.length} review(s)?`)) {
        fetch('{{ route("admin.reviews.bulk-approve") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ review_ids: reviewIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
});

// Bulk reject
document.getElementById('bulkRejectBtn').addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.review-checkbox:checked');
    const reviewIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (reviewIds.length === 0) return;
    
    const reason = prompt('Enter rejection reason:');
    if (!reason) return;
    
    fetch('{{ route("admin.reviews.bulk-reject") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ 
            review_ids: reviewIds,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
});

// Export reviews
function exportReviews() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = '{{ route("admin.reviews.export") }}?' + params.toString();
}
</script>
@endpush