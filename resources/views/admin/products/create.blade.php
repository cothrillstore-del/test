@extends('admin.layouts.app')

@section('title', 'Add New Product')

@section('content')
<div class="container-fluid">
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b">
            <div class="flex items-center">
                <a href="{{ route('admin.products.index') }}" class="mr-4">
                    <i class="fas fa-arrow-left text-gray-600"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Add New Product</h1>
            </div>
        </div>

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
                                       value="{{ old('name') }}"
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
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('brand_id') border-red-500 @enderror"
                                            required>
                                        <option value="">Select Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('brand_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Category <span class="text-red-500">*</span>
                                    </label>
                                    <select name="category_id" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('category_id') border-red-500 @enderror"
                                            required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                            @foreach($category->children as $child)
                                                <option value="{{ $child->id }}" {{ old('category_id') == $child->id ? 'selected' : '' }}>
                                                    &nbsp;&nbsp;-- {{ $child->name }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Model Year</label>
                                    <input type="number" 
                                           name="model_year" 
                                           value="{{ old('model_year', date('Y')) }}"
                                           min="2000" 
                                           max="{{ date('Y') + 2 }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                                    <input type="text" 
                                           name="sku" 
                                           value="{{ old('sku') }}"
                                           placeholder="Auto-generate if empty"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                                <textarea name="short_description" 
                                          rows="2"
                                          maxlength="500"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">{{ old('short_description') }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">Max 500 characters</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Description</label>
                                <textarea name="description" 
                                          rows="6"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">{{ old('description') }}</textarea>
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
                                       value="{{ old('price_min') }}"
                                       min="0" 
                                       step="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Max Price ($)</label>
                                <input type="number" 
                                       name="price_max" 
                                       value="{{ old('price_max') }}"
                                       min="0" 
                                       step="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Retail Price ($)</label>
                                <input type="number" 
                                       name="retail_price" 
                                       value="{{ old('retail_price') }}"
                                       min="0" 
                                       step="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>
                        </div>
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
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="coming_soon" {{ old('status') == 'coming_soon' ? 'selected' : '' }}>Coming Soon</option>
                                    <option value="discontinued" {{ old('status') == 'discontinued' ? 'selected' : '' }}>Discontinued</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Release Date</label>
                                <input type="date" 
                                       name="release_date" 
                                       value="{{ old('release_date') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="is_featured" 
                                           value="1"
                                           {{ old('is_featured') ? 'checked' : '' }}
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
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Main Image</label>
                                <input type="file" 
                                       name="main_image" 
                                       accept="image/jpeg,image/png,image/jpg,image/webp"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <p class="text-xs text-gray-500 mt-1">Recommended: 800x800px, Max 2MB</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gallery Images</label>
                                <input type="file" 
                                       name="gallery_images[]" 
                                       multiple
                                       accept="image/jpeg,image/png,image/jpg,image/webp"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <p class="text-xs text-gray-500 mt-1">Multiple images allowed, Max 2MB each</p>
                            </div>
                        </div>
                    </div>

                    <!-- Specifications -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Specifications</h3>
                        
                        <div id="specifications-container" class="space-y-2">
                            <div class="flex gap-2">
                                <input type="text" 
                                       placeholder="Specification name"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
                                <input type="text" 
                                       placeholder="Value"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
                                <button type="button" onclick="addSpecification()" class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    <i class="fas fa-plus"></i>
                                </button>
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
                                       value="{{ old('meta_title') }}"
                                       maxlength="60"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <p class="text-xs text-gray-500 mt-1">Max 60 characters</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                                <textarea name="meta_description" 
                                          rows="2"
                                          maxlength="160"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">{{ old('meta_description') }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">Max 160 characters</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Keywords</label>
                                <input type="text" 
                                       name="meta_keywords" 
                                       value="{{ old('meta_keywords') }}"
                                       placeholder="keyword1, keyword2, keyword3"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
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
                <button type="submit" name="action" value="save_draft" 
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Save as Draft
                </button>
                <button type="submit" name="action" value="save_publish" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Save & Publish
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let specIndex = 0;

function addSpecification() {
    const container = document.getElementById('specifications-container');
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    div.innerHTML = `
        <input type="text" 
               name="specifications[${specIndex}][name]"
               placeholder="Specification name"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
        <input type="text" 
               name="specifications[${specIndex}][value]"
               placeholder="Value"
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
        <button type="button" onclick="this.parentElement.remove()" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
    specIndex++;
}

// Handle save as draft
document.querySelector('button[value="save_draft"]').addEventListener('click', function(e) {
    e.preventDefault();
    document.querySelector('select[name="status"]').value = 'draft';
    this.form.submit();
});
</script>
@endpush