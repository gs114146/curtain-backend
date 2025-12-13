<?php

namespace App\Http\Controllers;

use App\Models\ProductMedia;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProductMediaController extends Controller
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
    public function index(Request $request)
    {
        $query = ProductMedia::with('product');

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $media = $query->orderBy('sort_order')->get();

        if ($request->ajax()) {
            return response()->json($media);
        }

        return view('admin.product-media.index', compact('media'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,svg,mp4,avi,mov,wmv|max:10240', // 10MB max
            'file_type' => 'required|in:image,video',
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string',
            'is_primary' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('product-media', $fileName, 'public');

        $media = ProductMedia::create([
            'product_id' => $request->product_id,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $request->file_type,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'sort_order' => $request->sort_order ?? 0,
            'is_primary' => $request->is_primary ?? false,
            'alt_text' => $request->alt_text,
            'caption' => $request->caption,
        ]);

        if ($request->ajax()) {
            return response()->json($media->load('product'), 201);
        }

        return redirect()->back()->with('success', 'Media uploaded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductMedia $productMedia)
    {
        $productMedia->load('product');

        if (request()->ajax()) {
            return response()->json($productMedia);
        }

        return view('admin.product-media.show', compact('productMedia'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductMedia $productMedia)
    {
        $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string',
            'is_primary' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
            'status' => 'sometimes|in:active,inactive'
        ]);

        $productMedia->update($request->all());

        if ($request->ajax()) {
            return response()->json($productMedia->load('product'));
        }

        return redirect()->back()->with('success', 'Media updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductMedia $productMedia)
    {
        // Delete the file from storage
        if (Storage::disk('public')->exists($productMedia->file_path)) {
            Storage::disk('public')->delete($productMedia->file_path);
        }

        $productMedia->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Media deleted successfully']);
        }

        return redirect()->back()->with('success', 'Media deleted successfully.');
    }

    /**
     * Get media for a specific product
     */
    public function getProductMedia(Product $product)
    {
        $media = $product->media()->orderBy('sort_order')->get();

        if (request()->ajax()) {
            return response()->json($media);
        }

        return view('admin.products.media.index', compact('product', 'media'));
    }
}
