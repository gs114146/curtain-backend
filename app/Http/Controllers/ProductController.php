<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductMedia;
use App\Models\Color;
use App\Models\Design;
use App\Models\Material;
use App\Models\Transparency;
use App\Models\Characteristic;
use App\Models\Usage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return $this->getDataTableData();
        }

        // Get related data for forms
        $colors = Color::where('status', 'active')->orderBy('name', 'asc')->get();
        $designs = Design::where('status', 'active')->orderBy('name', 'asc')->get();
        $materials = Material::where('status', 'active')->orderBy('name', 'asc')->get();
        $transparencies = Transparency::where('status', 'active')->orderBy('name', 'asc')->get();
        $characteristics = Characteristic::where('status', 'active')->orderBy('name', 'asc')->get();
        $usage = Usage::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('admin.products.index', compact('colors', 'designs', 'materials', 'transparencies', 'characteristics', 'usage'));
    }

    /**
     * Show the form for creating a new product (dedicated page instead of modal).
     */
    public function create()
    {
        // Get related data for forms
        $colors = Color::where('status', 'active')->orderBy('name', 'asc')->get();
        $designs = Design::where('status', 'active')->orderBy('name', 'asc')->get();
        $materials = Material::where('status', 'active')->orderBy('name', 'asc')->get();
        $transparencies = Transparency::where('status', 'active')->orderBy('name', 'asc')->get();
        $characteristics = Characteristic::where('status', 'active')->orderBy('name', 'asc')->get();
        $usage = Usage::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('admin.products.create', compact('colors', 'designs', 'materials', 'transparencies', 'characteristics', 'usage'));
    }

    /**
     * Get DataTable data for products.
     */
    public function getDataTableData()
    {
        $query = Product::with(['design', 'material', 'transparency', 'characteristic', 'usage', 'variants.color']);

        // Handle search (from DataTables search)
        if (request()->has('search') && !empty(request('search')['value'])) {
            $searchValue = request('search')['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('sku', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%")
                    ->orWhereHas('design', function ($designQuery) use ($searchValue) {
                        $designQuery->where('name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('material', function ($materialQuery) use ($searchValue) {
                        $materialQuery->where('name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('variants.color', function ($colorQuery) use ($searchValue) {
                        $colorQuery->where('name', 'like', "%{$searchValue}%");
                    });
            });
        }

        // Handle custom filters
        $this->applyCustomFilters($query);

        // Handle ordering
        if (request()->has('order')) {
            $orderColumn = request('order')[0]['column'];
            $orderDir = request('order')[0]['dir'];
            $columns = ['name', 'variants', 'price_range', 'stock_total', 'status', 'actions'];

            if (isset($columns[$orderColumn])) {
                if ($columns[$orderColumn] === 'actions') {
                    // Actions column is not orderable, so order by name instead
                    $query->orderBy('name', $orderDir);
                } else {
                    $query->orderBy($columns[$orderColumn], $orderDir);
                }
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        // Get total count before pagination
        $totalRecords = Product::count();
        $filteredRecords = $query->count();

        // Handle pagination
        $start = request('start', 0);
        $length = request('length', 10);
        $products = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($products as $product) {
            $variants = $product->variants;
            $variantCount = $variants->count();
            $variantNames = $variants->pluck('color.name')->join(', ');

            // Get price range from variants
            $prices = $variants->pluck('price')->filter();
            $minPrice = $prices->min();
            $maxPrice = $prices->max();
            $priceRange = $minPrice ? '€' . number_format($minPrice, 2) . ($minPrice != $maxPrice ? ' - €' . number_format($maxPrice, 2) : '') : 'N/A';

            // Get total stock from all variants
            $totalStock = $variants->sum('stock_quantity');

            $data[] = [
                'name' => $product->name,
                'variants' => $variantCount > 0 ? $variantCount . ' variants (' . $variantNames . ')' : 'No variants',
                'price_range' => $priceRange,
                'stock_total' => $totalStock,
                'status' => $product->status,
                'actions' => $product->id
            ];
        }

        return response()->json([
            'draw' => intval(request('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Check if this is a variant-based product or legacy product
            $hasVariants = $request->has('variants') && is_array($request->variants) && count($request->variants) > 0;

            if ($hasVariants) {
                // New variant-based product
                $request->validate([
                    'name' => 'required|string|max:255',
                    'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                    'description' => 'required|string',
                    'manual_product_id' => 'required|string|max:255|unique:products,manual_product_id',
                    'dimensions' => 'required|string|max:255',
                    'weight' => 'required|string|max:255',
                    'horizontal_repeat' => 'nullable|string|max:255',
                    'vertical_repeat' => 'nullable|string|max:255',
                    'direction' => 'nullable|string|max:255',
                    'extra_data' => 'nullable|string',
                    'design_ids' => 'required|array|min:1',
                    'design_ids.*' => 'exists:designs,id',
                    'material_ids' => 'required|array|min:1',
                    'material_ids.*' => 'exists:materials,id',
                    'transparency_id' => 'nullable|exists:transparency,id',
                    'characteristic_ids' => 'required|array|min:1',
                    'characteristic_ids.*' => 'exists:characteristics,id',
                    'usage_id' => 'required|exists:usage,id',
                    'type' => 'required|in:product_listing,view_qualities,upholstery,latest',
                    'product_base' => 'required|in:curtain,upholstery',
                    'status' => 'in:active,inactive,draft',
                    'featured' => 'boolean',
                    'meta_title' => 'nullable|string|max:255',
                    'meta_description' => 'nullable|string',
                    'designs' => 'nullable|string',
                    'compositions' => 'nullable|string',
                    'hsn_code' => 'nullable|string|max:255',
                    'gst' => 'nullable|numeric|min:0|max:100',
                    'variants' => 'required|array|min:1',
                    'variants.*.color_id' => 'required|exists:colors,id',
                    'variants.*.article' => 'required|string|max:255',
                    'variants.*.article_description' => 'required|string',
                    'variants.*.price' => 'nullable|numeric|min:0',
                    'variants.*.sale_price' => 'nullable|numeric|min:0',
                    'variants.*.stock_quantity' => 'nullable|integer|min:0',
                    'variants.*.sku' => 'nullable|string',
                    'variants.*.is_default' => 'boolean',
                    'variants.*.images' => 'nullable|array',
                    'variants.*.images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240'
                ]);

                $data = $request->all();
                $data['slug'] = $this->createUniqueSlug($request->name);

                // Handle product image upload
                if ($request->hasFile('image')) {
                    $data['image'] = $this->uploadProductImage($request->file('image'));
                }

                // Extract relationship IDs before removing from data
                $designIds = $request->input('design_ids', []);
                $materialIds = $request->input('material_ids', []);
                $characteristicIds = $request->input('characteristic_ids', []);

                // Remove variants and relationship arrays from product data
                unset($data['variants'], $data['design_ids'], $data['material_ids'], $data['characteristic_ids']);

                // Create the base product
                $product = Product::create($data);

                // Sync many-to-many relationships
                $product->designs()->sync($designIds);
                $product->materials()->sync($materialIds);
                $product->characteristics()->sync($characteristicIds);

                // Create variants
                $this->createProductVariants($product, $request->variants);
            } else {
                // Legacy product with single color
                $request->validate([
                    'name' => 'required|string|max:255',
                    'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                    'description' => 'required|string',
                    'sku' => 'nullable|string|unique:products,sku',
                    'manual_product_id' => 'required|string|max:255|unique:products,manual_product_id',
                    'price' => 'nullable|numeric|min:0',
                    'sale_price' => 'nullable|numeric|min:0',
                    'stock_quantity' => 'nullable|integer|min:0',
                    'dimensions' => 'required|string|max:255',
                    'weight' => 'required|string|max:255',
                    'horizontal_repeat' => 'nullable|string|max:255',
                    'vertical_repeat' => 'nullable|string|max:255',
                    'direction' => 'nullable|string|max:255',
                    'extra_data' => 'nullable|string',
                    'color_id' => 'required|exists:colors,id',
                    'design_ids' => 'required|array|min:1',
                    'design_ids.*' => 'exists:designs,id',
                    'material_ids' => 'required|array|min:1',
                    'material_ids.*' => 'exists:materials,id',
                    'transparency_id' => 'nullable|exists:transparency,id',
                    'characteristic_ids' => 'required|array|min:1',
                    'characteristic_ids.*' => 'exists:characteristics,id',
                    'usage_id' => 'required|exists:usage,id',
                    'type' => 'required|in:product_listing,view_qualities,upholstery,latest',
                    'product_base' => 'required|in:curtain,upholstery',
                    'status' => 'in:active,inactive,draft',
                    'featured' => 'boolean',
                    'in_stock' => 'boolean',
                    'meta_title' => 'nullable|string|max:255',
                    'meta_description' => 'nullable|string',
                    'designs' => 'nullable|string',
                    'compositions' => 'nullable|string',
                    'hsn_code' => 'nullable|string|max:255',
                    'gst' => 'nullable|numeric|min:0|max:100',
                ]);

                $data = $request->all();
                $data['slug'] = $this->createUniqueSlug($request->name);

                // Handle product image upload
                if ($request->hasFile('image')) {
                    $data['image'] = $this->uploadProductImage($request->file('image'));
                }

                // Auto-generate SKU if not provided
                if (empty($data['sku'])) {
                    $data['sku'] = $this->createSkuFromName($request->name);
                }

                // Extract relationship IDs before removing from data
                $designIds = $request->input('design_ids', []);
                $materialIds = $request->input('material_ids', []);
                $characteristicIds = $request->input('characteristic_ids', []);

                // Remove relationship arrays from product data
                unset($data['design_ids'], $data['material_ids'], $data['characteristic_ids']);

                $product = Product::create($data);

                // Sync many-to-many relationships
                $product->designs()->sync($designIds);
                $product->materials()->sync($materialIds);
                $product->characteristics()->sync($characteristicIds);

                // Create product_media record using the same uploaded image
                if ($request->hasFile('image') && isset($data['image'])) {
                    // Generate thumbnail
                    $thumbnailPath = $this->generateThumbnail($data['image']);

                    $image = $request->file('image');
                    $product->media()->create([
                        'file_path' => $data['image'],
                        'file_name' => $image->getClientOriginalName(),
                        'thumbnail' => $thumbnailPath,
                        'file_type' => $image->getClientOriginalExtension(),
                        'mime_type' => $image->getMimeType(),
                        'file_size' => $image->getSize(),
                        'sort_order' => 1,
                        'is_primary' => true,
                        'alt_text' => $product->name,
                        'status' => 'active'
                    ]);
                }
            }

            if ($request->ajax()) {
                $loadRelations = $hasVariants
                    ? ['design', 'material', 'transparency', 'characteristic', 'usage', 'variants.color', 'variants.media']
                    : ['color', 'design', 'material', 'transparency', 'characteristic', 'usage', 'media'];

                return response()->json([
                    'success' => true,
                    'message' => 'Product created successfully.',
                    'data' => $product->load($loadRelations)
                ], 201);
            }

            return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the product.',
                    'error' => $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // Load appropriate relationships based on product type
        $isVariantProduct = $product->variants()->count() > 0;

        if ($isVariantProduct) {
            $product->load(['design', 'material', 'transparency', 'characteristic', 'usage', 'designs', 'materials', 'characteristics', 'variants.color', 'variants.media', 'media']);
        } else {
            $product->load(['color', 'design', 'material', 'transparency', 'characteristic', 'usage', 'designs', 'materials', 'characteristics', 'media']);
        }

        if (request()->ajax()) {
            return response()->json($product);
        }

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        // Load appropriate relationships based on product type
        $isVariantProduct = $product->variants()->count() > 0;

        if ($isVariantProduct) {
            $product->load(['design', 'material', 'transparency', 'characteristic', 'usage', 'designs', 'materials', 'characteristics', 'variants.color', 'variants.media']);
        } else {
            $product->load(['color', 'design', 'material', 'transparency', 'characteristic', 'usage', 'designs', 'materials', 'characteristics', 'media']);
        }

        // Get related data for forms
        $colors = Color::where('status', 'active')->orderBy('name', 'asc')->get();
        $designs = Design::where('status', 'active')->orderBy('name', 'asc')->get();
        $materials = Material::where('status', 'active')->orderBy('name', 'asc')->get();
        $transparencies = Transparency::where('status', 'active')->orderBy('name', 'asc')->get();
        $characteristics = Characteristic::where('status', 'active')->orderBy('name', 'asc')->get();
        $usage = Usage::where('status', 'active')->orderBy('name', 'asc')->get();

        if (request()->ajax()) {
            return response()->json([
                'product' => $product,
                'colors' => $colors,
                'designs' => $designs,
                'materials' => $materials,
                'transparencies' => $transparencies,
                'characteristics' => $characteristics,
                'usage' => $usage,
                'is_variant_product' => $isVariantProduct
            ]);
        }

        return view('admin.products.edit', compact('product', 'colors', 'designs', 'materials', 'transparencies', 'characteristics', 'usage', 'isVariantProduct'));
    }

    /**
     * Show the edit modal for the specified resource.
     */
    public function editModal(Product $product)
    {
        // Load appropriate relationships based on product type
        $isVariantProduct = $product->variants()->count() > 0;

        if ($isVariantProduct) {
            $product->load(['design', 'material', 'transparency', 'characteristic', 'usage', 'designs', 'materials', 'characteristics', 'variants.color', 'variants.media']);
        } else {
            $product->load(['color', 'design', 'material', 'transparency', 'characteristic', 'usage', 'designs', 'materials', 'characteristics', 'media']);
        }

        // Get related data for forms
        $colors = Color::where('status', 'active')->orderBy('name', 'asc')->get();
        $designs = Design::where('status', 'active')->orderBy('name', 'asc')->get();
        $materials = Material::where('status', 'active')->orderBy('name', 'asc')->get();
        $transparencies = Transparency::where('status', 'active')->orderBy('name', 'asc')->get();
        $characteristics = Characteristic::where('status', 'active')->orderBy('name', 'asc')->get();
        $usage = Usage::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('admin.products.edit-modal', compact('product', 'colors', 'designs', 'materials', 'transparencies', 'characteristics', 'usage', 'isVariantProduct'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        try {
            // Normalize variants: remove placeholders like variants[keep_*] used to preserve existing variants
            if ($request->has('variants') && is_array($request->variants)) {
                $normalizedVariants = [];
                foreach ($request->variants as $key => $variantData) {
                    if (is_string($key) && \Illuminate\Support\Str::startsWith($key, 'keep_')) {
                        continue; // skip preservation markers
                    }
                    // Skip empty variant entries
                    if (is_array($variantData) && !empty($variantData)) {
                        $normalizedVariants[] = $variantData;
                    }
                }
                $request->merge(['variants' => $normalizedVariants]);
            }
            // Check if this is a variant-based product or legacy product
            $hasVariants = $request->has('variants') && is_array($request->variants) && count($request->variants) > 0;
            $isLegacyProduct = $product->color_id !== null;

            if ($hasVariants) {
                // Update with variants
                // Image is always nullable on update - user may not want to change it

                // Build dynamic validation rules for variants
                $variantRules = [
                    'variants' => 'sometimes|array',
                    'variants.*.id' => 'nullable|exists:product_variants,id',
                ];

                // For each variant, make fields required only if it's a new variant (no id)
                foreach ($request->input('variants', []) as $index => $variant) {
                    if (isset($variant['id']) && !empty($variant['id'])) {
                        // Existing variant - fields are optional
                        $variantRules["variants.{$index}.color_id"] = 'nullable|exists:colors,id';
                        $variantRules["variants.{$index}.article"] = 'nullable|string|max:255';
                        $variantRules["variants.{$index}.article_description"] = 'nullable|string';
                    } else {
                        // New variant - fields are required
                        $variantRules["variants.{$index}.color_id"] = 'required|exists:colors,id';
                        $variantRules["variants.{$index}.article"] = 'required|string|max:255';
                        $variantRules["variants.{$index}.article_description"] = 'required|string';
                    }
                }

                $request->validate(array_merge([
                    'name' => 'required|string|max:255',
                    'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                    'description' => 'required|string',
                    'manual_product_id' => 'required|string|max:255|unique:products,manual_product_id,' . $product->id,
                    'dimensions' => 'sometimes|required|string|max:255',
                    'weight' => 'sometimes|required|string|max:255',
                    'horizontal_repeat' => 'nullable|string|max:255',
                    'vertical_repeat' => 'nullable|string|max:255',
                    'direction' => 'nullable|string|max:255',
                    'extra_data' => 'nullable|string',
                    'design_id' => 'sometimes|required|exists:designs,id',
                    'material_id' => 'sometimes|required|exists:materials,id',
                    'transparency_id' => 'nullable|exists:transparency,id',
                    'characteristic_id' => 'sometimes|required|exists:characteristics,id',
                    'usage_id' => 'sometimes|required|exists:usage,id',
                    'type' => 'required|in:product_listing,view_qualities,upholstery,latest',
                    'product_base' => 'required|in:curtain,upholstery',
                    'status' => 'sometimes|in:active,inactive,draft',
                    'featured' => 'sometimes|boolean',
                    'meta_title' => 'nullable|string|max:255',
                    'meta_description' => 'nullable|string',
                    'designs' => 'nullable|string',
                    'compositions' => 'nullable|string',
                    'hsn_code' => 'nullable|string|max:255',
                    'gst' => 'nullable|numeric|min:0|max:100',
                    'variants.*.price' => 'nullable|numeric|min:0',
                    'variants.*.sale_price' => 'nullable|numeric|min:0',
                    'variants.*.stock_quantity' => 'nullable|integer|min:0',
                    'variants.*.sku' => 'nullable|string',
                    'variants.*.is_default' => 'boolean',
                    'variants.*.status' => 'sometimes|in:active,inactive,draft',
                    'variants.*.images' => 'nullable|array',
                    'variants.*.images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240'
                ], $variantRules));

                $data = $request->all();

                // Extract relationship IDs before removing from data
                $designIds = $request->input('design_ids');
                $materialIds = $request->input('material_ids');
                $characteristicIds = $request->input('characteristic_ids');

                // Remove variants and relationship arrays from product data
                unset($data['variants'], $data['design_ids'], $data['material_ids'], $data['characteristic_ids']);

                // Remove image from data if no file is uploaded (to preserve existing image)
                if (!$request->hasFile('image')) {
                    unset($data['image']);
                }

                if ($request->has('name')) {
                    $data['slug'] = $this->createUniqueSlug($request->name, $product->id);
                }

                // Handle product image upload
                if ($request->hasFile('image')) {
                    // Delete old image if exists
                    if ($product->image) {
                        $this->deleteProductImage($product->image);
                    }
                    $data['image'] = $this->uploadProductImage($request->file('image'));
                }

                $product->update($data);

                // Sync many-to-many relationships if provided
                if ($designIds !== null) {
                    $product->designs()->sync($designIds);
                }
                if ($materialIds !== null) {
                    $product->materials()->sync($materialIds);
                }
                if ($characteristicIds !== null) {
                    $product->characteristics()->sync($characteristicIds);
                }

                // Update variants if provided
                if ($request->has('variants')) {
                    $this->updateProductVariants($product, $request->variants);
                }
            } else {
                // Legacy product update
                // Image is always nullable on update - user may not want to change it
                $request->validate([
                    'name' => 'required|string|max:255',
                    'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                    'description' => 'required|string',
                    'manual_product_id' => 'required|string|max:255|unique:products,manual_product_id,' . $product->id,
                    'price' => 'nullable|numeric|min:0',
                    'sale_price' => 'nullable|numeric|min:0',
                    'stock_quantity' => 'nullable|integer|min:0',
                    'dimensions' => 'sometimes|required|string|max:255',
                    'weight' => 'sometimes|required|string|max:255',
                    'horizontal_repeat' => 'nullable|string|max:255',
                    'vertical_repeat' => 'nullable|string|max:255',
                    'direction' => 'nullable|string|max:255',
                    'extra_data' => 'nullable|string',
                    'color_id' => 'sometimes|required|exists:colors,id',
                    'design_ids' => 'sometimes|required|array|min:1',
                    'design_ids.*' => 'exists:designs,id',
                    'material_ids' => 'sometimes|required|array|min:1',
                    'material_ids.*' => 'exists:materials,id',
                    'transparency_id' => 'nullable|exists:transparency,id',
                    'characteristic_ids' => 'sometimes|required|array|min:1',
                    'characteristic_ids.*' => 'exists:characteristics,id',
                    'usage_id' => 'sometimes|required|exists:usage,id',
                    'type' => 'required|in:product_listing,view_qualities,upholstery,latest',
                    'product_base' => 'required|in:curtain,upholstery',
                    'status' => 'sometimes|in:active,inactive,draft',
                    'featured' => 'sometimes|boolean',
                    'in_stock' => 'sometimes|boolean',
                    'meta_title' => 'nullable|string|max:255',
                    'meta_description' => 'nullable|string',
                    'designs' => 'nullable|string',
                    'compositions' => 'nullable|string',
                    'hsn_code' => 'nullable|string|max:255',
                    'gst' => 'nullable|numeric|min:0|max:100',
                ]);

                $data = $request->all();

                // Extract relationship IDs before removing from data
                $designIds = $request->input('design_ids');
                $materialIds = $request->input('material_ids');
                $characteristicIds = $request->input('characteristic_ids');

                // Remove SKU and relationship arrays from update data to prevent changes for legacy products
                unset($data['sku'], $data['design_ids'], $data['material_ids'], $data['characteristic_ids']);

                // Remove image from data if no file is uploaded (to preserve existing image)
                if (!$request->hasFile('image')) {
                    unset($data['image']);
                }

                if ($request->has('name')) {
                    $data['slug'] = $this->createUniqueSlug($request->name, $product->id);
                }

                // Handle product image upload
                if ($request->hasFile('image')) {
                    // Delete old image if exists
                    if ($product->image) {
                        $this->deleteProductImage($product->image);
                    }
                    $data['image'] = $this->uploadProductImage($request->file('image'));
                }

                $product->update($data);

                // Sync many-to-many relationships if provided
                if ($designIds !== null) {
                    $product->designs()->sync($designIds);
                }
                if ($materialIds !== null) {
                    $product->materials()->sync($materialIds);
                }
                if ($characteristicIds !== null) {
                    $product->characteristics()->sync($characteristicIds);
                }

                // Update product_media record using the same uploaded image
                if ($request->hasFile('image') && isset($data['image'])) {
                    // Delete existing media records
                    $existingMedia = $product->media;
                    foreach ($existingMedia as $media) {
                        // Don't delete the file if it's the same as the new one
                        if ($media->file_path !== $data['image']) {
                            $fullPath = public_path($media->file_path);
                            if (file_exists($fullPath)) {
                                unlink($fullPath);
                            }
                            // Delete thumbnail if exists
                            if ($media->thumbnail) {
                                $thumbnailPath = public_path($media->thumbnail);
                                if (file_exists($thumbnailPath)) {
                                    unlink($thumbnailPath);
                                }
                            }
                        }
                        $media->delete();
                    }

                    // Generate thumbnail
                    $thumbnailPath = $this->generateThumbnail($data['image']);

                    // Create new media record
                    $image = $request->file('image');
                    $product->media()->create([
                        'file_path' => $data['image'],
                        'file_name' => $image->getClientOriginalName(),
                        'thumbnail' => $thumbnailPath,
                        'file_type' => $image->getClientOriginalExtension(),
                        'mime_type' => $image->getMimeType(),
                        'file_size' => $image->getSize(),
                        'sort_order' => 1,
                        'is_primary' => true,
                        'alt_text' => $product->name,
                        'status' => 'active'
                    ]);
                }

                // Handle image deletions for legacy products
                if ($request->has('delete_media_ids')) {
                    $this->handleImageDeletions($request->input('delete_media_ids'));
                }

                // Handle primary image update for legacy products
                if ($request->has('primary_media_id')) {
                    $this->setPrimaryImage($product, $request->input('primary_media_id'));
                }
            }

            if ($request->ajax()) {
                $loadRelations = $hasVariants || $product->variants()->count() > 0
                    ? ['design', 'material', 'transparency', 'characteristic', 'usage', 'variants.color', 'variants.media', 'media']
                    : ['color', 'design', 'material', 'transparency', 'characteristic', 'usage', 'media'];

                return response()->json([
                    'success' => true,
                    'message' => 'Product updated successfully.',
                    'data' => $product->load($loadRelations)
                ]);
            }

            return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the product.',
                    'error' => $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product deleted successfully.'
                ]);
            }

            return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the product.'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Get all related data for creating/editing products
     */
    public function getRelatedData(): JsonResponse
    {
        return response()->json([
            'colors' => Color::where('status', 'active')->orderBy('name', 'asc')->get(),
            'designs' => Design::where('status', 'active')->orderBy('name', 'asc')->get(),
            'materials' => Material::where('status', 'active')->orderBy('name', 'asc')->get(),
            'transparencies' => Transparency::where('status', 'active')->orderBy('name', 'asc')->get(),
            'characteristics' => Characteristic::where('status', 'active')->orderBy('name', 'asc')->get(),
            'usage' => Usage::where('status', 'active')->orderBy('name', 'asc')->get(),
        ]);
    }

    /**
     * Add a new variant to an existing product
     */
    public function addVariant(Request $request, Product $product): JsonResponse
    {
        try {
            $request->validate([
                'color_id' => 'required|exists:colors,id',
                'price' => 'required|numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0',
                'stock_quantity' => 'nullable|integer|min:0',
                'sku' => 'nullable|string',
                'is_default' => 'boolean',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240'
            ]);

            $variantData = $request->all();

            // Auto-generate SKU if not provided
            if (empty($variantData['sku'])) {
                $variantData['sku'] = $this->createVariantSku($product->name, $variantData['color_id']);
            }

            // Set sort order
            $variantData['sort_order'] = $product->variants()->max('sort_order') + 1;

            // If this is the first variant or marked as default, make it default
            if ($product->variants()->count() === 0 || $variantData['is_default']) {
                $variantData['is_default'] = true;
                // Remove default from other variants
                $product->variants()->update(['is_default' => false]);
            } else {
                $variantData['is_default'] = false;
            }

            $variant = $product->variants()->create($variantData);

            // Handle variant images
            if ($request->hasFile('images')) {
                $this->handleVariantImageUploads($request->file('images'), $variant);
            }

            return response()->json([
                'success' => true,
                'message' => 'Variant added successfully.',
                'data' => $variant->load(['color', 'media'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding the variant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a specific variant
     */
    public function updateVariant(Request $request, ProductVariant $variant): JsonResponse
    {
        try {
            $request->validate([
                'article' => 'required|string|max:255',
                'article_description' => 'required|string',
                'price' => 'sometimes|required|numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0',
                'stock_quantity' => 'nullable|integer|min:0',
                'sku' => 'nullable|string',
                'is_default' => 'boolean',
                'status' => 'sometimes|in:active,inactive,draft',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240'
            ]);

            $data = $request->all();

            // If setting as default, remove default from other variants
            if (isset($data['is_default']) && $data['is_default']) {
                $variant->product->variants()->where('id', '!=', $variant->id)->update(['is_default' => false]);
            }

            $variant->update($data);

            // Handle variant images - delete old ones before uploading new ones
            if ($request->hasFile('images')) {
                $this->deleteVariantImages($variant);
                $this->handleVariantImageUploads($request->file('images'), $variant);
            }

            return response()->json([
                'success' => true,
                'message' => 'Variant updated successfully.',
                'data' => $variant->load(['color', 'media'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the variant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a specific variant
     */
    public function deleteVariant(ProductVariant $variant): JsonResponse
    {
        try {
            $product = $variant->product;

            // If this was the default variant, make another one default
            if ($variant->is_default) {
                $newDefault = $product->variants()->where('id', '!=', $variant->id)->first();
                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }

            $variant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Variant deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the variant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate SKU based on product name
     */
    public function generateSku(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $name = $request->input('name');
        $sku = $this->createSkuFromName($name);

        return response()->json([
            'success' => true,
            'sku' => $sku
        ]);
    }

    /**
     * Create SKU from product name
     */
    private function createSkuFromName(string $name): string
    {
        // Remove special characters and convert to uppercase
        $sku = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);
        $sku = strtoupper($sku);

        // Replace spaces with hyphens
        $sku = preg_replace('/\s+/', '-', $sku);

        // Remove multiple consecutive hyphens
        $sku = preg_replace('/-+/', '-', $sku);

        // Remove leading/trailing hyphens
        $sku = trim($sku, '-');

        // Limit to 20 characters for readability
        if (strlen($sku) > 20) {
            $sku = substr($sku, 0, 20);
            $sku = rtrim($sku, '-');
        }

        // Add random digits and timestamp for uniqueness
        // Format: PRODUCT-NAME-XXXX-YYYYMMDD (where XXXX is random 4-digit number)
        $randomDigits = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $timestamp = date('Ymd');
        $sku = $sku . '-' . $randomDigits . '-' . $timestamp;

        // Check if SKU already exists and add counter if needed
        $originalSku = $sku;
        $counter = 1;

        while (Product::where('sku', $sku)->exists()) {
            $sku = $originalSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }

    /**
     * Create unique slug from product name
     * 
     * @param string $name The product name
     * @param int|null $excludeId Product ID to exclude from uniqueness check (for updates)
     * @return string A unique slug
     */
    private function createUniqueSlug(string $name, ?int $excludeId = null): string
    {
        // Generate base slug from name
        $slug = Str::slug($name);

        // If slug is empty (e.g., name contains only special characters), use a default
        if (empty($slug)) {
            $slug = 'product-' . time();
        }

        // If slug exists, append a counter until we find a unique one
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $query = Product::where('slug', $slug);
            if ($excludeId !== null) {
                $query->where('id', '!=', $excludeId);
            }

            if (!$query->exists()) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Show product media management page
     */
    public function media(Product $product)
    {
        $media = $product->media()->orderBy('sort_order')->get();
        return view('admin.products.media.index', compact('product', 'media'));
    }

    /**
     * Handle multiple image uploads
     */
    private function handleImageUploads(array $images, Product $product)
    {
        $sortOrder = $product->media()->max('sort_order') ?? 0;
        $isFirstImage = $product->media()->count() === 0;

        foreach ($images as $image) {
            $sortOrder++;

            // Generate unique filename
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            // Store image in public folder
            $publicPath = public_path('products');
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0755, true);
            }

            // Use copy instead of move to avoid temporary file issues
            $destinationPath = $publicPath . '/' . $filename;
            copy($image->getRealPath(), $destinationPath);
            $path = 'products/' . $filename;

            // Generate thumbnail
            $thumbnailPath = $this->generateThumbnail($path);

            // Create media record
            $media = $product->media()->create([
                'file_path' => $path,
                'file_name' => $image->getClientOriginalName(),
                'thumbnail' => $thumbnailPath,
                'file_type' => $image->getClientOriginalExtension(),
                'mime_type' => $image->getMimeType(),
                'file_size' => $image->getSize(),
                'sort_order' => $sortOrder,
                'is_primary' => $isFirstImage,
                'alt_text' => $product->name,
                'status' => 'active'
            ]);

            $isFirstImage = false; // Only first image is primary
        }
    }

    /**
     * Handle single image upload
     */
    private function handleSingleImageUpload($image, Product $product)
    {
        // Delete existing images first
        $existingMedia = $product->media;
        foreach ($existingMedia as $media) {
            // Delete file from public folder
            $fullPath = public_path($media->file_path);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            // Delete thumbnail if exists
            if ($media->thumbnail) {
                $thumbnailPath = public_path($media->thumbnail);
                if (file_exists($thumbnailPath)) {
                    unlink($thumbnailPath);
                }
            }
            // Delete media record
            $media->delete();
        }

        // Generate unique filename
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

        // Store image in public folder
        $publicPath = public_path('products');
        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0755, true);
        }

        // Use copy instead of move to avoid temporary file issues
        $destinationPath = $publicPath . '/' . $filename;
        copy($image->getRealPath(), $destinationPath);
        $path = 'products/' . $filename;

        // Generate thumbnail
        $thumbnailPath = $this->generateThumbnail($path);

        // Create media record
        $product->media()->create([
            'file_path' => $path,
            'file_name' => $image->getClientOriginalName(),
            'thumbnail' => $thumbnailPath,
            'file_type' => $image->getClientOriginalExtension(),
            'mime_type' => $image->getMimeType(),
            'file_size' => $image->getSize(),
            'sort_order' => 1,
            'is_primary' => true,
            'alt_text' => $product->name,
            'status' => 'active'
        ]);
    }

    /**
     * Handle image deletions
     */
    private function handleImageDeletions(array $mediaIds)
    {
        foreach ($mediaIds as $mediaId) {
            $media = ProductMedia::find($mediaId);
            if ($media) {
                // Delete file from public folder
                $fullPath = public_path($media->file_path);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }

                // Delete thumbnail if exists
                if ($media->thumbnail) {
                    $thumbnailPath = public_path($media->thumbnail);
                    if (file_exists($thumbnailPath)) {
                        unlink($thumbnailPath);
                    }
                }

                // Delete media record
                $media->delete();
            }
        }
    }

    /**
     * Apply custom filters to the query
     */
    private function applyCustomFilters($query)
    {
        // Search filter
        if (request()->has('search_filter') && !empty(request('search_filter'))) {
            $searchValue = request('search_filter');
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('sku', 'like', "%{$searchValue}%")
                    ->orWhere('description', 'like', "%{$searchValue}%");
            });
        }

        // Color filter
        if (request()->has('color_filter') && !empty(request('color_filter'))) {
            $query->where('color_id', request('color_filter'));
        }

        // Material filter
        if (request()->has('material_filter') && !empty(request('material_filter'))) {
            $query->where('material_id', request('material_filter'));
        }

        // Design filter
        if (request()->has('design_filter') && !empty(request('design_filter'))) {
            $query->where('design_id', request('design_filter'));
        }

        // Product type filter
        if (request()->has('type_filter') && !empty(request('type_filter'))) {
            $query->where('type', request('type_filter'));
        }

        // Product base filter
        if (request()->has('product_base_filter') && !empty(request('product_base_filter'))) {
            $query->where('product_base', request('product_base_filter'));
        }
    }

    /**
     * Set primary image
     */
    private function setPrimaryImage(Product $product, $mediaId)
    {
        // Remove primary from all images
        $product->media()->update(['is_primary' => false]);

        // Set new primary
        $media = ProductMedia::find($mediaId);
        if ($media && $media->product_id === $product->id) {
            $media->update(['is_primary' => true]);
        }
    }

    /**
     * Create product variants
     */
    private function createProductVariants(Product $product, array $variants)
    {
        $sortOrder = 0;
        $hasDefault = false;

        foreach ($variants as $variantData) {
            $sortOrder++;

            // Auto-generate SKU if not provided
            if (empty($variantData['sku'])) {
                $variantData['sku'] = $this->createVariantSku($product->name, $variantData['color_id']);
            }

            // Ensure only one default variant
            if (isset($variantData['is_default']) && $variantData['is_default'] && !$hasDefault) {
                $hasDefault = true;
            } else {
                $variantData['is_default'] = false;
            }

            $variantData['sort_order'] = $sortOrder;
            $variant = $product->variants()->create($variantData);

            // Handle variant images
            if (isset($variantData['images']) && is_array($variantData['images'])) {
                $this->handleVariantImageUploads($variantData['images'], $variant);
            }
        }

        // If no default variant was set, make the first one default
        if (!$hasDefault && $product->variants()->count() > 0) {
            $product->variants()->first()->update(['is_default' => true]);
        }
    }

    /**
     * Update product variants
     */
    private function updateProductVariants(Product $product, array $variants)
    {
        $existingVariantIds = [];
        $sortOrder = 0;
        $hasDefault = false;

        foreach ($variants as $variantData) {
            // Skip if variant data is empty or invalid
            if (!is_array($variantData) || empty($variantData)) {
                continue;
            }

            $sortOrder++;
            $variantData['sort_order'] = $sortOrder;

            $variant = null;

            if (isset($variantData['id']) && $variantData['id']) {
                // Update existing variant
                $variant = ProductVariant::find($variantData['id']);
                if ($variant && $variant->product_id === $product->id) {
                    unset($variantData['id']);
                    // Only update fields that are provided (exclude null and empty strings)
                    $updateData = array_filter($variantData, function ($value) {
                        return $value !== null && $value !== '';
                    });
                    $variant->update($updateData);
                    $existingVariantIds[] = $variant->id;
                } else {
                    continue; // Skip if variant not found or doesn't belong to product
                }
            } else {
                // Create new variant - color_id is required for new variants
                if (!isset($variantData['color_id']) || empty($variantData['color_id'])) {
                    continue; // Skip if color_id is missing for new variant
                }

                if (empty($variantData['sku'])) {
                    $variantData['sku'] = $this->createVariantSku($product->name, $variantData['color_id']);
                }
                $variant = $product->variants()->create($variantData);
                $existingVariantIds[] = $variant->id;
            }

            // Ensure variant was created/updated successfully before proceeding
            if (!$variant) {
                continue;
            }

            // Ensure only one default variant
            if (isset($variantData['is_default']) && $variantData['is_default'] && !$hasDefault) {
                $hasDefault = true;
                // Remove default from other variants
                $product->variants()->where('id', '!=', $variant->id)->update(['is_default' => false]);
            }

            // Handle variant images - check if images are being uploaded
            if (isset($variantData['images']) && is_array($variantData['images']) && !empty($variantData['images'])) {
                // Check if these are file uploads (UploadedFile objects)
                $hasFileUploads = false;
                foreach ($variantData['images'] as $image) {
                    if ($image instanceof \Illuminate\Http\UploadedFile) {
                        $hasFileUploads = true;
                        break;
                    }
                }

                if ($hasFileUploads) {
                    // Delete old images before uploading new ones
                    $this->deleteVariantImages($variant);
                    // Upload new images
                    $this->handleVariantImageUploads($variantData['images'], $variant);
                }
            }
        }

        // If no default variant was set, make the first one default
        if (!$hasDefault && $product->variants()->count() > 0) {
            $firstVariant = $product->variants()->orderBy('sort_order')->first();
            if ($firstVariant) {
                $firstVariant->update(['is_default' => true]);
            }
        }

        // Do not delete variants that were not part of this update payload; preserve existing ones
    }

    /**
     * Delete variant images and thumbnails
     */
    private function deleteVariantImages(ProductVariant $variant): void
    {
        $existingMedia = $variant->media;
        foreach ($existingMedia as $media) {
            // Delete file from public folder
            $fullPath = public_path($media->file_path);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            // Delete thumbnail if exists
            if ($media->thumbnail) {
                $thumbnailPath = public_path($media->thumbnail);
                if (file_exists($thumbnailPath)) {
                    unlink($thumbnailPath);
                }
            }
            // Delete media record
            $media->delete();
        }
    }

    /**
     * Handle variant image uploads
     */
    private function handleVariantImageUploads(array $images, ProductVariant $variant)
    {
        $sortOrder = $variant->media()->max('sort_order') ?? 0;
        $isFirstImage = $variant->media()->count() === 0;

        foreach ($images as $image) {
            $sortOrder++;

            // Generate unique filename
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            // Store image in public folder
            $publicPath = public_path('products');
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0755, true);
            }

            $destinationPath = $publicPath . '/' . $filename;
            copy($image->getRealPath(), $destinationPath);
            $path = 'products/' . $filename;

            // Generate thumbnail
            $thumbnailPath = $this->generateThumbnail($path);

            // Create media record
            $variant->media()->create([
                'product_id' => $variant->product_id,
                'file_path' => $path,
                'file_name' => $image->getClientOriginalName(),
                'thumbnail' => $thumbnailPath,
                'file_type' => $image->getClientOriginalExtension(),
                'mime_type' => $image->getMimeType(),
                'file_size' => $image->getSize(),
                'sort_order' => $sortOrder,
                'is_primary' => $isFirstImage,
                'alt_text' => $variant->product->name . ' - ' . $variant->color->name,
                'status' => 'active'
            ]);

            $isFirstImage = false;
        }
    }

    /**
     * Create variant SKU
     */
    private function createVariantSku(string $productName, int $colorId): string
    {
        $color = Color::find($colorId);
        $colorName = $color ? $color->name : 'UNKNOWN';

        // Create base SKU from product name
        $baseSku = preg_replace('/[^a-zA-Z0-9\s]/', '', $productName);
        $baseSku = strtoupper($baseSku);
        $baseSku = preg_replace('/\s+/', '-', $baseSku);
        $baseSku = preg_replace('/-+/', '-', $baseSku);
        $baseSku = trim($baseSku, '-');

        // Add color
        $colorSku = preg_replace('/[^a-zA-Z0-9\s]/', '', $colorName);
        $colorSku = strtoupper($colorSku);
        $colorSku = preg_replace('/\s+/', '-', $colorSku);
        $colorSku = preg_replace('/-+/', '-', $colorSku);
        $colorSku = trim($colorSku, '-');

        $sku = $baseSku . '-' . $colorSku;

        // Limit length
        if (strlen($sku) > 30) {
            $sku = substr($sku, 0, 30);
            $sku = rtrim($sku, '-');
        }

        // Add random digits and timestamp for uniqueness
        $randomDigits = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $timestamp = date('Ymd');
        $sku = $sku . '-' . $randomDigits . '-' . $timestamp;

        // Check if SKU already exists and add counter if needed
        $originalSku = $sku;
        $counter = 1;

        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = $originalSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }

    /**
     * Upload product image and return the path
     */
    private function uploadProductImage($image): string
    {
        // Generate unique filename
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

        // Store image in public folder
        $publicPath = public_path('products');
        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0755, true);
        }

        // Use copy instead of move to avoid temporary file issues
        $destinationPath = $publicPath . '/' . $filename;
        copy($image->getRealPath(), $destinationPath);

        // Return relative path for database storage
        return 'products/' . $filename;
    }

    /**
     * Delete product image file
     */
    private function deleteProductImage(string $imagePath): void
    {
        $fullPath = public_path($imagePath);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    /**
     * Generate thumbnail from image file
     */
    private function generateThumbnail(string $imagePath, int $maxWidth = 300, int $maxHeight = 300): ?string
    {
        $fullImagePath = public_path($imagePath);

        if (!file_exists($fullImagePath)) {
            return null;
        }

        // Get image info
        $imageInfo = getimagesize($fullImagePath);
        if (!$imageInfo) {
            return null;
        }

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Calculate thumbnail dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $thumbWidth = (int)($originalWidth * $ratio);
        $thumbHeight = (int)($originalHeight * $ratio);

        // Create image resource based on mime type
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $sourceImage = imagecreatefromjpeg($fullImagePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($fullImagePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($fullImagePath);
                break;
            default:
                return null;
        }

        if (!$sourceImage) {
            return null;
        }

        // Create thumbnail
        $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);

        // Preserve transparency for PNG and GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefilledrectangle($thumbnail, 0, 0, $thumbWidth, $thumbHeight, $transparent);
        }

        // Resize image
        imagecopyresampled(
            $thumbnail,
            $sourceImage,
            0,
            0,
            0,
            0,
            $thumbWidth,
            $thumbHeight,
            $originalWidth,
            $originalHeight
        );

        // Generate thumbnail filename
        $pathInfo = pathinfo($imagePath);
        $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        $fullThumbnailPath = public_path($thumbnailPath);

        // Ensure directory exists
        $thumbnailDir = dirname($fullThumbnailPath);
        if (!file_exists($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        // Save thumbnail based on mime type with improved quality
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                // JPEG quality: 0-100, higher = better quality (90 for high quality thumbnails)
                imagejpeg($thumbnail, $fullThumbnailPath, 80);
                break;
            case 'image/png':
                // PNG compression: 0-9, lower = better quality (0 = no compression, best quality)
                imagepng($thumbnail, $fullThumbnailPath, 0);
                break;
            case 'image/gif':
                imagegif($thumbnail, $fullThumbnailPath);
                break;
        }

        // Free memory
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);

        return $thumbnailPath;
    }
}
