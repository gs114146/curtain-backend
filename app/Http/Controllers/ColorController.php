<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ColorController extends Controller
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

        return view('admin.colors.index');
    }

    /**
     * Get DataTables data for AJAX requests
     */
    public function getDataTableData()
    {
        $query = Color::withCount('products');

        // Handle search
        if (request()->has('search') && !empty(request('search')['value'])) {
            $searchValue = request('search')['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('color_value', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }

        // Handle ordering
        if (request()->has('order')) {
            $orderColumn = request('order')[0]['column'];
            $orderDir = request('order')[0]['dir'];
            $columns = ['name', 'color_value', 'status', 'products_count', 'actions'];

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
        $totalRecords = Color::count();
        $filteredRecords = $query->count();

        // Handle pagination
        $start = request('start', 0);
        $length = request('length', 10);
        $colors = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($colors as $color) {
            $data[] = [
                'name' => $color->name,
                'color_value' => $color->color_value,
                'status' => $color->status,
                'products_count' => $color->products_count ?? 0,
                'actions' => $color->id
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
                'name' => 'required|string|max:255|unique:colors,name',
                'color_value' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/|unique:colors,color_value',
                'status' => 'required|in:active,inactive'
            ]);

            $color = Color::create($request->all());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Color created successfully.',
                    'data' => $color
                ], 201);
            }

            return redirect()->route('admin.colors.index')->with('success', 'Color created successfully.');
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
                    'message' => 'An error occurred while creating the color.'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Color $color)
    {
        if (request()->ajax()) {
            return response()->json($color);
        }

        return view('admin.colors.show', compact('color'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Color $color)
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:colors,name,' . $color->id,
                'color_value' => 'sometimes|required|string|regex:/^#[0-9A-Fa-f]{6}$/|unique:colors,color_value,' . $color->id,
                'status' => 'sometimes|required|in:active,inactive'
            ]);

            $color->update($request->all());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Color updated successfully.',
                    'data' => $color
                ]);
            }

            return redirect()->route('admin.colors.index')->with('success', 'Color updated successfully.');
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
                    'message' => 'An error occurred while updating the color.'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Color $color)
    {
        try {
            // Check if color is being used by any products
            if ($color->products()->count() > 0) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete color. It is being used by ' . $color->products()->count() . ' product(s).'
                    ], 409);
                }
                return redirect()->route('admin.colors.index')->with('error', 'Cannot delete color. It is being used by products.');
            }

            $color->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Color deleted successfully.'
                ]);
            }

            return redirect()->route('admin.colors.index')->with('success', 'Color deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the color.'
                ], 500);
            }
            throw $e;
        }
    }
}
