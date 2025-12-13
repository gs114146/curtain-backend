<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MaterialController extends Controller
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

        return view('admin.materials.index');
    }

    /**
     * Get DataTable data for materials.
     */
    public function getDataTableData()
    {
        $query = Material::withCount('products');

        // Handle search
        if (request()->has('search') && !empty(request('search')['value'])) {
            $searchValue = request('search')['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }

        // Handle ordering
        if (request()->has('order')) {
            $orderColumn = request('order')[0]['column'];
            $orderDir = request('order')[0]['dir'];
            $columns = ['name', 'status', 'products_count', 'actions'];

            if (isset($columns[$orderColumn])) {
                if ($columns[$orderColumn] === 'actions') {
                    // Actions column is not orderable, so order by name instead
                    $query->orderBy('name', $orderDir);
                } else {
                    $query->orderBy($columns[$orderColumn], $orderDir);
                }
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        // Get total count before pagination
        $totalRecords = Material::count();
        $filteredRecords = $query->count();

        // Handle pagination
        $start = request('start', 0);
        $length = request('length', 10);
        $materials = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($materials as $material) {
            $data[] = [
                'name' => $material->name,
                'status' => $material->status,
                'products_count' => $material->products_count ?? 0,
                'actions' => $material->id
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
            $request->validate([
                'name' => 'required|string|max:255|unique:materials,name',
                'status' => 'required|in:active,inactive'
            ]);

            $material = Material::create($request->all());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Material created successfully.',
                    'data' => $material
                ], 201);
            }

            return redirect()->route('admin.materials.index')->with('success', 'Material created successfully.');
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
                    'message' => 'An error occurred while creating the material.'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Material $material)
    {
        if (request()->ajax()) {
            return response()->json($material);
        }

        return view('admin.materials.show', compact('material'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Material $material)
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:materials,name,' . $material->id,
                'status' => 'sometimes|required|in:active,inactive'
            ]);

            $material->update($request->all());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Material updated successfully.',
                    'data' => $material
                ]);
            }

            return redirect()->route('admin.materials.index')->with('success', 'Material updated successfully.');
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
                    'message' => 'An error occurred while updating the material.'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Material $material)
    {
        try {
            // Check if material is being used by any products
            if ($material->products()->count() > 0) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete material. It is being used by ' . $material->products()->count() . ' product(s).'
                    ], 409);
                }
                return redirect()->route('admin.materials.index')->with('error', 'Cannot delete material. It is being used by products.');
            }

            $material->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Material deleted successfully.'
                ]);
            }

            return redirect()->route('admin.materials.index')->with('success', 'Material deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the material.'
                ], 500);
            }
            throw $e;
        }
    }
}
