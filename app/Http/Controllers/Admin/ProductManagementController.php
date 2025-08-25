<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductManagementController extends Controller
{
    /**
     * Display listing of products
     */
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'category']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->is_featured);
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $products = $query->paginate(15)->withQueryString();
        $brands = Brand::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'brands', 'categories'));
    }

    /**
     * Show form for creating new product
     */
    public function create()
    {
        $brands = Brand::orderBy('name')->get();
        $categories = Category::with('children')->parents()->ordered()->get();
        
        return view('admin.products.create', compact('brands', 'categories'));
    }

    /**
     * Store new product
     */
    public function store(Request $request)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'model_year' => 'nullable|digits:4|min:2000|max:' . (date('Y') + 2),
            'sku' => 'nullable|string|unique:products,sku',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0|gte:price_min',
            'retail_price' => 'nullable|numeric|min:0',
            'release_date' => 'nullable|date',
            'status' => 'required|in:active,discontinued,coming_soon,draft',
            'is_featured' => 'boolean',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'specifications' => 'nullable|array',
            'features' => 'nullable|array',
            'variants' => 'nullable|array',
            'variants.*.variant_type' => 'required_with:variants|string',
            'variants.*.variant_name' => 'required_with:variants|string',
            'variants.*.variant_value' => 'required_with:variants|string',
            'variants.*.price' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Handle main image upload
            $mainImagePath = null;
            if ($request->hasFile('main_image')) {
                $mainImagePath = $request->file('main_image')->store('products/main', 'public');
            }

            // Handle gallery images upload
            $galleryImages = [];
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    $galleryImages[] = $image->store('products/gallery', 'public');
                }
            }

            // Create product
            $productData = $request->except(['main_image', 'gallery_images', 'variants']);
            $productData['main_image'] = $mainImagePath;
            $productData['gallery_images'] = $galleryImages;
            $productData['slug'] = Str::slug($request->name . ' ' . $request->model_year);
            
            // Generate SKU if not provided
            if (empty($productData['sku'])) {
                $productData['sku'] = $this->generateSku($request->brand_id, $request->category_id);
            }

            $product = Product::create($productData);

            // Create variants if provided
            if ($request->has('variants') && is_array($request->variants)) {
                foreach ($request->variants as $index => $variantData) {
                    $variantData['product_id'] = $product->id;
                    $variantData['sort_order'] = $index;
                    if (empty($variantData['sku'])) {
                        $variantData['sku'] = $product->sku . '-' . strtoupper(Str::random(3));
                    }
                    ProductVariant::create($variantData);
                }
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded files
            if ($mainImagePath) {
                Storage::disk('public')->delete($mainImagePath);
            }
            foreach ($galleryImages as $image) {
                Storage::disk('public')->delete($image);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating product: ' . $e->getMessage());
        }
    }

    /**
     * Display product details
     */
    public function show(Product $product)
    {
        $product->load(['brand', 'category', 'variants', 'reviews.author']);
        
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show form for editing product
     */
    public function edit(Product $product)
    {
        $brands = Brand::orderBy('name')->get();
        $categories = Category::with('children')->parents()->ordered()->get();
        $product->load('variants');
        
        return view('admin.products.edit', compact('product', 'brands', 'categories'));
    }

    /**
     * Update product
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'model_year' => 'nullable|digits:4|min:2000|max:' . (date('Y') + 2),
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0|gte:price_min',
            'retail_price' => 'nullable|numeric|min:0',
            'release_date' => 'nullable|date',
            'status' => 'required|in:active,discontinued,coming_soon,draft',
            'is_featured' => 'boolean',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'specifications' => 'nullable|array',
            'features' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $updateData = $request->except(['main_image', 'gallery_images', 'variants', 'remove_gallery_images']);

            // Handle main image upload
            if ($request->hasFile('main_image')) {
                // Delete old image
                if ($product->main_image) {
                    Storage::disk('public')->delete($product->main_image);
                }
                $updateData['main_image'] = $request->file('main_image')->store('products/main', 'public');
            }

            // Handle gallery images
            $currentGallery = $product->gallery_images ?? [];
            
            // Remove selected images
            if ($request->has('remove_gallery_images')) {
                foreach ($request->remove_gallery_images as $imageToRemove) {
                    Storage::disk('public')->delete($imageToRemove);
                    $currentGallery = array_diff($currentGallery, [$imageToRemove]);
                }
            }

            // Add new gallery images
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    $currentGallery[] = $image->store('products/gallery', 'public');
                }
            }
            
            $updateData['gallery_images'] = array_values($currentGallery);

            // Update product
            $product->update($updateData);

            // Update variants
            if ($request->has('variants')) {
                // Delete removed variants
                $existingVariantIds = collect($request->variants)
                    ->filter(function ($variant) {
                        return isset($variant['id']);
                    })
                    ->pluck('id')
                    ->toArray();
                
                $product->variants()->whereNotIn('id', $existingVariantIds)->delete();

                // Update or create variants
                foreach ($request->variants as $index => $variantData) {
                    $variantData['sort_order'] = $index;
                    
                    if (isset($variantData['id'])) {
                        $product->variants()->where('id', $variantData['id'])->update($variantData);
                    } else {
                        $variantData['product_id'] = $product->id;
                        if (empty($variantData['sku'])) {
                            $variantData['sku'] = $product->sku . '-' . strtoupper(Str::random(3));
                        }
                        ProductVariant::create($variantData);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating product: ' . $e->getMessage());
        }
    }

    /**
     * Delete product
     */
    public function destroy(Product $product)
    {
        try {
            // Delete images
            if ($product->main_image) {
                Storage::disk('public')->delete($product->main_image);
            }
            
            if ($product->gallery_images) {
                foreach ($product->gallery_images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $product->delete();

            return redirect()->route('admin.products.index')
                ->with('success', 'Product deleted successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting product: ' . $e->getMessage());
        }
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);
        
        return response()->json([
            'success' => true,
            'is_featured' => $product->is_featured
        ]);
    }

    /**
     * Bulk delete products
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        try {
            $products = Product::whereIn('id', $request->product_ids)->get();
            
            foreach ($products as $product) {
                // Delete images
                if ($product->main_image) {
                    Storage::disk('public')->delete($product->main_image);
                }
                
                if ($product->gallery_images) {
                    foreach ($product->gallery_images as $image) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }

            Product::whereIn('id', $request->product_ids)->delete();

            return response()->json([
                'success' => true,
                'message' => count($request->product_ids) . ' products deleted successfully!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique SKU
     */
    private function generateSku($brandId, $categoryId)
    {
        $brand = Brand::find($brandId);
        $category = Category::find($categoryId);
        
        $prefix = strtoupper(substr($brand->name, 0, 3) . substr($category->name, 0, 2));
        $random = strtoupper(Str::random(5));
        
        return $prefix . '-' . $random;
    }
}