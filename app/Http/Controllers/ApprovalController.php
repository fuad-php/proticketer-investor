<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Approval;
use App\Models\Order;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\Investor;
use App\Models\User;
use App\Notifications\OrderApprovalRequest;
use App\Notifications\OrderApproved;
use App\Notifications\ProfitPayout;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view approvals')->only(['index', 'show']);
        $this->middleware('permission:approve orders|reject orders')->only(['approve', 'reject']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Approval::with(['order.client', 'order.creator', 'approver', 'order.investments.investor']);
        
        // Get approvals based on user role
        if ($user->hasRole(['director', 'managing_director', 'chairman'])) {
            $userRole = $this->getUserApprovalRole($user);
            $query->where(function($q) use ($user, $userRole) {
                $q->where('approver_id', $user->id)
                  ->orWhere('approver_role', $userRole);
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by approver role
        if ($request->filled('approver_role')) {
            $query->where('approver_role', $request->approver_role);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $approvals = $query->latest()->paginate(15);
        
        // Get statistics for dashboard
        $stats = [
            'pending' => Approval::pending()->count(),
            'approved' => Approval::approved()->count(),
            'rejected' => Approval::rejected()->count(),
            'requested_changes' => Approval::requestedChanges()->count(),
        ];

        return view('approvals.index', compact('approvals', 'stats'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Approval $approval)
    {
        $approval->load(['order.client', 'order.creator', 'order.investments.investor', 'approver']);
        
        return view('approvals.show', compact('approval'));
    }

    /**
     * Approve an order
     */
    public function approve(Request $request, Approval $approval)
    {
        $user = auth()->user();
        
        // Check if user can approve this order
        if (!$this->canApprove($user, $approval)) {
            return back()->with('error', 'You are not authorized to approve this order.');
        }

        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        
        try {
            // Update approval
            $approval->update([
                'status' => 'approved',
                'comments' => $request->comments,
                'approved_at' => now(),
                'approver_id' => $user->id,
            ]);

            // Check if all approvals are completed
            $order = $approval->order;
            $allApprovals = $order->approvals;
            $approvedCount = $allApprovals->where('status', 'approved')->count();
            
            // Notify order creator about approval
            $order->creator->notify(new OrderApproved($order, $user->name));
            
            if ($approvedCount === $allApprovals->count()) {
                // All approvals completed - activate the order
                $this->activateOrder($order);
            } else {
                // Notify next approver
                $this->notifyNextApprover($order, $approvedCount);
            }

            DB::commit();

            return redirect()->route('approvals.show', $approval)
                ->with('success', 'Order approved successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to approve order: ' . $e->getMessage());
        }
    }

    /**
     * Reject an order
     */
    public function reject(Request $request, Approval $approval)
    {
        $user = auth()->user();
        
        // Check if user can reject this order
        if (!$this->canApprove($user, $approval)) {
            return back()->with('error', 'You are not authorized to reject this order.');
        }

        $request->validate([
            'comments' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        
        try {
            // Update approval
            $approval->update([
                'status' => 'rejected',
                'comments' => $request->comments,
                'approved_at' => now(),
                'approver_id' => $user->id,
            ]);

            // Reject the order and all pending approvals
            $order = $approval->order;
            $order->update(['status' => 'cancelled']);
            
            $order->approvals()->where('status', 'pending')->update([
                'status' => 'rejected',
                'comments' => 'Order rejected by ' . $user->name,
                'approved_at' => now(),
            ]);

            // Refund investors
            $this->refundInvestors($order);

            DB::commit();

            return redirect()->route('approvals.show', $approval)
                ->with('success', 'Order rejected successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to reject order: ' . $e->getMessage());
        }
    }

    /**
     * Check if user can approve the order
     */
    private function canApprove($user, $approval)
    {
        $userRole = $this->getUserApprovalRole($user);
        
        return $user->hasRole(['director', 'managing_director', 'chairman']) &&
               $approval->approver_role === $userRole &&
               $approval->status === 'pending';
    }

    /**
     * Get user's approval role
     */
    private function getUserApprovalRole($user)
    {
        if ($user->hasRole('director')) return 'director';
        if ($user->hasRole('managing_director')) return 'managing_director';
        if ($user->hasRole('chairman')) return 'chairman';
        
        return null;
    }

    /**
     * Activate order after all approvals
     */
    private function activateOrder(Order $order)
    {
        $order->update(['status' => 'active']);
        
        // Create transactions for each investment
        foreach ($order->investments as $investment) {
            $this->createInvestmentTransaction($investment);
        }
    }

    /**
     * Create investment transaction
     */
    private function createInvestmentTransaction(Investment $investment)
    {
        $investor = $investment->investor;
        
        Transaction::create([
            'transaction_id' => 'TXN-' . strtoupper(uniqid()),
            'investor_id' => $investor->id,
            'investment_id' => $investment->id,
            'order_id' => $investment->order_id,
            'type' => 'investment',
            'entry_type' => 'debit',
            'amount' => $investment->amount,
            'balance_after' => $investor->current_balance,
            'description' => "Investment in order {$investment->order->order_number}",
            'created_by' => auth()->id(),
            'transaction_date' => now(),
        ]);
    }

    /**
     * Notify next approver in the workflow
     */
    private function notifyNextApprover(Order $order, int $approvedCount)
    {
        $approvalRoles = ['director', 'managing_director', 'chairman'];
        $nextRole = $approvalRoles[$approvedCount] ?? null;
        
        if ($nextRole) {
            $nextApprover = User::role($nextRole)->first();
            if ($nextApprover) {
                $nextApprover->notify(new OrderApprovalRequest($order, $nextRole));
            }
        }
    }

    /**
     * Refund investors when order is rejected
     */
    private function refundInvestors(Order $order)
    {
        foreach ($order->investments as $investment) {
            $investor = $investment->investor;
            
            // Update investor balances
            $investor->decrement('total_invested', $investment->amount);
            $investor->decrement('current_balance', $investment->amount);
            
            // Create refund transaction
            Transaction::create([
                'transaction_id' => 'REF-' . strtoupper(uniqid()),
                'investor_id' => $investor->id,
                'investment_id' => $investment->id,
                'order_id' => $investment->order_id,
                'type' => 'principal_return',
                'entry_type' => 'credit',
                'amount' => $investment->amount,
                'balance_after' => $investor->current_balance,
                'description' => "Refund for rejected order {$investment->order->order_number}",
                'created_by' => auth()->id(),
                'transaction_date' => now(),
            ]);
            
            // Update investment status
            $investment->update(['status' => 'cancelled']);
        }
    }

    /**
     * Create multi-stage approval workflow for an order
     */
    public static function createApprovalWorkflow(Order $order)
    {
        $approvalRoles = ['director', 'managing_director', 'chairman'];
        
        foreach ($approvalRoles as $role) {
            Approval::create([
                'order_id' => $order->id,
                'approver_role' => $role,
                'status' => 'pending',
                'version' => 1,
            ]);
        }
        
        // Notify first approver (Director)
        $firstApprover = User::role('director')->first();
        if ($firstApprover) {
            $firstApprover->notify(new OrderApprovalRequest($order, 'director'));
        }
    }

    /**
     * Request changes to an order
     */
    public function requestChanges(Request $request, Approval $approval)
    {
        $user = auth()->user();
        
        // Check if user can request changes
        if (!$this->canApprove($user, $approval)) {
            return back()->with('error', 'You are not authorized to request changes for this order.');
        }

        $request->validate([
            'comments' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        
        try {
            // Update approval
            $approval->update([
                'status' => 'requested_changes',
                'comments' => $request->comments,
                'approved_at' => now(),
                'approver_id' => $user->id,
            ]);

            // Reset all subsequent approvals to pending
            $order = $approval->order;
            $approvalRoles = ['director', 'managing_director', 'chairman'];
            $currentRoleIndex = array_search($approval->approver_role, $approvalRoles);
            
            for ($i = $currentRoleIndex + 1; $i < count($approvalRoles); $i++) {
                $order->approvals()
                    ->where('approver_role', $approvalRoles[$i])
                    ->update([
                        'status' => 'pending',
                        'comments' => null,
                        'approved_at' => null,
                        'approver_id' => null,
                    ]);
            }

            // Notify order creator about requested changes
            $order->creator->notify(new OrderApprovalRequest($order, 'changes_requested', $request->comments));

            DB::commit();

            return redirect()->route('approvals.show', $approval)
                ->with('success', 'Changes requested successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to request changes: ' . $e->getMessage());
        }
    }

    /**
     * Get approval workflow status for an order
     */
    public function getWorkflowStatus(Order $order)
    {
        $approvals = $order->approvals()->orderBy('approver_role')->get();
        $workflow = [];
        
        $approvalRoles = ['director', 'managing_director', 'chairman'];
        
        foreach ($approvalRoles as $role) {
            $approval = $approvals->where('approver_role', $role)->first();
            $workflow[] = [
                'role' => $role,
                'status' => $approval ? $approval->status : 'pending',
                'approver' => $approval ? $approval->approver : null,
                'comments' => $approval ? $approval->comments : null,
                'approved_at' => $approval ? $approval->approved_at : null,
            ];
        }
        
        return $workflow;
    }
}
