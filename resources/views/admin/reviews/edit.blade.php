@extends('admin.layouts.app')

@section('title', 'Edit Review')

@section('content')
<div class="container-fluid max-w-4xl">
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b">
            <div class="flex items-center">
                <a href="{{ route('admin.reviews.index') }}" class="mr-4">
                    <i class="fas fa-arrow-left text-gray-600"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Edit Review</h1>
            </div>
        </div>

        <form action="{{ route('admin.reviews.update', $review) }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Product & Author Info (Read-only) -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Review Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                            <div class="px-3 py-2 bg-white border border-gray-300 rounded-lg">
                                {{ $review->product->name }} ({{ $review->product->brand->name }})
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                            <div class="px-3 py-2 bg-white border border-gray-300 rounded-lg">
                                {{ $review->user->name }} ({{ $review->user->email }})
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Review Content -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Review Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="title" 
                           value="{{ old('title', $review->title) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('title') border-red-500 @enderror"
                           required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Rating <span class="text-red-500">*</span>
                    </label>
                    <div class="flex space-x-4">
                        @for($i = 1; $i <= 5; $i++)
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" 
                                   name="rating" 
                                   value="{{ $i }}"
                                   {{ old('rating', $review->rating) == $i ? 'checked' : '' }}
                                   class="mr-2"
                                   required>
                            <span class="flex">
                                @for($j = 1; $j <= $i; $j++)
                                    <i class="fas fa-star text-yellow-400"></i>
                                @endfor
                                @for($j = $i + 1; $j <= 5; $j++)
                                    <i class="fas fa-star text-gray-300"></i>
                                @endfor
                            </span>
                        </label>
                        @endfor
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Review Content <span class="text-red-500">*</span>
                    </label>
                    <textarea name="content" 
                              rows="8"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('content') border-red-500 @enderror"
                              required>{{ old('content', $review->content) }}</textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pros and Cons -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pros</label>
                        <div id="pros-container" class="space-y-2">
                            @if($review->pros)
                                @foreach($review->pros as $index => $pro)
                                <div class="flex gap-2">
                                    <input type="text" 
                                           name="pros[]" 
                                           value="{{ $pro }}"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
                                    <button type="button" onclick="this.parentElement.remove()" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" onclick="addPro()" class="mt-2 text-sm text-green-600 hover:text-green-700">
                            <i class="fas fa-plus mr-1"></i>Add Pro
                        </button>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cons</label>
                        <div id="cons-container" class="space-y-2">
                            @if($review->cons)
                                @foreach($review->cons as $index => $con)
                                <div class="flex gap-2">
                                    <input type="text" 
                                           name="cons[]" 
                                           value="{{ $con }}"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
                                    <button type="button" onclick="this.parentElement.remove()" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" onclick="addCon()" class="mt-2 text-sm text-red-600 hover:text-red-700">
                            <i class="fas fa-plus mr-1"></i>Add Con
                        </button>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Skill Level</label>
                        <select name="skill_level" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            <option value="">Select Skill Level</option>
                            <option value="beginner" {{ old('skill_level', $review->skill_level) == 'beginner' ? 'selected' : '' }}>Beginner</option>
                            <option value="intermediate" {{ old('skill_level', $review->skill_level) == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                            <option value="advanced" {{ old('skill_level', $review->skill_level) == 'advanced' ? 'selected' : '' }}>Advanced</option>
                            <option value="pro" {{ old('skill_level', $review->skill_level) == 'pro' ? 'selected' : '' }}>Pro</option>
                        </select>
                    </div>

                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="verified_purchase" 
                                   value="1"
                                   {{ old('verified_purchase', $review->verified_purchase) ? 'checked' : '' }}
                                   class="mr-2 rounded border-gray-300 text-green-600">
                            <span class="text-sm font-medium text-gray-700">Verified Purchase</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_featured" 
                                   value="1"
                                   {{ old('is_featured', $review->is_featured) ? 'checked' : '' }}
                                   class="mr-2 rounded border-gray-300 text-yellow-600">
                            <span class="text-sm font-medium text-gray-700">Featured Review</span>
                        </label>
                    </div>
                </div>

                <!-- Media -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Review Media</label>
                    
                    @if($review->media->count() > 0)
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Current Media (check to remove):</p>
                        <div class="grid grid-cols-4 gap-3">
                            @foreach($review->media as $media)
                            <div class="relative">
                                <img src="{{ Storage::url($media->media_url) }}" 
                                     alt="Review media"
                                     class="w-full h-20 object-cover rounded-lg">
                                <label class="absolute top-1 right-1 bg-red-600 text-white p-1 rounded cursor-pointer">
                                    <input type="checkbox" 
                                           name="remove_media[]" 
                                           value="{{ $media->id }}"
                                           class="hidden">
                                    <i class="fas fa-times text-xs"></i>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Add New Media:</label>
                        <input type="file" 
                               name="new_media[]" 
                               multiple
                               accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <p class="text-xs text-gray-500 mt-1">You can select multiple images</p>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex justify-end space-x-3 pt-6 border-t">
                <a href="{{ route('admin.reviews.show', $review) }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Update Review
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function addPro() {
    const container = document.getElementById('pros-container');
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    div.innerHTML = `
        <input type="text" 
               name="pros[]" 
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
               placeholder="Enter a pro">
        <button type="button" onclick="this.parentElement.remove()" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function addCon() {
    const container = document.getElementById('cons-container');
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    div.innerHTML = `
        <input type="text" 
               name="cons[]" 
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
               placeholder="Enter a con">
        <button type="button" onclick="this.parentElement.remove()" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}
</script>
@endpush