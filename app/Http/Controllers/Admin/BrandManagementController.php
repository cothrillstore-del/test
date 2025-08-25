<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandManagementController extends Controller
{
    /**
     * Display listing of brands
     */
    public function index(Request $request)
    {
        $query = Brand::withCount('products');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        // Filter by featured
        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->is_featured);
        }

        // Sorting
        $sortField = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $brands = $query->paginate(15)->withQueryString();

        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Show form for creating new brand
     */
    public function create()
    {
        $countries = $this->getCountriesList();
        return view('admin.brands.create', compact('countries'));
    }

    /**
     * Store new brand
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:1024',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            $data = $request->except('logo');
            
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($request->name);
            }

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $data['logo_url'] = $request->file('logo')->store('brands', 'public');
            }

            // Set default sort order
            if (empty($data['sort_order'])) {
                $data['sort_order'] = Brand::max('sort_order') + 1;
            }

            Brand::create($data);

            return redirect()->route('admin.brands.index')
                ->with('success', 'Brand created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating brand: ' . $e->getMessage());
        }
    }

    /**
     * Display brand details
     */
    public function show(Brand $brand)
    {
        $brand->load(['products' => function ($query) {
            $query->latest()->take(10);
        }]);
        
        return view('admin.brands.show', compact('brand'));
    }

    /**
     * Show form for editing brand
     */
    public function edit(Brand $brand)
    {
        $countries = $this->getCountriesList();
        return view('admin.brands.edit', compact('brand', 'countries'));
    }

    /**
     * Update brand
     */
    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $brand->id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:1024',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            $data = $request->except('logo');

            // Generate slug if changed name
            if ($request->name !== $brand->name && empty($data['slug'])) {
                $data['slug'] = Str::slug($request->name);
            }

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo
                if ($brand->logo_url) {
                    Storage::disk('public')->delete($brand->logo_url);
                }
                $data['logo_url'] = $request->file('logo')->store('brands', 'public');
            }

            $brand->update($data);

            return redirect()->route('admin.brands.index')
                ->with('success', 'Brand updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating brand: ' . $e->getMessage());
        }
    }

    /**
     * Delete brand
     */
    public function destroy(Brand $brand)
    {
        try {
            // Check if brand has products
            if ($brand->products()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete brand with associated products. Please reassign or delete products first.');
            }

            // Delete logo
            if ($brand->logo_url) {
                Storage::disk('public')->delete($brand->logo_url);
            }

            $brand->delete();

            return redirect()->route('admin.brands.index')
                ->with('success', 'Brand deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting brand: ' . $e->getMessage());
        }
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Brand $brand)
    {
        $brand->update(['is_featured' => !$brand->is_featured]);
        
        return response()->json([
            'success' => true,
            'is_featured' => $brand->is_featured
        ]);
    }

    /**
     * Update brands order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'brands' => 'required|array',
            'brands.*.id' => 'required|exists:brands,id',
            'brands.*.sort_order' => 'required|integer|min:0'
        ]);

        foreach ($request->brands as $item) {
            Brand::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get countries list
     */
    private function getCountriesList()
    {
        return [
            'USA' => 'United States',
            'Japan' => 'Japan',
            'UK' => 'United Kingdom',
            'Germany' => 'Germany',
            'France' => 'France',
            'Canada' => 'Canada',
            'Australia' => 'Australia',
            'South Korea' => 'South Korea',
            'Taiwan' => 'Taiwan',
            'China' => 'China',
        ];
    }
}