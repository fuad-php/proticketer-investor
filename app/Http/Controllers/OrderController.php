<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Client;
use App\Models\Investor;
use App\Models\Investment;
use App\Models\Approval;
use App\Models\User;
use App\Notifications\OrderApprovalRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view orders')->only(['index', 'show']);
        $this->middleware('permission:create orders')->only(['create', 'store']);
        $this->middleware('permission:edit orders')->only(['edit', 'update']);
        $this->middleware('permission:delete orders')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with(['client', 'creator', 'investments', 'approvals'])
            ->latest()
            ->paginate(15);

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::where('is_active', true)->get();
        $investors = Investor::where('is_active', true)->get();
        
        return view('orders.create', compact('clients', 'investors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'nullable|exists:clients,id',
            'total_amount' => 'required|numeric|min:0',
            'profit_percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
            'investors' => 'required|array|min:1',
            'investors.*.investor_id' => 'required|exists:investors,id',
            'investors.*.amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Create the order
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'client_id' => $request->client_id,
                'created_by' => auth()->id(),
                'title' => $request->title,
                'description' => $request->description,
                'total_amount' => $request->total_amount,
                'profit_percentage' => $request->profit_percentage,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'draft',
                'payment_status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Create investments for each investor
            foreach ($request->investors as $investorData) {
                $investor = Investor::find($investorData['investor_id']);
                $amount = $investorData['amount'];
                $expectedProfit = $amount * ($request->profit_percentage / 100);

                Investment::create([
                    'order_id' => $order->id,
                    'investor_id' => $investor->id,
                    'amount' => $amount,
                    'profit_percentage' => $request->profit_percentage,
                    'expected_profit' => $expectedProfit,
                    'investment_date' => $request->start_date,
                    'maturity_date' => $request->end_date,
                    'status' => 'active',
                    'payment_status' => 'pending',
                ]);

                // Update investor totals
                $investor->increment('total_invested', $amount);
                $investor->increment('current_balance', $amount);
            }

            // Create approval workflow
            $this->createApprovalWorkflow($order);

            // Notify first approver
            $this->notifyFirstApprover($order);

            DB::commit();

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order created successfully and sent for approval.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load(['client', 'creator', 'investments.investor', 'approvals.approver']);
        
        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        if ($order->status !== 'draft') {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Only draft orders can be edited.');
        }

        $clients = Client::where('is_active', true)->get();
        $investors = Investor::where('is_active', true)->get();
        
        return view('orders.edit', compact('order', 'clients', 'investors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        if ($order->status !== 'draft') {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Only draft orders can be edited.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'nullable|exists:clients,id',
            'total_amount' => 'required|numeric|min:0',
            'profit_percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        $order->update($request->only([
            'title', 'description', 'client_id', 'total_amount',
            'profit_percentage', 'start_date', 'end_date', 'notes'
        ]));

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        if ($order->status !== 'draft') {
            return redirect()->route('orders.index')
                ->with('error', 'Only draft orders can be deleted.');
        }

        DB::beginTransaction();
        
        try {
            // Update investor balances
            foreach ($order->investments as $investment) {
                $investor = $investment->investor;
                $investor->decrement('total_invested', $investment->amount);
                $investor->decrement('current_balance', $investment->amount);
            }

            // Delete related records
            $order->investments()->delete();
            $order->approvals()->delete();
            $order->delete();

            DB::commit();

            return redirect()->route('orders.index')
                ->with('success', 'Order deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('orders.index')
                ->with('error', 'Failed to delete order: ' . $e->getMessage());
        }
    }

    /**
     * Create approval workflow for the order
     */
    private function createApprovalWorkflow(Order $order)
    {
        $approvalRoles = ['director', 'managing_director', 'chairman'];
        
        foreach ($approvalRoles as $role) {
            Approval::create([
                'order_id' => $order->id,
                'approver_id' => null, // Will be assigned when user with role is available
                'approver_role' => $role,
                'status' => 'pending',
                'version' => 1,
            ]);
        }
    }

    /**
     * Notify first approver in the workflow
     */
    private function notifyFirstApprover(Order $order)
    {
        $firstApprover = User::role('director')->first();
        if ($firstApprover) {
            $firstApprover->notify(new OrderApprovalRequest($order, 'director'));
        }
    }
}
