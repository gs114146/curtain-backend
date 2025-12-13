<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ConsultationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['store']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return $this->getDataTableData();
        }

        return view('admin.consultations.index');
    }

    /**
     * Get DataTable data for consultations.
     */
    public function getDataTableData()
    {
        $query = Consultation::query();

        // Handle search (from DataTables search)
        if (request()->has('search') && !empty(request('search')['value'])) {
            $searchValue = request('search')['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('full_name', 'like', "%{$searchValue}%")
                    ->orWhere('email', 'like', "%{$searchValue}%")
                    ->orWhere('phone_number', 'like', "%{$searchValue}%")
                    ->orWhere('service_type', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }

        // Handle ordering
        if (request()->has('order')) {
            $orderColumn = request('order')[0]['column'];
            $orderDir = request('order')[0]['dir'];
            $columns = ['full_name', 'email', 'phone_number', 'preferred_date', 'service_type', 'created_at'];

            if (isset($columns[$orderColumn])) {
                $query->orderBy($columns[$orderColumn], $orderDir);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Get total count before pagination
        $totalRecords = Consultation::count();
        $filteredRecords = $query->count();

        // Handle pagination
        $start = request('start', 0);
        $length = request('length', 10);
        $consultations = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($consultations as $consultation) {
            $data[] = [
                'full_name' => $consultation->full_name,
                'email' => $consultation->email,
                'phone_number' => $consultation->phone_number,
                'preferred_date' => $consultation->preferred_date ? $consultation->preferred_date->format('Y-m-d') : '-',
                'service_type' => $consultation->service_type,
                'created_at' => $consultation->created_at->format('M d, Y')
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
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone_number' => 'required|string|max:20',
                'preferred_date' => 'required|date|after:today',
                'service_type' => 'required|string|max:255',
                'additional_message' => 'nullable|string|max:1000'
            ]);

            $consultation = Consultation::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Consultation request submitted successfully.',
                'data' => $consultation
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the consultation request.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Consultation $consultation): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $consultation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the consultation.'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Consultation $consultation): JsonResponse
    {
        try {
            $request->validate([
                'full_name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|max:255',
                'phone_number' => 'sometimes|required|string|max:20',
                'preferred_date' => 'sometimes|required|date|after:today',
                'service_type' => 'sometimes|required|string|max:255',
                'additional_message' => 'nullable|string|max:1000',
                'status' => 'sometimes|in:pending,confirmed,completed,cancelled'
            ]);

            $consultation->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Consultation updated successfully.',
                'data' => $consultation
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the consultation.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Consultation $consultation): JsonResponse
    {
        try {
            $consultation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Consultation deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the consultation.'
            ], 500);
        }
    }

    /**
     * Update consultation status
     */
    public function updateStatus(Request $request, Consultation $consultation): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,confirmed,completed,cancelled'
            ]);

            $consultation->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Consultation status updated successfully.',
                'data' => $consultation
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the consultation status.'
            ], 500);
        }
    }

    /**
     * Get consultations by status
     */
    public function getByStatus(string $status): JsonResponse
    {
        try {
            $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];

            if (!in_array($status, $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status provided.'
                ], 400);
            }

            $consultations = Consultation::where('status', $status)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $consultations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching consultations by status.'
            ], 500);
        }
    }
}
