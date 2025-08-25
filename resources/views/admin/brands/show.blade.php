@extends('admin.layouts.app')

@section('title', 'Brand Details')

@section('content')
<div class="container-fluid max-w-6xl">
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('admin.brands.index') }}" class="mr-4">
                        <i class="fas fa-arrow-left text-gray-600"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800">Brand: {{ $brand->name }}</h1>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.brands.edit', $brand) }}" 
                       class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    @if($brand->products()->count() == 0)
                    <form action="{{ route('admin.brands.destroy', $brand) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Brand Information</h3>
                        <dl class="space-y-2">
                            <div class="flex">
                                <dt class="w-32 text-gray-600">Name:</dt>
                                <dd class="font-medium">{{ $brand->name }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 text-gray-600">Slug:</dt>
                                <dd>{{ $brand->slug }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 text-gray-600">Country:</dt>
                                <dd>{{ $brand->country ?? '-' }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 text-gray-600">Website:</dt>
                                <dd>
                                    @if($brand->website)
                                        <a href="{{ $brand->website }}" target="_blank" class="text-blue-600 hover:underline">
                                            {{ $brand->website }} <i class="fas fa-external-link-alt text-xs"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 text-gray-600">Featured:</dt>
                                <dd>
                                    @if($brand->is_featured)
                                        <span class="text-yellow-500"><i class="fas fa-star"></i> Yes</span>
                                    @else
                                        <span class="text-gray-400">No</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 text-gray-600">Sort Order:</dt>
                                <dd>{{ $brand->sort_order }}</dd>
                            </div>
                        </dl>
                    </div>

                    @if($brand->description)
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Description</h3>
                        <p class="text-gray-700">{{ $brand->description }}</p>
                    </div>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    @if($brand->logo_url)
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Logo</h3>
                        <img src="{{ Storage::url($brand->logo_url) }}" 
                             alt="{{ $brand->name }}"
                             class="w-32 h-32 object-contain border rounded bg-gray-50 p-4">
                    </div>
                    @endif

                    <div>
                        <h3 class="text-lg font-semibold mb-3">Statistics</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Total Products:</dt>
                                    <dd class="font-semibold">{{ $brand->products()->count() }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Active Products:</dt>
                                    <dd class="font-semibold">{{ $brand->products()->where('status', 'active')->count() }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Created:</dt>
                                    <dd>{{ $brand->created_at->format('M d, Y') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Last Updated:</dt>
                                    <dd>{{ $brand->updated_at->format('M d, Y') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            @if($brand->products->count() > 0)
            <div class="mt-8 pt-8 border-t">
                <h3 class="text-lg font-semibold mb-4">Recent Products ({{ $brand->products()->count() }} total)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($brand->products()->latest()->take(6)->get() as $product)
                    <div class="border rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex items-start space-x-3">
                            @if($product->main_image)
                                <img src="{{ Storage::url($product->main_image) }}" 
                                     alt="{{ $product->name }}"
                                     class="w-16 h-16 object-cover rounded">
                            @endif
                            <div class="flex-1">
                                <h4 class="font-medium">{{ $product->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $product->model_year }}</p>
                                <p class="text-sm">{{ $product->price_range }}</p>
                                <a href="{{ route('admin.products.edit', $product) }}" 
                                   class="text-xs text-blue-600 hover:underline">
                                    Edit Product →
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($brand->products()->count() > 6)
                    <div class="mt-4 text-center">
                        <a href="{{ route('admin.products.index', ['brand_id' => $brand->id]) }}" 
                           class="text-blue-600 hover:underline">
                            View all {{ $brand->products()->count() }} products →
                        </a>
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection