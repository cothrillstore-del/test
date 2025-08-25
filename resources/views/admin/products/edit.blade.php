@extends('admin.layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="container-fluid">
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b">
            <div class="flex items-center">
                <a href="{{ route('admin.products.index') }}" class="mr-4">
                    <i class="fas fa-arrow-left text-gray-600"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Edit Product: {{ $product->name }}</h1>
            </div>
        </div>

        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Product Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       value="{{ old('name', $product->name) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('name') border-red-500 @enderror"
                                       required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Brand <span class="text-red-500">*</span>
                                    </label>
                                    <select name="brand_id" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                            required>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Category <span class="text-red-500">*</span>
                                    </label>
                                    <select name="category_id" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                            required>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                            @foreach($category->children as $child)
                                                <option value="{{ $child->id }}" {{ old('category_id', $product->category_id) == $child->id ? 'selected' : '' }}>
                                                    &nbsp;&nbsp;-- {{ $child->name }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Model Year</label>
                                    <input type="number" 
                                           name="model_year" 
                                           value="{{ old('model_year', $product->model_year) }}"
                                           min="2000" 
                                           max="{{ date('Y') + 2 }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                                    <input type="text" 
                                           name="sku" 
                                           value="{{ old('sku', $product->sku) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                                <textarea name="short_description" 
                                          rows="2"
                                          maxlength="500"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">{{ old('short_description', $product->short_description) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Description</label>
                                <textarea name="description" 
                                          rows="6"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">{{ old('description', $product->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Pricing</h3>
                        
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Min Price ($)</label>
                                <input type="number" 
                                       name="price_min" 
                                       value="{{ old('price_min', $product->price_min) }}"
                                       min="0" 
                                       step="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Max Price ($)</label>
                                <input type="number" 
                                       name="price_max" 
                                       value="{{ old('price_max', $product->price_max) }}"
                                       min="0" 
                                       step="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Retail Price ($)</label>
                                <input type="number" 
                                       name="retail_price" 
                                       value="{{ old('retail_price', $product->retail_price) }}"
                                       min="0" 
                                       step="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>
                        </div>
                    </div>

                    <!-- Variants -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Product Variants</h3>
                        
                        <div id="variants-container" class="space-y-3">
                            @foreach($product->variants as $index => $variant)
                            <div class="flex gap-2 variant-row">
                                <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                                <select name="variants[{{ $index }}][variant_type]" class="px-3 py-2 border border-gray-300 rounded-lg">
                                    <option value="flex" {{ $variant->variant_type == 'flex' ? 'selected' : '' }}>Flex</option>
                                    <option value="loft" {{ $variant->variant_type == 'loft' ? 'selected' : '' }}>Loft</option>
                                    <option value="length" {{ $variant->variant_type == 'length' ? 'selected' : '' }}>Length</option>
                                    <option value="color" {{ $variant->variant_type == 'color' ? 'selected' : '' }}>Color</option>
                                    <option value="hand" {{ $variant->variant_type == 'hand' ? 'selected' : '' }}>Hand</option>
                                    <option value="size" {{ $variant->variant_type == 'size' ? 'selected' : '' }}>Size</option>
                                </select>
                                <input type="text" 
                                       name="variants[{{ $index }}][variant_name]"
                                       value="{{ $variant->variant_name }}"
                                       placeholder="Variant name"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
                                <input type="text" 
                                       name="variants[{{ $index }}][variant_value]"
                                       value="{{ $variant->variant_value }}"
                                       placeholder="Value"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
                                <input type="number" 
                                       name="variants[{{ $index }}][price]"
                                       value="{{ $variant->price }}"
                                       placeholder="Price"
                                       step="0.01"
                                       class="w-24 px-3 py-2 border border-gray-300 rounded-lg">
                                <button type="button" onclick="removeVariant(this)" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        
                        <button type="button" onclick="addVariant()" class="mt-3 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <i class="fas fa-plus mr-2"></i>Add Variant
                        </button>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Status & Settings -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Status & Settings</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select name="status" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                        required>
                                    <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="coming_soon" {{ old('status', $product->status) == 'coming_soon' ? 'selected' : '' }}>Coming Soon</option>
                                    <option value="discontinued" {{ old('status', $product->status) == 'discontinued' ? 'selected' : '' }}>Discontinued</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Release Date</label>
                                <input type="date" 
                                       name="release_date" 
                                       value="{{ old('release_date', $product->release_date?->format('Y-m-d')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="is_featured" 
                                           value="1"
                                           {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                                           class="mr-2 rounded border-gray-300 text-green-600 focus:ring-green-500">
                                    <span class="text-sm font-medium text-gray-700">Featured Product</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Images -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Product Images</h3>
                        
                        <div class="space-y-4">
                            <!-- Current Main Image -->
                            @if($product->main_image)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Main Image</label>
                                <img src="{{ Storage::url($product->main_image) }}" 
                                     alt="Main image" 
                                     class="w-32 h-32 object-cover rounded-lg">
                            </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Main Image</label>
                                <input type="file" 
                                       name="main_image" 
                                       accept="image/jpeg,image/png,image/jpg,image/webp"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image</p>
                            </div>

                            <!-- Current Gallery Images -->
                            @if($product->gallery_images && count($product->gallery_images) > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Gallery Images</label>
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach($product->gallery_images as $image)
                                    <div class="relative group">
                                        <img src="{{ Storage::url($image) }}" 
                                             alt="Gallery image" 
                                             class="w-full h-20 object-cover rounded-lg">
                                        <label class="absolute top-1 right-1 bg-red-600 text-white p-1 rounded opacity-0 group-hover:opacity-100 cursor-pointer">
                                            <input type="checkbox" 
                                                   name="remove_gallery_images[]" 
                                                   value="{{ $image }}"
                                                   class="hidden">
                                            <i class="fas fa-times text-xs"></i>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Click X to mark images for removal</p>
                            </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Add Gallery Images</label>
                                <input type="file" 
                                       name="gallery_images[]" 
                                       multiple
                                       accept="image/jpeg,image/png,image/jpg,image/webp"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>
                        </div>
                    </div>

                    <!-- SEO -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">SEO Settings</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                                <input type="text" 
                                       name="meta_title" 
                                       value="{{ old('meta_title', $product->meta_title) }}"
                                       maxlength="60"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                                <textarea name="meta_description" 
                                          rows="2"
                                          maxlength="160"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">{{ old('meta_description', $product->meta_description) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Keywords</label>
                                <input type="text" 
                                       name="meta_keywords" 
                                       value="{{ old('meta_keywords', $product->meta_keywords) }}"
                                       placeholder="keyword1, keyword2, keyword3"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Product Statistics</h3>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Views:</span>
                                <span class="font-semibold">{{ number_format($product->view_count) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Reviews:</span>
                                <span class="font-semibold">{{ $product->review_count }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Average Rating:</span>
                                <span class="font-semibold">{{ number_format($product->average_rating, 1) }} ⭐</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Created:</span>
                                <span class="font-semibold">{{ $product->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.products.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Update Product
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let variantIndex = {{ $product->variants->count() }};

function addVariant() {
    const container = document.getElementById('variants-container');
    const div = document.createElement('div');
    div.className = 'flex gap-2 variant-row';
    div.innerHTML = `
        <select name="variants[${variantIndex}][variant_type]" class="px-3 py-2 border border-gray-300 rounded-lg">
            <option value="flex">Flex</option>
            <option value="loft">Loft</option>
            <option value="length">Length</option>
            <option value="color">Color</option>
            <option value="hand">Hand</option>
            <option value="size">Size</option>
        </select>
        <input type="text" 
               name="variants[${variantIndex}][variant_name]"
               placeholder="Variant name"
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
        <input type="text" 
               name="variants[${variantIndex}][variant_value]"
               placeholder="Value"
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
        <input type="number" 
               name="variants[${variantIndex}][price]"
               placeholder="Price"
               step="0.01"
               class="w-24 px-3 py-2 border border-gray-300 rounded-lg">
        <button type="button" onclick="removeVariant(this)" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
    variantIndex++;
}

function removeVariant(button) {
    button.closest('.variant-row').remove();
}

// Toggle image removal
document.querySelectorAll('input[name="remove_gallery_images[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const img = this.closest('.relative').querySelector('img');
        if (this.checked) {
            img.style.opacity = '0.5';
        } else {
            img.style.opacity = '1';
        }
    });
});
</script>
@endpush