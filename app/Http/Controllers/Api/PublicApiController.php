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
use App\Models\Usage;
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

            // Get the SQL query for debugging (only in non-production)
            $sqlQuery = null;
            $filterCounts = [];
            if (config('app.debug')) {
                $sqlQuery = $query->toSql();
                $bindings = $query->getBindings();
                // Replace bindings in SQL for readability
                foreach ($bindings as $binding) {
                    $value = is_numeric($binding) ? $binding : "'" . $binding . "'";
                    $sqlQuery = preg_replace('/\?/', $value, $sqlQuery, 1);
                }

                // Count products matching each individual filter
                $baseQuery = Product::where('status', 'active');
                $filterCounts['base'] = $baseQuery->count();

                // Test each filter individually
                if ($designIds = $this->normalizeIds($request->input('design_id'))) {
                    $testQuery = clone $baseQuery;
                    $testQuery->whereHas('designs', function ($q) use ($designIds) {
                        $q->whereIn('designs.id', $designIds);
                    });
                    $filterCounts['design_id_' . implode(',', $designIds)] = $testQuery->count();
                }

                if ($materialIds = $this->normalizeIds($request->input('material_id'))) {
                    $testQuery = clone $baseQuery;
                    $testQuery->whereHas('materials', function ($q) use ($materialIds) {
                        $q->whereIn('materials.id', $materialIds);
                    });
                    $filterCounts['material_id_' . implode(',', $materialIds)] = $testQuery->count();
                }

                if ($typeValues = $this->normalizeStrings($request->input('type'))) {
                    $testQuery = clone $baseQuery;
                    $testQuery->whereIn('type', $typeValues);
                    $filterCounts['type_' . implode(',', $typeValues)] = $testQuery->count();
                }

                if ($usageIds = $this->normalizeIds($request->input('usage_id'))) {
                    $testQuery = clone $baseQuery;
                    $testQuery->whereIn('usage_id', $usageIds);
                    $filterCounts['usage_id_' . implode(',', $usageIds)] = $testQuery->count();
                }

                if ($colorIds = $this->normalizeIds($request->input('color_id'))) {
                    $testQuery = clone $baseQuery;
                    $testQuery->where(function ($q) use ($colorIds) {
                        $q->whereIn('color_id', $colorIds)
                            ->orWhereHas('variants', function ($sq) use ($colorIds) {
                                $sq->where('status', 'active')
                                    ->whereIn('color_id', $colorIds);
                            });
                    });
                    $filterCounts['color_id_' . implode(',', $colorIds)] = $testQuery->count();
                }
            }

            $products = $query->paginate(min($request->get('per_page', 12), 50));

            $response = [
                'success' => true,
                'data' => $products->getCollection()->map(function ($p) use ($request) {
                    return $this->transformProduct($p, false, $request->search ? trim((string) $request->search) : null);
                }),
                'pagination' => $this->getPaginationData($products),
                'filters_applied' => [
                    'search' => $request->search,
                    'color_id' => $this->normalizeIds($request->input('color_id')),
                    'design_id' => $this->normalizeIds($request->input('design_id')),
                    'material_id' => $this->normalizeIds($request->input('material_id')),
                    'transparency_id' => $this->normalizeIds($request->input('transparency_id')),
                    'characteristic_id' => $this->normalizeIds($request->input('characteristic_id')),
                    'usage_id' => $this->normalizeIds($request->input('usage_id')),
                    'min_price' => $request->min_price,
                    'max_price' => $request->max_price,
                    'in_stock' => $request->in_stock,
                    'featured' => $request->featured,
                    'type' => $this->normalizeStrings($request->input('type')),
                    'product_base' => $this->normalizeStrings($request->input('product_base'), ['curtain', 'upholstery']),
                    'sort_by' => $request->sort_by,
                    'sort_order' => $request->sort_order,
                ]
            ];

            // Add SQL query in debug mode
            if ($sqlQuery) {
                $response['debug'] = [
                    'sql_query' => $sqlQuery,
                    'total_before_pagination' => $query->count(),
                    'filter_counts' => $filterCounts
                ];
            }

            return response()->json($response);
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
            $product = Product::with(['color', 'design', 'material', 'transparency', 'characteristic', 'usage', 'designs', 'materials', 'characteristics', 'media', 'variants.color', 'variants.media'])
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
                'characteristics' => Characteristic::class,
                'usage' => Usage::class
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
            $products = Product::with(['color', 'design', 'material', 'transparency', 'characteristic', 'usage', 'designs', 'materials', 'characteristics', 'media', 'variants.color', 'variants.media'])
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
     * Get products with type 'latest'
     */
    public function getLatestProducts(Request $request): JsonResponse
    {
        try {
            $query = Product::with(['color', 'design', 'material', 'transparency', 'characteristic', 'usage', 'designs', 'materials', 'characteristics', 'media', 'variants.color', 'variants.media'])
                ->where('status', 'active')
                ->where('type', 'latest');

            // Product Base filter (curtain or upholstery)
            if ($productBase = $request->product_base) {
                $baseValues = is_array($productBase) ? $productBase : explode(',', $productBase);
                $baseValues = array_map('trim', $baseValues);
                $baseValues = array_filter($baseValues, function ($value) {
                    return in_array(strtolower($value), ['curtain', 'upholstery']);
                });
                if (!empty($baseValues)) {
                    $query->whereIn('product_base', $baseValues);
                }
            }

            // Variant count filter
            if ($request->has('variant_count')) {
                $variantCount = (int) $request->variant_count;
                if ($variantCount > 0) {
                    // Filter products that have exactly this many variants
                    $query->has('variants', '=', $variantCount);
                } elseif ($variantCount === 0) {
                    // Filter products with no variants (legacy products)
                    $query->doesntHave('variants');
                }
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
                    'filter_type' => 'latest',
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
     * Always applies filters when provided, even if they return 0 results
     */
    private function buildProductQuery(Request $request)
    {
        $query = Product::with(['color', 'design', 'material', 'transparency', 'characteristic', 'usage', 'designs', 'materials', 'characteristics', 'media', 'variants.color', 'variants.media'])
            ->where('status', 'active');

        // Search - name, description, manual_product_id, and variant article/article_description
        if ($search = $request->search) {
            $search = trim((string) $search);
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('manual_product_id', 'LIKE', "%{$search}%")
                        ->orWhereHas('variants', function ($vq) use ($search) {
                            $vq->where('status', 'active')
                                ->where(function ($sq) use ($search) {
                                    $sq->where('article', 'LIKE', "%{$search}%")
                                        ->orWhere('article_description', 'LIKE', "%{$search}%");
                                });
                        });
                });
            }
        }

        // ID-based filters (single relationship) - accepts comma-separated values
        // Always apply if filter is provided
        if ($transparencyIds = $this->normalizeIds($request->input('transparency_id'))) {
            $query->whereIn('transparency_id', $transparencyIds);
        }

        if ($usageIds = $this->normalizeIds($request->input('usage_id'))) {
            $query->whereIn('usage_id', $usageIds);
        }

        // ID-based filters (many-to-many relationships) - accepts comma-separated values
        // Always apply if filter is provided
        if ($designIds = $this->normalizeIds($request->input('design_id') ?: $request->input('design_ids'))) {
            $query->whereHas('designs', function ($q) use ($designIds) {
                $q->whereIn('designs.id', $designIds);
            });
        }

        if ($materialIds = $this->normalizeIds($request->input('material_id') ?: $request->input('material_ids'))) {
            $query->whereHas('materials', function ($q) use ($materialIds) {
                $q->whereIn('materials.id', $materialIds);
            });
        }

        if ($characteristicIds = $this->normalizeIds($request->input('characteristic_id') ?: $request->input('characteristic_ids'))) {
            $query->whereHas('characteristics', function ($q) use ($characteristicIds) {
                $q->whereIn('characteristics.id', $characteristicIds);
            });
        }

        // Color filter - accepts comma-separated values
        // Checks both legacy color_id and variant colors (OR logic)
        // Always apply if filter is provided
        // Only checks active variants
        if ($colorIds = $this->normalizeIds($request->input('color_id'))) {
            $query->where(function ($q) use ($colorIds) {
                $q->whereIn('color_id', $colorIds)
                    ->orWhereHas('variants', function ($sq) use ($colorIds) {
                        $sq->where('status', 'active')
                            ->whereIn('color_id', $colorIds);
                    });
            });
        }

        // Price range filter - accepts single min/max values
        // A product matches if it has at least one price point (product or variant) within the range
        // Always apply if filter is provided
        $minPrice = $request->min_price ? (float) $request->min_price : null;
        $maxPrice = $request->max_price ? (float) $request->max_price : null;

        if ($minPrice !== null || $maxPrice !== null) {
            $query->where(function ($q) use ($minPrice, $maxPrice) {
                $q->where(function ($productPriceQ) use ($minPrice, $maxPrice) {
                    $productPriceQ->whereNotNull('price');
                    if ($minPrice !== null && $maxPrice !== null) {
                        $productPriceQ->where('price', '>=', $minPrice)
                            ->where('price', '<=', $maxPrice);
                    } elseif ($minPrice !== null) {
                        $productPriceQ->where('price', '>=', $minPrice);
                    } elseif ($maxPrice !== null) {
                        $productPriceQ->where('price', '<=', $maxPrice);
                    }
                })
                    ->orWhereHas('variants', function ($variantQ) use ($minPrice, $maxPrice) {
                        $variantQ->where('status', 'active')
                            ->whereNotNull('price');
                        if ($minPrice !== null && $maxPrice !== null) {
                            $variantQ->where('price', '>=', $minPrice)
                                ->where('price', '<=', $maxPrice);
                        } elseif ($minPrice !== null) {
                            $variantQ->where('price', '>=', $minPrice);
                        } elseif ($maxPrice !== null) {
                            $variantQ->where('price', '<=', $maxPrice);
                        }
                    });
            });
        }

        // Boolean filters - accepts '1' or 'true' as true, everything else as false
        // Always apply if filter is provided
        // Only checks active variants
        if ($request->in_stock == '1' || $request->in_stock === 'true') {
            $query->where(function ($q) {
                $q->where('in_stock', true)
                    ->orWhereHas('variants', function ($sq) {
                        $sq->where('status', 'active')
                            ->where('in_stock', true);
                    });
            });
        }

        if ($request->featured == '1' || $request->featured === 'true') {
            $query->where('featured', true);
        }

        // String-based filters - accepts comma-separated values
        // Always apply if filter is provided
        $typeValues = $this->normalizeStrings($request->input('type'));
        if (!empty($typeValues)) {
            $query->whereIn('type', $typeValues);
        } else {
            // If no type is specified, default to product_listing
            $query->where('type', 'product_listing');
        }

        // Product Base filter - accepts comma-separated values
        // Always apply if filter is provided
        $productBaseValues = $this->normalizeStrings($request->input('product_base'), ['curtain', 'upholstery']);
        if (!empty($productBaseValues)) {
            $query->whereIn('product_base', $productBaseValues);
        }

        // Variant count filter - accepts single integer value
        if ($request->has('variant_count')) {
            $variantCount = (int) $request->variant_count;
            if ($variantCount > 0) {
                // Filter products that have exactly this many variants
                $query->has('variants', '=', $variantCount);
            } elseif ($variantCount === 0) {
                // Filter products with no variants (legacy products)
                $query->doesntHave('variants');
            }
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
     * Handles: null, "null", "", empty arrays
     */
    private function normalizeIds($value): array
    {
        // Handle null, empty string, empty array, or string "null"
        if (
            $value === null || $value === '' || $value === [] ||
            (is_string($value) && strtolower(trim($value)) === 'null')
        ) {
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
            // Skip empty strings and "null" strings
            if ($v === '' || strtolower($v) === 'null') {
                return null;
            }
            return (int) $v;
        }, $arr), function ($v) {
            return $v !== null && $v !== 0; // keep non-null, non-zero IDs
        })));

        return $ids;
    }

    /**
     * Normalize string values from request into an array of strings.
     * Accepts: ["value1","value2"], "value1,value2", "value1"
     * Optionally filters to only allow specific values
     * Handles: null, "null", "", empty arrays
     */
    private function normalizeStrings($value, array $allowedValues = []): array
    {
        // Handle null, empty string, empty array, or string "null"
        if (
            $value === null || $value === '' || $value === [] ||
            (is_string($value) && strtolower(trim($value)) === 'null')
        ) {
            return [];
        }

        // If already an array, use it
        if (is_array($value)) {
            $arr = $value;
        } else {
            // Split comma-separated string
            $arr = explode(',', (string) $value);
        }

        // Trim and filter empty values and "null" strings
        $strings = array_map('trim', $arr);
        $strings = array_filter($strings, function ($v) {
            return $v !== '' && strtolower($v) !== 'null';
        });

        // If allowed values are specified, filter to only those
        if (!empty($allowedValues)) {
            $allowedLower = array_map('strtolower', $allowedValues);
            $strings = array_filter($strings, function ($v) use ($allowedLower) {
                return in_array(strtolower($v), $allowedLower);
            });
            // Normalize case to match allowed values
            $strings = array_map(function ($v) use ($allowedValues, $allowedLower) {
                $index = array_search(strtolower($v), $allowedLower);
                return $index !== false ? $allowedValues[$index] : $v;
            }, $strings);
        }

        // Remove duplicates and reindex
        return array_values(array_unique($strings));
    }

    /**
     * Transform product data for API response.
     * When $search is provided and a variant matches (article or article_description),
     * that variant is used as default_variant; otherwise is_default or first variant.
     */
    public function transformProduct($product, bool $includeVariants = false, ?string $search = null)
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
            'type' => $product->type,
            'product_base' => $product->product_base,
            'featured' => $product->featured,
            'status' => $product->status,
            'in_stock' => $product->in_stock,
            'meta_title' => $product->meta_title,
            'meta_description' => $product->meta_description,
            'color_no' => $product->color_no,
            'color_id' => $product->color_id,
            'design_id' => $product->design_id,
            'material_id' => $product->material_id,
            'transparency_id' => $product->transparency_id,
            'characteristic_id' => $product->characteristic_id,
            'designs' => $product->designs, // Legacy text field
            'designs_list' => $product->relationLoaded('designs') ? $product->getRelation('designs')->pluck('name')->join(', ') : '',
            'designs_array' => $product->relationLoaded('designs') ? $product->getRelation('designs')->map(function ($design) {
                return ['id' => $design->id, 'name' => $design->name];
            })->toArray() : [],
            'materials_list' => $product->relationLoaded('materials') ? $product->getRelation('materials')->pluck('name')->join(', ') : '',
            'materials_array' => $product->relationLoaded('materials') ? $product->getRelation('materials')->map(function ($material) {
                return ['id' => $material->id, 'name' => $material->name];
            })->toArray() : [],
            'characteristics_list' => $product->relationLoaded('characteristics') ? $product->getRelation('characteristics')->pluck('name')->join(', ') : '',
            'characteristics_array' => $product->relationLoaded('characteristics') ? $product->getRelation('characteristics')->map(function ($characteristic) {
                return ['id' => $characteristic->id, 'name' => $characteristic->name];
            })->toArray() : [],
            'compositions' => $product->compositions,
            'hsn_code' => $product->hsn_code,
            'gst' => $product->gst !== null ? (float) $product->gst : null,
            'usage_id' => $product->usage_id,
            'usage' => $product->usage ? [
                'id' => $product->usage->id,
                'name' => $product->usage->name
            ] : null,
            // Single relationships (legacy)
            'color' => $product->color ? [
                'id' => $product->color->id,
                'name' => $product->color->name,
                'color_value' => $product->color->color_value
            ] : null,
            'design' => $product->design ? [
                'id' => $product->design->id,
                'name' => $product->design->name
            ] : null,
            'material' => $product->material ? [
                'id' => $product->material->id,
                'name' => $product->material->name
            ] : null,
            'transparency' => $product->transparency ? [
                'id' => $product->transparency->id,
                'name' => $product->transparency->name
            ] : null,
            'characteristic' => $product->characteristic ? [
                'id' => $product->characteristic->id,
                'name' => $product->characteristic->name
            ] : null,
            'created_at' => $product->created_at->toISOString(),
            'updated_at' => $product->updated_at->toISOString()
        ];

        if ($isVariantProduct) {
            // Product with variants
            $variants = $includeVariants ? $product->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'article' => $variant->article,
                    'article_description' => $variant->article_description,
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
                    'sort_order' => $variant->sort_order,
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

            // Determine default variant: if search matches a variant's article/article_description, use it; else is_default or first
            $variants = $product->variants ?? collect();
            if ($search !== null && $search !== '' && $variants->isNotEmpty()) {
                $searchLower = mb_strtolower(trim($search));
                $matchingVariant = $variants
                    ->where('status', 'active')
                    ->first(function ($v) use ($searchLower) {
                        $a = mb_strtolower((string) ($v->article ?? ''));
                        $d = mb_strtolower((string) ($v->article_description ?? ''));
                        return mb_strpos($a, $searchLower) !== false || mb_strpos($d, $searchLower) !== false;
                    });
                $defaultVariant = $matchingVariant ?: $variants->where('is_default', true)->first() ?: $variants->first();
            } else {
                $defaultVariant = $variants->where('is_default', true)->first() ?: $variants->first();
            }
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
                    'article' => $defaultVariant->article,
                    'article_description' => $defaultVariant->article_description,
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
                'product_type' => 'variant_product', // Indicates this is a variant-based product
                'variants' => $includeVariants ? $variants : null,
                'variant_count' => $product->variants->count(),
                'price_range' => $priceRange,
                'min_price' => $minPrice ? '€' . number_format($minPrice, 2) : null,
                'max_price' => $maxPrice ? '€' . number_format($maxPrice, 2) : null,
                'total_stock' => $totalStock,
                'in_stock' => $inStock, // Override with calculated value from variants
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
                'product_type' => 'single_product', // Indicates this is a legacy single product
                'variant_count' => 0, // Single products have no variants
                'sku' => $product->sku,
                'price' => $product->price ? '€' . number_format($product->price, 2) : null,
                'sale_price' => $product->sale_price ? '€' . number_format($product->sale_price, 2) : null,
                'stock_quantity' => $product->stock_quantity,
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
