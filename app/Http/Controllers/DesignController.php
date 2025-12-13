<?php

namespace App\Http\Controllers;

use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DesignController extends Controller
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

        return view('admin.designs.index');
    }

    /**
     * Get DataTable data for designs.
     */
    public function getDataTableData()
    {
        $query = Design::withCount('products');

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
        $totalRecords = Design::count();
        $filteredRecords = $query->count();

        // Handle pagination
        $start = request('start', 0);
        $length = request('length', 10);
        $designs = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($designs as $design) {
            $data[] = [
                'name' => $design->name,
                'status' => $design->status,
                'products_count' => $design->products_count ?? 0,
                'actions' => $design->id
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
                'name' => 'required|string|max:255|unique:designs,name',
                'status' => 'required|in:active,inactive'
            ]);

            $design = Design::create($request->all());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Design created successfully.',
                    'data' => $design
                ], 201);
            }

            return redirect()->route('admin.designs.index')->with('success', 'Design created successfully.');
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
                    'message' => 'An error occurred while creating the design.'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Design $design)
    {
        if (request()->ajax()) {
            return response()->json($design);
        }

        return view('admin.designs.show', compact('design'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Design $design)
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:designs,name,' . $design->id,
                'status' => 'sometimes|required|in:active,inactive'
            ]);

            $design->update($request->all());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Design updated successfully.',
                    'data' => $design
                ]);
            }

            return redirect()->route('admin.designs.index')->with('success', 'Design updated successfully.');
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
                    'message' => 'An error occurred while updating the design.'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Design $design)
    {
        try {
            // Check if design is being used by any products
            if ($design->products()->count() > 0) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete design. It is being used by ' . $design->products()->count() . ' product(s).'
                    ], 409);
                }
                return redirect()->route('admin.designs.index')->with('error', 'Cannot delete design. It is being used by products.');
            }

            $design->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Design deleted successfully.'
                ]);
            }

            return redirect()->route('admin.designs.index')->with('success', 'Design deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the design.'
                ], 500);
            }
            throw $e;
        }
    }
}
