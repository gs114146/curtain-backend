<?php

namespace App\Http\Controllers;

use App\Models\Usage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UsageController extends Controller
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

        return view('admin.usage.index');
    }

    /**
     * Get DataTable data for usage.
     */
    public function getDataTableData()
    {
        $query = Usage::withCount('products');

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
        $totalRecords = Usage::count();
        $filteredRecords = $query->count();

        // Handle pagination
        $start = request('start', 0);
        $length = request('length', 10);
        $usage = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($usage as $item) {
            $data[] = [
                'name' => $item->name,
                'status' => $item->status,
                'products_count' => $item->products_count ?? 0,
                'actions' => $item->id
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
                'name' => 'required|string|max:255|unique:usage,name',
                'status' => 'required|in:active,inactive'
            ]);

            $usage = Usage::create($request->all());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usage created successfully.',
                    'data' => $usage
                ], 201);
            }

            return redirect()->route('admin.usage.index')->with('success', 'Usage created successfully.');
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
                    'message' => 'An error occurred while creating the usage.'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Usage $usage)
    {
        if (request()->ajax()) {
            return response()->json($usage);
        }

        return view('admin.usage.show', compact('usage'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Usage $usage)
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:usage,name,' . $usage->id,
                'status' => 'sometimes|required|in:active,inactive'
            ]);

            $usage->update($request->all());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usage updated successfully.',
                    'data' => $usage
                ]);
            }

            return redirect()->route('admin.usage.index')->with('success', 'Usage updated successfully.');
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
                    'message' => 'An error occurred while updating the usage.'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Usage $usage)
    {
        try {
            // Check if usage is being used by any products
            if ($usage->products()->count() > 0) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete usage. It is being used by ' . $usage->products()->count() . ' product(s).'
                    ], 409);
                }
                return redirect()->route('admin.usage.index')->with('error', 'Cannot delete usage. It is being used by products.');
            }

            $usage->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usage deleted successfully.'
                ]);
            }

            return redirect()->route('admin.usage.index')->with('success', 'Usage deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the usage.'
                ], 500);
            }
            throw $e;
        }
    }
}
