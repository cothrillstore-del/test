@extends('admin.layouts.app')

@section('title', 'Review Details')

@section('content')
<div class="container-fluid max-w-6xl">
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('admin.reviews.index') }}" class="mr-4">
                        <i class="fas fa-arrow-left text-gray-600"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800">Review Details</h1>
                </div>
                <div class="flex space-x-2">
                    @if($review->status === 'pending' || $review->status === 'flagged')
                        <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-check mr-2"></i>Approve
                            </button>
                        </form>
                        <button onclick="showRejectModal({{ $review->id }})"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            <i class="fas fa-times mr-2"></i>Reject
                        </button>
                    @endif
                    
                    <a href="{{ route('admin.reviews.edit', $review) }}" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Review Content -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h2 class="text-xl font-semibold mb-4">{{ $review->title }}</h2>
                        
                        <div class="flex items-center mb-4">
                            <div class="flex items-center mr-4">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }} text-lg"></i>
                                @endfor
                            </div>
                            <span class="text-lg font-medium">{{ $review->rating }}.0</span>
                            
                            @if($review->verified_purchase)
                                <span class="ml-4 px-2 py-1 bg-green-100 text-green-800 text-xs rounded">
                                    <i class="fas fa-check-circle mr-1"></i>Verified Purchase
                                </span>
                            @endif
                            
                            @if($review->is_featured)
                                <span class="ml-2 px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">
                                    <i class="fas fa-star mr-1"></i>Featured
                                </span>
                            @endif
                        </div>

                        <div class="prose max-w-none text-gray-700">
                            {!! nl2br(e($review->content)) !!}
                        </div>

                        @if($review->pros && count($review->pros) > 0)
                        <div class="mt-6">
                            <h4 class="font-semibold text-green-600 mb-2">
                                <i class="fas fa-plus-circle mr-1"></i>Pros
                            </h4>
                            <ul class="list-disc list-inside text-gray-700">
                                @foreach($review->pros as $pro)
                                    <li>{{ $pro }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        @if($review->cons && count($review->cons) > 0)
                        <div class="mt-4">
                            <h4 class="font-semibold text-red-600 mb-2">
                                <i class="fas fa-minus-circle mr-1"></i>Cons
                            </h4>
                            <ul class="list-disc list-inside text-gray-700">
                                @foreach($review->cons as $con)
                                    <li>{{ $con }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        @if($review->test_conditions)
                        <div class="mt-6 p-4 bg-white rounded">
                            <h4 class="font-semibold mb-2">Test Conditions</h4>
                            <dl class="grid grid-cols-2 gap-2 text-sm">
                                @foreach($review->test_conditions as $key => $value)
                                <dt class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}:</dt>
                                <dd class="font-medium">{{ $value }}</dd>
                                @endforeach
                            </dl>
                        </div>
                        @endif
                    </div>

                    <!-- Media -->
                    @if($review->media->count() > 0)
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Media ({{ $review->media->count() }})</h3>
                        <div class="grid grid-cols-3 gap-4">
                            @foreach($review->media as $media)
                            <div class="relative group">
                                <img src="{{ Storage::url($media->media_url) }}" 
                                     alt="Review media"
                                     class="w-full h-32 object-cover rounded-lg">
                                @if($media->caption)
                                <p class="text-xs text-gray-600 mt-1">{{ $media->caption }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Comments -->
                    @if($review->comments->count() > 0)
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Comments ({{ $review->comments->count() }})</h3>
                        <div class="space-y-4">
                            @foreach($review->comments as $comment)
                            <div class="bg-white p-4 rounded">
                                <div class="flex items-start">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}" 
                                         alt="{{ $comment->user->name }}"
                                         class="w-8 h-8 rounded-full mr-3">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-1">
                                            <span class="font-medium text-sm">{{ $comment->user->name }}</span>
                                            <span class="text-xs text-gray-500 ml-2">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-gray-700 text-sm">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Status Info -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Status Information</h3>
                        
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm text-gray-600">Current Status</dt>
                                <dd class="mt-1">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'flagged' => 'bg-purple-100 text-purple-800'
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full {{ $statusColors[$review->status] }}">
                                        {{ ucfirst($review->status) }}
                                    </span>
                                </dd>
                            </div>

                            @if($review->approved_by)
                            <div>
                                <dt class="text-sm text-gray-600">Moderated By</dt>
                                <dd class="mt-1 font-medium">{{ $review->approver->name }}</dd>
                            </div>
                            @endif

                            @if($review->approved_at)
                            <div>
                                <dt class="text-sm text-gray-600">Moderated At</dt>
                                <dd class="mt-1">{{ $review->approved_at->format('M d, Y H:i') }}</dd>
                            </div>
                            @endif

                            @if($review->rejection_reason)
                            <div>
                                <dt class="text-sm text-gray-600">Rejection Reason</dt>
                                <dd class="mt-1 text-red-600">{{ $review->rejection_reason }}</dd>
                            </div>
                            @endif

                            <div>
                                <dt class="text-sm text-gray-600">Submitted</dt>
                                <dd class="mt-1">{{ $review->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Product Info -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Product Information</h3>
                        
                        <div class="flex items-center mb-4">
                            @if($review->product->main_image)
                                <img src="{{ Storage::url($review->product->main_image) }}" 
                                     alt="{{ $review->product->name }}"
                                     class="w-16 h-16 object-cover rounded mr-3">
                            @endif
                            <div>
                                <h4 class="font-medium">{{ $review->product->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $review->product->brand->name }}</p>
                                <p class="text-sm text-gray-600">{{ $review->product->model_year }}</p>
                            </div>
                        </div>
                        
                        <a href="{{ route('admin.products.show', $review->product) }}" 
                           class="text-blue-600 hover:underline text-sm">
                            View Product Details →
                        </a>
                    </div>

                    <!-- Author Info -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Author Information</h3>
                        
                        <div class="flex items-center mb-4">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($review->user->name) }}" 
                                 alt="{{ $review->user->name }}"
                                 class="w-12 h-12 rounded-full mr-3">
                            <div>
                                <h4 class="font-medium">{{ $review->user->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $review->user->email }}</p>
                            </div>
                        </div>
                        
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Member Since</dt>
                                <dd>{{ $review->user->created_at->format('M Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Total Reviews</dt>
                                <dd>{{ $review->user->reviews()->count() }}</dd>
                            </div>
                            @if($review->skill_level)
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Skill Level</dt>
                                <dd class="capitalize">{{ $review->skill_level }}</dd>
                            </div>
                            @endif
                        </dl>
                        
                        <a href="{{ route('admin.users.show', $review->user) }}" 
                           class="text-blue-600 hover:underline text-sm mt-3 inline-block">
                            View User Profile →
                        </a>
                    </div>

                    <!-- Engagement Stats -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Engagement</h3>
                        
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Helpful Votes</dt>
                                <dd class="font-medium">{{ $review->helpful_count }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Not Helpful</dt>
                                <dd class="font-medium">{{ $review->unhelpful_count }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Helpfulness</dt>
                                <dd class="font-medium">{{ $review->getHelpfulnessPercentage() }}%</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Comments</dt>
                                <dd class="font-medium">{{ $review->comments->count() }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Review</h3>
            <form action="{{ route('admin.reviews.reject', $review) }}" method="POST">
                @csrf
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
function showRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>
@endpush