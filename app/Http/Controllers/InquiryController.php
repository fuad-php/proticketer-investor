<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inquiry;
use App\Models\Client;
use App\Models\User;
use App\Notifications\InquiryResponse;
use Illuminate\Support\Str;

class InquiryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view inquiries')->only(['index', 'show']);
        $this->middleware('permission:create inquiries')->only(['create', 'store']);
        $this->middleware('permission:edit inquiries')->only(['edit', 'update']);
        $this->middleware('permission:delete inquiries')->only(['destroy']);
        $this->middleware('permission:respond inquiries')->only(['respond']);
        $this->middleware('role:client')->only(['clientInquiries']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Inquiry::with(['client', 'assignedUser']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by overdue
        if ($request->filled('overdue')) {
            $query->where('preferred_timeframe', '<', now())
                  ->whereNotIn('status', ['completed', 'closed']);
        }

        $inquiries = $query->latest()->paginate(15);

        // Get statistics
        $stats = [
            'total' => Inquiry::count(),
            'received' => Inquiry::received()->count(),
            'in_progress' => Inquiry::inProgress()->count(),
            'quoted' => Inquiry::quoted()->count(),
            'completed' => Inquiry::completed()->count(),
            'closed' => Inquiry::closed()->count(),
            'overdue' => Inquiry::where('preferred_timeframe', '<', now())
                ->whereNotIn('status', ['completed', 'closed'])
                ->count(),
            'unassigned' => Inquiry::unassigned()->count(),
        ];

        // Get filter options
        $clients = Client::where('is_active', true)->get();
        $users = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['accounts', 'super_admin', 'director']);
        })->get();

        return view('inquiries.index', compact('inquiries', 'stats', 'clients', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::where('is_active', true)->get();
        $users = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['accounts', 'super_admin']);
        })->get();
        
        return view('inquiries.create', compact('clients', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'quantity' => 'nullable|string|max:100',
            'specifications' => 'nullable|string',
            'preferred_timeframe' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $inquiry = Inquiry::create([
            'inquiry_number' => 'INQ-' . strtoupper(Str::random(8)),
            'client_id' => $request->client_id,
            'subject' => $request->subject,
            'description' => $request->description,
            'category' => $request->category,
            'quantity' => $request->quantity,
            'specifications' => $request->specifications,
            'preferred_timeframe' => $request->preferred_timeframe,
            'status' => 'received',
            'assigned_to' => $request->assigned_to,
        ]);

        return redirect()->route('inquiries.show', $inquiry)
            ->with('success', 'Inquiry created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Inquiry $inquiry)
    {
        $inquiry->load(['client', 'assignedUser']);
        
        return view('inquiries.show', compact('inquiry'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inquiry $inquiry)
    {
        $clients = Client::where('is_active', true)->get();
        $users = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['accounts', 'super_admin']);
        })->get();
        
        return view('inquiries.edit', compact('inquiry', 'clients', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inquiry $inquiry)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'quantity' => 'nullable|string|max:100',
            'specifications' => 'nullable|string',
            'preferred_timeframe' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:received,in_progress,quoted,completed,closed',
        ]);

        $inquiry->update($request->all());

        return redirect()->route('inquiries.show', $inquiry)
            ->with('success', 'Inquiry updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inquiry $inquiry)
    {
        $inquiry->delete();

        return redirect()->route('inquiries.index')
            ->with('success', 'Inquiry deleted successfully.');
    }

    /**
     * Respond to an inquiry
     */
    public function respond(Request $request, Inquiry $inquiry)
    {
        $request->validate([
            'response' => 'required|string',
            'status' => 'required|in:in_progress,quoted,completed,closed',
        ]);

        $inquiry->update([
            'response' => $request->response,
            'status' => $request->status,
        ]);

        // Notify client about the response
        $client = $inquiry->client;
        if ($client) {
            // Find client user by email
            $clientUser = User::where('email', $client->email)->first();
            if ($clientUser) {
                $clientUser->notify(new InquiryResponse($inquiry, auth()->user()->name));
            }
        }

        return redirect()->route('inquiries.show', $inquiry)
            ->with('success', 'Response sent successfully.');
    }

    /**
     * Show client inquiries (for client role)
     */
    public function clientInquiries()
    {
        $user = auth()->user();
        
        // Find client by email
        $client = Client::where('email', $user->email)->first();
        
        if (!$client) {
            return redirect()->route('profile.edit')->with('error', 'Please complete your client profile.');
        }

        $inquiries = $client->inquiries()
            ->with(['assignedUser'])
            ->latest()
            ->paginate(15);

        return view('client.inquiries', compact('inquiries', 'client'));
    }

    /**
     * Assign inquiry to a user
     */
    public function assign(Request $request, Inquiry $inquiry)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $inquiry->update([
            'assigned_to' => $request->assigned_to,
            'priority' => $request->priority ?? 'medium',
            'status' => 'in_progress',
        ]);

        // Notify assigned user
        $assignedUser = User::find($request->assigned_to);
        $assignedUser->notify(new InquiryResponse($inquiry, 'assigned'));

        return redirect()->route('inquiries.show', $inquiry)
            ->with('success', 'Inquiry assigned successfully.');
    }

    /**
     * Update inquiry status
     */
    public function updateStatus(Request $request, Inquiry $inquiry)
    {
        $request->validate([
            'status' => 'required|in:received,in_progress,quoted,completed,closed',
            'actual_completion_date' => 'nullable|date',
        ]);

        $updateData = ['status' => $request->status];

        if ($request->status === 'completed' && $request->actual_completion_date) {
            $updateData['actual_completion_date'] = $request->actual_completion_date;
        }

        $inquiry->update($updateData);

        // Notify client if status changed to completed
        if ($request->status === 'completed') {
            $client = $inquiry->client;
            if ($client) {
                $clientUser = User::where('email', $client->email)->first();
                if ($clientUser) {
                    $clientUser->notify(new InquiryResponse($inquiry, 'completed'));
                }
            }
        }

        return redirect()->route('inquiries.show', $inquiry)
            ->with('success', 'Status updated successfully.');
    }

    /**
     * Add internal notes
     */
    public function addNotes(Request $request, Inquiry $inquiry)
    {
        $request->validate([
            'internal_notes' => 'required|string|max:1000',
        ]);

        $currentNotes = $inquiry->internal_notes ?: '';
        $newNotes = $currentNotes . "\n\n[" . now()->format('Y-m-d H:i:s') . "] " . $request->internal_notes;

        $inquiry->update(['internal_notes' => $newNotes]);

        return redirect()->route('inquiries.show', $inquiry)
            ->with('success', 'Notes added successfully.');
    }

    /**
     * Export inquiries as CSV
     */
    public function export(Request $request)
    {
        $query = Inquiry::with(['client', 'assignedUser']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $inquiries = $query->get();

        $filename = "inquiries_export_" . date('Y-m-d') . ".csv";

        return response()->streamDownload(function() use ($inquiries) {
            $handle = fopen('php://output', 'w');

            // Write header
            fputcsv($handle, [
                'Inquiry Number',
                'Client',
                'Subject',
                'Category',
                'Status',
                'Priority',
                'Assigned To',
                'Created Date',
                'Preferred Timeframe',
                'Response Date',
                'Quotation Amount',
                'Days Since Created',
                'Is Overdue'
            ]);

            // Write data
            foreach ($inquiries as $inquiry) {
                fputcsv($handle, [
                    $inquiry->inquiry_number,
                    $inquiry->client->name,
                    $inquiry->subject,
                    $inquiry->category,
                    $inquiry->status,
                    $inquiry->priority,
                    $inquiry->assignedUser ? $inquiry->assignedUser->name : 'Unassigned',
                    $inquiry->created_at->format('Y-m-d'),
                    $inquiry->preferred_timeframe ? $inquiry->preferred_timeframe->format('Y-m-d') : '',
                    $inquiry->response_date ? $inquiry->response_date->format('Y-m-d') : '',
                    $inquiry->quotation_amount,
                    $inquiry->days_since_created,
                    $inquiry->isOverdue() ? 'Yes' : 'No'
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
