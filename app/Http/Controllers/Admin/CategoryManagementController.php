<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryManagementController extends Controller
{
    /**
     * Display listing of categories
     */
    public function index(Request $request)
    {
        $query = Category::with('parent')->withCount('products');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by parent
        if ($request->has('parent_only')) {
            $query->whereNull('parent_category_id');
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Sorting
        $sortField = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        
        if ($sortField == 'sort_order') {
            $query->orderBy('parent_category_id', 'asc')->orderBy('sort_order', 'asc');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        $categories = $query->paginate(20)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show form for creating new category
     */
    public function create()
    {
        $parentCategories = Category::whereNull('parent_category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
            
        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store new category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'parent_category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255'
        ]);

        // Prevent category from being its own parent
        if ($request->parent_category_id) {
            $parent = Category::find($request->parent_category_id);
            if ($parent->parent_category_id !== null) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Cannot create sub-category under another sub-category. Only 2 levels allowed.');
            }
        }

        try {
            $data = $request->except('image');
            
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($request->name);
                
                // Make unique if needed
                $count = 1;
                while (Category::where('slug', $data['slug'])->exists()) {
                    $data['slug'] = Str::slug($request->name) . '-' . $count;
                    $count++;
                }
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image_url'] = $request->file('image')->store('categories', 'public');
            }

            // Set default sort order
            if (empty($data['sort_order'])) {
                $maxOrder = Category::where('parent_category_id', $request->parent_category_id)
                    ->max('sort_order');
                $data['sort_order'] = ($maxOrder ?? 0) + 1;
            }

            // Prepare meta data
            $data['meta_data'] = [
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords
            ];

            Category::create($data);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating category: ' . $e->getMessage());
        }
    }

    /**
     * Display category details
     */
    public function show(Category $category)
    {
        $category->load(['products' => function ($query) {
            $query->latest()->take(10);
        }, 'children', 'parent']);
        
        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show form for editing category
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_category_id')
            ->where('id', '!=', $category->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
            
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update category
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'parent_category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255'
        ]);

        // Prevent category from being its own parent
        if ($request->parent_category_id == $category->id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Category cannot be its own parent.');
        }

        // Prevent creating circular reference
        if ($request->parent_category_id && $category->children->contains('id', $request->parent_category_id)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cannot set a child category as parent.');
        }

        try {
            $data = $request->except('image');

            // Generate slug if changed name
            if ($request->name !== $category->name && empty($data['slug'])) {
                $data['slug'] = Str::slug($request->name);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($category->image_url) {
                    Storage::disk('public')->delete($category->image_url);
                }
                $data['image_url'] = $request->file('image')->store('categories', 'public');
            }

            // Update meta data
            $data['meta_data'] = [
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords
            ];

            $category->update($data);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating category: ' . $e->getMessage());
        }
    }

    /**
     * Delete category
     */
    public function destroy(Category $category)
    {
        try {
            // Check if category has products
            if ($category->products()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete category with associated products. Please reassign or delete products first.');
            }

            // Check if category has children
            if ($category->children()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete category with sub-categories. Please delete sub-categories first.');
            }

            // Delete image
            if ($category->image_url) {
                Storage::disk('public')->delete($category->image_url);
            }

            $category->delete();

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting category: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        
        // Also update children if this is a parent category
        if ($category->children()->count() > 0) {
            $category->children()->update(['is_active' => $category->is_active]);
        }
        
        return response()->json([
            'success' => true,
            'is_active' => $category->is_active
        ]);
    }

    /**
     * Update categories order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.sort_order' => 'required|integer|min:0'
        ]);

        foreach ($request->categories as $item) {
            Category::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get category tree for select
     */
    public function getCategoryTree()
    {
        $categories = Category::with('children')
            ->whereNull('parent_category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }
}