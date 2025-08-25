@extends('admin.layouts.app')

@section('title', 'Brands Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Brands Management</h1>
                <p class="text-gray-600 mt-1">Manage golf equipment brands</p>
            </div>
            <a href="{{ route('admin.brands.create') }}" 
               class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Add New Brand
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.brands.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search brands..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            
            <select name="is_featured" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">All Brands</option>
                <option value="1" {{ request('is_featured') == '1' ? 'selected' : '' }}>Featured Only</option>
                <option value="0" {{ request('is_featured') == '0' ? 'selected' : '' }}>Non-Featured</option>
            </select>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                <i class="fas fa-search mr-2"></i>Filter
            </button>

            <a href="{{ route('admin.brands.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg">
                <i class="fas fa-redo mr-2"></i>Reset
            </a>
        </form>
    </div>

    <!-- Brands Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-6 border-b">
            <p class="text-gray-600">Total: {{ $brands->total() }} brands</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Order
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Brand
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Country
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Products
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Featured
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Website
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="brands-list">
                    @forelse($brands as $brand)
                    <tr class="hover:bg-gray-50 brand-row" data-id="{{ $brand->id }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <i class="fas fa-grip-vertical text-gray-400 mr-2 drag-handle cursor-move"></i>
                                <span class="text-sm text-gray-600">{{ $brand->sort_order }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($brand->logo_url)
                                    <img src="{{ Storage::url($brand->logo_url) }}" 
                                         alt="{{ $brand->name }}"
                                         class="w-10 h-10 rounded object-contain bg-gray-100 p-1 mr-3">
                                @else
                                    <div class="w-10 h-10 rounded bg-gray-200 flex items-center justify-center mr-3">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $brand->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $brand->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $brand->country ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-900">{{ $brand->products_count }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="toggleFeatured({{ $brand->id }})" 
                                    class="toggle-featured"
                                    data-id="{{ $brand->id }}">
                                <i class="fas fa-star {{ $brand->is_featured ? 'text-yellow-500' : 'text-gray-300' }} text-xl"></i>
                            </button>
                        </td>
                        <td class="px-6 py-4">
                            @if($brand->website)
                                <a href="{{ $brand->website }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('admin.brands.show', $brand) }}" 
                                   class="text-blue-600 hover:text-blue-900"
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.brands.edit', $brand) }}" 
                                   class="text-green-600 hover:text-green-900"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($brand->products_count == 0)
                                <form action="{{ route('admin.brands.destroy', $brand) }}" 
                                      method="POST" 
                                      class="inline-block"
                                      onsubmit="return confirm('Are you sure you want to delete this brand?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No brands found. <a href="{{ route('admin.brands.create') }}" class="text-green-600">Add your first brand</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t">
            {{ $brands->links() }}
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .ui-sortable-helper {
        background: white;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script>
// Toggle featured
function toggleFeatured(brandId) {
    fetch(`/admin/brands/${brandId}/toggle-featured`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const star = document.querySelector(`[data-id="${brandId}"] i`);
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

// Sortable for reordering
$(function() {
    $("#brands-list").sortable({
        handle: ".drag-handle",
        update: function(event, ui) {
            let brands = [];
            $('.brand-row').each(function(index) {
                brands.push({
                    id: $(this).data('id'),
                    sort_order: index + 1
                });
            });

            fetch('{{ route("admin.brands.update-order") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ brands: brands })
            });
        }
    });
});
</script>
@endpush