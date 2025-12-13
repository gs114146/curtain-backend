<?php

namespace App\Http\Controllers;

use App\Models\Transparency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TransparencyController extends Controller
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

        return view('admin.transparency.index');
    }

    /**
     * Get DataTable data for transparency.
     */
    public function getDataTableData()
    {
        $query = Transparency::withCount('products');

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
        $totalRecords = Transparency::count();
        $filteredRecords = $query->count();

        // Handle pagination
        $start = request('start', 0);
        $length = request('length', 10);
        $transparencies = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($transparencies as $transparency) {
            $data[] = [
                'name' => $transparency->name,
                'status' => $transparency->status,
                'products_count' => $transparency->products_count ?? 0,
                'actions' => $transparency->id
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
                'name' => 'required|string|max:255|unique:transparency,name',
                'status' => 'required|in:active,inactive'
            ]);

            $transparency = Transparency::create($request->all());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transparency created successfully.',
                    'data' => $transparency
                ], 201);
            }

            return redirect()->route('admin.transparency.index')->with('success', 'Transparency created successfully.');
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
                    'message' => 'An error occurred while creating the transparency.'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transparency $transparency)
    {
        if (request()->ajax()) {
            return response()->json($transparency);
        }

        return view('admin.transparency.show', compact('transparency'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transparency $transparency)
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:transparency,name,' . $transparency->id,
                'status' => 'sometimes|required|in:active,inactive'
            ]);

            $transparency->update($request->all());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transparency updated successfully.',
                    'data' => $transparency
                ]);
            }

            return redirect()->route('admin.transparency.index')->with('success', 'Transparency updated successfully.');
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
                    'message' => 'An error occurred while updating the transparency.'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transparency $transparency)
    {
        try {
            // Check if transparency is being used by any products
            if ($transparency->products()->count() > 0) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete transparency. It is being used by ' . $transparency->products()->count() . ' product(s).'
                    ], 409);
                }
                return redirect()->route('admin.transparency.index')->with('error', 'Cannot delete transparency. It is being used by products.');
            }

            $transparency->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transparency deleted successfully.'
                ]);
            }

            return redirect()->route('admin.transparency.index')->with('success', 'Transparency deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the transparency.'
                ], 500);
            }
            throw $e;
        }
    }
}
