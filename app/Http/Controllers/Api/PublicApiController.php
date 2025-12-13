<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Color;
use App\Models\Design;
use App\Models\Material;
use App\Models\Transparency;
use App\Models\Characteristic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicApiController extends Controller
{
    /**
     * Get products with search and filtering
     */
    public function getProducts(Request $request): JsonResponse
    {
        try {
            $query = $this->buildProductQuery($request);
            $products = $query->paginate(min($request->get('per_page', 12), 50));

            return response()->json([
                'success' => true,
                'data' => $products->getCollection()->map(function ($p) {
                    return $this->transformProduct($p, false);
                }),
                'pagination' => $this->getPaginationData($products),
                // 'filters_applied' => $request->only(['search', 'color_id', 'design_id', 'material_id', 'transparency_id', 'characteristic_id', 'min_price', 'max_price', 'in_stock', 'featured', 'sort_by', 'sort_order'])
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error fetching products', $e->getMessage());
        }
    }

    /**
     * Get a single product by ID
     */
    public function getProduct($id): JsonResponse
    {
        try {
            $product = Product::with(['color', 'design', 'material', 'transparency', 'characteristic', 'usage', 'media', 'variants.color', 'variants.media'])
                ->where('status', 'active')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $this->transformProduct($product, true)
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Product not found', $e->getMessage(), 404);
        }
    }

    /**
     * Get all filter options
     */
    public function getFilterOptions(): JsonResponse
    {
        try {
            $models = [
                'colors' => Color::class,
                'designs' => Design::class,
                'materials' => Material::class,
                'transparencies' => Transparency::class,
                'characteristics' => Characteristic::class
            ];

            $filters = [];
            foreach ($models as $key => $model) {
                $filters[$key] = $model::where('status', 'active')
                    ->select($key === 'colors' ? ['id', 'name', 'color_value'] : ['id', 'name'])
                    ->orderBy('name')
                    ->get();
            }

            $legacyRange = Product::where('status', 'active')
                ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
                ->first();
            $variantRange = ProductVariant::whereHas('product', function ($q) {
                $q->where('status', 'active');
            })
                ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
                ->first();

            $minCandidates = [];
            if ($legacyRange && $legacyRange->min_price !== null) $minCandidates[] = $legacyRange->min_price;
            if ($variantRange && $variantRange->min_price !== null) $minCandidates[] = $variantRange->min_price;
            $maxCandidates = [];
            if ($legacyRange && $legacyRange->max_price !== null) $maxCandidates[] = $legacyRange->max_price;
            if ($variantRange && $variantRange->max_price !== null) $maxCandidates[] = $variantRange->max_price;

            $filters['price_range'] = [
                'min' => number_format(!empty($minCandidates) ? min($minCandidates) : 0, 2),
                'max' => number_format(!empty($maxCandidates) ? max($maxCandidates) : 0, 2)
            ];

            return response()->json(['success' => true, 'data' => $filters]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error fetching filter options', $e->getMessage());
        }
    }

    /**
     * Get featured products
     */
    public function getFeaturedProducts(Request $request): JsonResponse
    {
        try {
            $products = Product::with(['color', 'design', 'material', 'transparency', 'characteristic', 'usage', 'media', 'variants.color', 'variants.media'])
                ->where('status', 'active')
                ->where('featured', true)
                ->orderBy('created_at', 'desc')
                ->limit(min($request->get('limit', 8), 20))
                ->get();

            return response()->json([
                'success' => true,
                'data' => $products->map(function ($p) {
                    return $this->transformProduct($p, false);
                })
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error fetching featured products', $e->getMessage());
        }
    }

    /**
     * Get latest products from the last one year
     */
    public function getLatestProducts(Request $request): JsonResponse
    {
        try {
            $oneYearAgo = now()->subYear();

            $query = Product::with(['color', 'design', 'material', 'transparency', 'characteristic', 'usage', 'media', 'variants.color', 'variants.media'])
                ->where('status', 'active')
                ->where('created_at', '>=', $oneYearAgo);

            // Type filter
            if ($type = $request->type) {
                $query->where('type', $type);
            } else {
                // If no type is specified, default to product_listing
                $query->where('type', 'product_listing');
            }

            $query->orderBy('created_at', 'desc');

            // Apply pagination if requested
            $perPage = min($request->get('per_page', 12), 50);
            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products->getCollection()->map(function ($p) {
                    return $this->transformProduct($p, false);
                }),
                'pagination' => $this->getPaginationData($products),
                'meta' => [
                    'period' => 'last_one_year',
                    'from_date' => $oneYearAgo->toISOString(),
                    'to_date' => now()->toISOString(),
                    'total_products' => $products->total()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error fetching latest products', $e->getMessage());
        }
    }

    /**
     * Get product statistics
     */
    public function getProductStats(): JsonResponse
    {
        try {
            $stats = [
                'total_products' => Product::where('status', 'active')->count(),
                'featured_products' => Product::where('status', 'active')->where('featured', true)->count(),
                'in_stock_products' => Product::where('status', 'active')->where('in_stock', true)->count(),
                'total_colors' => Color::where('status', 'active')->count(),
                'total_designs' => Design::where('status', 'active')->count(),
                'total_materials' => Material::where('status', 'active')->count()
            ];

            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error fetching product statistics', $e->getMessage());
        }
    }

    /**
     * Build optimized product query with filters
     */
    private function buildProductQuery(Request $request)
    {
        $query = Product::with(['color', 'design', 'material', 'transparency', 'characteristic', 'usage', 'media', 'variants.color', 'variants.media'])
            ->where('status', 'active');

        // Search
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhereHas('color', fn($sq) => $sq->where('name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('design', fn($sq) => $sq->where('name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('material', fn($sq) => $sq->where('name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('variants.color', fn($sq) => $sq->where('name', 'LIKE', "%{$search}%"));
            });
        }

        // Filters (support multi-select via array or comma-separated string)
        $filters = ['design_id', 'material_id', 'transparency_id', 'characteristic_id'];
        foreach ($filters as $filter) {
            $ids = $this->normalizeIds($request->input($filter));
            if (!empty($ids)) {
                $query->whereIn($filter, $ids);
            }
        }

        // Color filter - check both legacy color_id and variant colors
        if ($colorIds = $this->normalizeIds($request->input('color_id'))) {
            $query->where(function ($q) use ($colorIds) {
                $q->whereIn('color_id', $colorIds)
                    ->orWhereHas('variants', fn($sq) => $sq->whereIn('color_id', $colorIds));
            });
        }

        // Price range - check both legacy price and variant prices
        if ($minPrice = $request->min_price) {
            $query->where(function ($q) use ($minPrice) {
                $q->where('price', '>=', $minPrice)
                    ->orWhereHas('variants', fn($sq) => $sq->where('price', '>=', $minPrice));
            });
        }
        if ($maxPrice = $request->max_price) {
            $query->where(function ($q) use ($maxPrice) {
                $q->where('price', '<=', $maxPrice)
                    ->orWhereHas('variants', fn($sq) => $sq->where('price', '<=', $maxPrice));
            });
        }

        // Boolean filters
        if ($request->in_stock == '1') {
            $query->where(function ($q) {
                $q->where('in_stock', true)
                    ->orWhereHas('variants', fn($sq) => $sq->where('in_stock', true));
            });
        }
        if ($request->featured == '1') $query->where('featured', true);

        // Type filter
        if ($type = $request->type) {
            $query->where('type', $type);
        } else {
            // If no type is specified, default to product_listing
            $query->where('type', 'product_listing');
        }

        // Sorting
        $sortBy = in_array($request->sort_by, ['name', 'price', 'created_at', 'stock_quantity'])
            ? $request->sort_by : 'created_at';
        $query->orderBy($sortBy, $request->get('sort_order', 'desc'));

        return $query;
    }

    /**
     * Normalize IDs from request into an array of integers.
     * Accepts: [1,2], "1,2,3", 1
     */
    private function normalizeIds($value): array
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }

        // If already an array, use it
        if (is_array($value)) {
            $arr = $value;
        } else {
            // Split comma-separated string
            $arr = explode(',', (string) $value);
        }

        // Cast to integers and remove empties/duplicates
        $ids = array_values(array_unique(array_filter(array_map(function ($v) {
            $v = trim((string) $v);
            return $v === '' ? null : (int) $v;
        }, $arr), function ($v) {
            return $v !== null && $v !== 0; // keep non-null, non-zero IDs
        })));

        return $ids;
    }

    /**
     * Transform product data for API response
     */
    public function transformProduct($product, bool $includeVariants = false)
    {
        $isVariantProduct = $product->variants && $product->variants->count() > 0;

        $baseData = [
            'id' => $product->id,
            'manual_product_id' => $product->manual_product_id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->image ? asset($product->image) : null,
            'description' => $product->description,
            'dimensions' => $product->dimensions,
            'weight' => $product->weight,
            'horizontal_repeat' => $product->horizontal_repeat,
            'vertical_repeat' => $product->vertical_repeat,
            'direction' => $product->direction,
            'extra_data' => $product->extra_data,
            'featured' => $product->featured,
            'status' => $product->status,
            'meta_title' => $product->meta_title,
            'meta_description' => $product->meta_description,
            'color_no' => $product->color_no,
            'designs' => $product->designs,
            'compositions' => $product->compositions,
            'hsn_code' => $product->hsn_code,
            'gst' => $product->gst !== null ? (float) $product->gst : null,
            'usage_id' => $product->usage_id,
            'usage' => $product->usage ? [
                'id' => $product->usage->id,
                'name' => $product->usage->name
            ] : null,
            'created_at' => $product->created_at->toISOString(),
            'updated_at' => $product->updated_at->toISOString()
        ];

        if ($isVariantProduct) {
            // Product with variants
            $variants = $includeVariants ? $product->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'color' => [
                        'id' => $variant->color->id,
                        'name' => $variant->color->name,
                        'color_value' => $variant->color->color_value
                    ],
                    'sku' => $variant->sku,
                    'price' => $variant->price !== null ? '€' . number_format($variant->price, 2) : null,
                    'sale_price' => $variant->sale_price ? '€' . number_format($variant->sale_price, 2) : null,
                    'stock_quantity' => $variant->stock_quantity,
                    'in_stock' => (bool) $variant->in_stock,
                    'is_default' => (bool) $variant->is_default,
                    'status' => $variant->status,
                    'media' => $variant->media->map(fn($media) => [
                        'id' => $media->id,
                        'file_path' => $media->file_path ? asset($media->file_path) : null,
                        'thumbnail' => $media->thumbnail ? asset($media->thumbnail) : null,
                        'file_type' => $media->file_type,
                        'is_primary' => (bool) $media->is_primary
                    ])
                ];
            }) : collect();

            // Get price range from variants
            $prices = $product->variants->pluck('price')->filter();
            $minPrice = $prices->min();
            $maxPrice = $prices->max();
            $priceRange = $minPrice ? '€' . number_format($minPrice, 2) . ($minPrice != $maxPrice ? ' - €' . number_format($maxPrice, 2) : '') : 'N/A';

            // Get total stock from all variants
            $totalStock = $product->variants->sum('stock_quantity');
            $inStock = $product->variants->where('in_stock', true)->count() > 0;

            // Determine root-level image from product image, default variant's primary media, or fallback
            $defaultVariant = $product->variants->where('is_default', true)->first() ?: $product->variants->first();
            $variantPrimary = null;
            if ($defaultVariant) {
                $variantPrimary = $defaultVariant->media->where('is_primary', true)->first() ?: $defaultVariant->media->first();
            }
            // Priority: product image > variant primary media > product media
            $rootImage = $product->image
                ? asset($product->image)
                : ($variantPrimary
                    ? asset($variantPrimary->file_path)
                    : (optional($product->media->where('is_primary', true)->first())->file_path
                        ? asset($product->media->where('is_primary', true)->first()->file_path)
                        : (optional($product->media->first())->file_path ? asset($product->media->first()->file_path) : null)));

            // Compute a compact default_variant block for quick consumption
            $defaultVariantBlock = null;
            if ($defaultVariant) {
                $defaultPrimary = $defaultVariant->media->where('is_primary', true)->first() ?: $defaultVariant->media->first();
                $defaultVariantBlock = [
                    'id' => $defaultVariant->id,
                    'sku' => $defaultVariant->sku,
                    'price' => $defaultVariant->price !== null ? '€' . number_format($defaultVariant->price, 2) : null,
                    'sale_price' => $defaultVariant->sale_price !== null ? '€' . number_format($defaultVariant->sale_price, 2) : null,
                    'stock_quantity' => $defaultVariant->stock_quantity,
                    'in_stock' => (bool) $defaultVariant->in_stock,
                    'color' => $defaultVariant->color ? [
                        'id' => $defaultVariant->color->id,
                        'name' => $defaultVariant->color->name,
                        'color_value' => $defaultVariant->color->color_value
                    ] : null,
                    'image' => $defaultPrimary ? asset($defaultPrimary->file_path) : null,
                    'thumbnail' => $defaultPrimary && $defaultPrimary->thumbnail ? asset($defaultPrimary->thumbnail) : null
                ];
            }

            $base = array_merge($baseData, [
                'type' => 'variant_product',
                'variants' => $includeVariants ? $variants : null,
                'variant_count' => $product->variants->count(),
                'price_range' => $priceRange,
                'min_price' => $minPrice ? '€' . number_format($minPrice, 2) : null,
                'max_price' => $maxPrice ? '€' . number_format($maxPrice, 2) : null,
                'total_stock' => $totalStock,
                'in_stock' => $inStock,
                'image' => $rootImage,
                'default_variant' => $defaultVariantBlock,
                'media' => $product->media->map(fn($media) => [
                    'id' => $media->id,
                    'file_path' => $media->file_path ? asset($media->file_path) : null,
                    'thumbnail' => $media->thumbnail ? asset($media->thumbnail) : null,
                    'file_type' => $media->file_type,
                    'is_primary' => $media->is_primary
                ])
            ]);

            // If variants are excluded, remove the key to avoid nulls in listing
            if (!$includeVariants) {
                unset($base['variants']);
            }
            return $base;
        } else {
            // Legacy product with single color
            return array_merge($baseData, [
                'type' => 'single_product',
                'sku' => $product->sku,
                'price' => $product->price ? '€' . number_format($product->price, 2) : null,
                'sale_price' => $product->sale_price ? '€' . number_format($product->sale_price, 2) : null,
                'stock_quantity' => $product->stock_quantity,
                'in_stock' => $product->in_stock,
                'color' => $product->color ? [
                    'id' => $product->color->id,
                    'name' => $product->color->name,
                    'color_value' => $product->color->color_value
                ] : null,
                'media' => $product->media->map(fn($media) => [
                    'id' => $media->id,
                    'file_path' => $media->file_path ? asset($media->file_path) : null,
                    'thumbnail' => $media->thumbnail ? asset($media->thumbnail) : null,
                    'file_type' => $media->file_type,
                    'is_primary' => $media->is_primary
                ]),
                'image' => $product->image ? asset($product->image) : (optional($product->media->where('is_primary', true)->first())->file_path ? asset($product->media->where('is_primary', true)->first()->file_path) : (optional($product->media->first())->file_path ? asset($product->media->first()->file_path) : null))
            ]);
        }
    }

    /**
     * Get pagination data
     */
    private function getPaginationData($products)
    {
        return [
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'from' => $products->firstItem(),
            'to' => $products->lastItem(),
            'has_more_pages' => $products->hasMorePages()
        ];
    }

    /**
     * Return error response
     */
    private function errorResponse(string $message, string $error, int $code = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $error
        ], $code);
    }
}
