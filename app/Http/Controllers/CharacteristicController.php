<?php

namespace App\Http\Controllers;

use App\Models\Characteristic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CharacteristicController extends Controller
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

        return view('admin.characteristics.index');
    }

    /**
     * Get DataTable data for characteristics.
     */
    public function getDataTableData()
    {
        $query = Characteristic::withCount('products');

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
        $totalRecords = Characteristic::count();
        $filteredRecords = $query->count();

        // Handle pagination
        $start = request('start', 0);
        $length = request('length', 10);
        $characteristics = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($characteristics as $characteristic) {
            $data[] = [
                'name' => $characteristic->name,
                'status' => $characteristic->status,
                'products_count' => $characteristic->products_count ?? 0,
                'actions' => $characteristic->id
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
                'name' => 'required|string|max:255|unique:characteristics,name',
                'status' => 'required|in:active,inactive'
            ]);

            $characteristic = Characteristic::create($request->all());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Characteristic created successfully.',
                    'data' => $characteristic
                ], 201);
            }

            return redirect()->route('admin.characteristics.index')->with('success', 'Characteristic created successfully.');
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
                    'message' => 'An error occurred while creating the characteristic.'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Characteristic $characteristic)
    {
        if (request()->ajax()) {
            return response()->json($characteristic);
        }

        return view('admin.characteristics.show', compact('characteristic'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Characteristic $characteristic)
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:characteristics,name,' . $characteristic->id,
                'status' => 'sometimes|required|in:active,inactive'
            ]);

            $characteristic->update($request->all());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Characteristic updated successfully.',
                    'data' => $characteristic
                ]);
            }

            return redirect()->route('admin.characteristics.index')->with('success', 'Characteristic updated successfully.');
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
                    'message' => 'An error occurred while updating the characteristic.'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Characteristic $characteristic)
    {
        try {
            // Check if characteristic is being used by any products
            if ($characteristic->products()->count() > 0) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete characteristic. It is being used by ' . $characteristic->products()->count() . ' product(s).'
                    ], 409);
                }
                return redirect()->route('admin.characteristics.index')->with('error', 'Cannot delete characteristic. It is being used by products.');
            }

            $characteristic->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Characteristic deleted successfully.'
                ]);
            }

            return redirect()->route('admin.characteristics.index')->with('success', 'Characteristic deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the characteristic.'
                ], 500);
            }
            throw $e;
        }
    }
}
