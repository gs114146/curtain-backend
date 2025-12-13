<?php

namespace App\Http\Controllers;

use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ContactUsController extends Controller
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

        return view('admin.contact-us.index');
    }

    /**
     * Get DataTable data for contact us messages.
     */
    public function getDataTableData()
    {
        $query = ContactUs::query();

        // Handle search (from DataTables search)
        if (request()->has('search') && !empty(request('search')['value'])) {
            $searchValue = request('search')['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('full_name', 'like', "%{$searchValue}%")
                    ->orWhere('email', 'like', "%{$searchValue}%")
                    ->orWhere('phone_number', 'like', "%{$searchValue}%")
                    ->orWhere('subject', 'like', "%{$searchValue}%")
                    ->orWhere('project_type', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }

        // Handle ordering
        if (request()->has('order')) {
            $orderColumn = request('order')[0]['column'];
            $orderDir = request('order')[0]['dir'];
            $columns = ['full_name', 'email', 'phone_number', 'subject', 'project_type', 'created_at'];

            if (isset($columns[$orderColumn])) {
                $query->orderBy($columns[$orderColumn], $orderDir);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Get total count before pagination
        $totalRecords = ContactUs::count();
        $filteredRecords = $query->count();

        // Handle pagination
        $start = request('start', 0);
        $length = request('length', 10);
        $contactMessages = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($contactMessages as $contact) {
            $data[] = [
                'full_name' => $contact->full_name,
                'email' => $contact->email,
                'phone_number' => $contact->phone_number,
                'subject' => $contact->subject,
                'project_type' => $contact->project_type,
                'created_at' => $contact->created_at->format('M d, Y')
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
                'project_type' => 'required|string|max:255',
                'subject' => 'required|string|max:255',
                'message' => 'required|string|max:2000'
            ]);

            $contactMessage = ContactUs::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Contact message submitted successfully.',
                'data' => $contactMessage
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
                'message' => 'An error occurred while creating the contact message.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ContactUs $contactUs): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $contactUs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the contact message.'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContactUs $contactUs): JsonResponse
    {
        try {
            $request->validate([
                'full_name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|max:255',
                'phone_number' => 'sometimes|required|string|max:20',
                'project_type' => 'sometimes|required|string|max:255',
                'subject' => 'sometimes|required|string|max:255',
                'message' => 'sometimes|required|string|max:2000',
                'status' => 'sometimes|in:unread,read,replied,closed'
            ]);

            $contactUs->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Contact message updated successfully.',
                'data' => $contactUs
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
                'message' => 'An error occurred while updating the contact message.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactUs $contactUs): JsonResponse
    {
        try {
            $contactUs->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contact message deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the contact message.'
            ], 500);
        }
    }

    /**
     * Update contact message status
     */
    public function updateStatus(Request $request, ContactUs $contactUs): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:unread,read,replied,closed'
            ]);

            $contactUs->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Contact message status updated successfully.',
                'data' => $contactUs
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
                'message' => 'An error occurred while updating the contact message status.'
            ], 500);
        }
    }

    /**
     * Get contact messages by status
     */
    public function getByStatus(string $status): JsonResponse
    {
        try {
            $validStatuses = ['unread', 'read', 'replied', 'closed'];

            if (!in_array($status, $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status provided.'
                ], 400);
            }

            $contactMessages = ContactUs::where('status', $status)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $contactMessages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching contact messages by status.'
            ], 500);
        }
    }

    /**
     * Get contact messages by project type
     */
    public function getByProjectType(string $projectType): JsonResponse
    {
        try {
            $contactMessages = ContactUs::where('project_type', $projectType)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $contactMessages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching contact messages by project type.'
            ], 500);
        }
    }
}
