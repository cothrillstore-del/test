<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Trending Products</h3>
        <span class="text-xs text-gray-500">Last 7 days</span>
    </div>
    
    <div class="space-y-3">
        @foreach($trendingProducts as $product)
        <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded">
            <div class="flex items-center space-x-3">
                @if($product->main_image)
                <img src="{{ Storage::url($product->main_image) }}" 
                     alt="{{ $product->name }}"
                     class="w-10 h-10 rounded object-cover">
                @endif
                <div>
                    <p class="font-medium text-sm">{{ $product->name }}</p>
                    <p class="text-xs text-gray-500">{{ $product->brand->name }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm font-semibold">{{ $product->recent_views }} views</p>
                <p class="text-xs text-green-600">
                    <i class="fas fa-arrow-up"></i> {{ $product->growth }}%
                </p>
            </div>
        </div>
        @endforeach
    </div>
</div>