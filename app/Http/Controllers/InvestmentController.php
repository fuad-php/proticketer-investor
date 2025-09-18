<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investment;
use App\Models\Order;
use App\Models\Investor;
use Illuminate\Support\Facades\DB;

class InvestmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view investments')->only(['index', 'show']);
        $this->middleware('permission:create investments')->only(['create', 'store']);
        $this->middleware('permission:edit investments')->only(['edit', 'update']);
        $this->middleware('permission:delete investments')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $investments = Investment::with(['order', 'investor.user'])
            ->latest()
            ->paginate(15);

        return view('investments.index', compact('investments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $orders = Order::where('status', 'active')->get();
        $investors = Investor::where('is_active', true)->get();
        
        return view('investments.create', compact('orders', 'investors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'investor_id' => 'required|exists:investors,id',
            'amount' => 'required|numeric|min:0.01',
            'profit_percentage' => 'required|numeric|min:0|max:100',
            'investment_date' => 'required|date',
            'maturity_date' => 'required|date|after:investment_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::findOrFail($request->order_id);
            
            // Calculate expected profit
            $expectedProfit = ($request->amount * $request->profit_percentage) / 100;
            
            $investment = Investment::create([
                'order_id' => $request->order_id,
                'investor_id' => $request->investor_id,
                'amount' => $request->amount,
                'profit_percentage' => $request->profit_percentage,
                'expected_profit' => $expectedProfit,
                'investment_date' => $request->investment_date,
                'maturity_date' => $request->maturity_date,
                'notes' => $request->notes,
            ]);

            // Update investor totals
            $investor = Investor::findOrFail($request->investor_id);
            $investor->increment('total_invested', $request->amount);
            $investor->increment('current_balance', $request->amount);

            DB::commit();
            return redirect()->route('investments.show', $investment)
                ->with('success', 'Investment created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Failed to create investment: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Investment $investment)
    {
        $investment->load(['order', 'investor.user']);
        return view('investments.show', compact('investment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Investment $investment)
    {
        $orders = Order::where('status', 'active')->get();
        $investors = Investor::where('is_active', true)->get();
        
        return view('investments.edit', compact('investment', 'orders', 'investors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Investment $investment)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'investor_id' => 'required|exists:investors,id',
            'amount' => 'required|numeric|min:0.01',
            'profit_percentage' => 'required|numeric|min:0|max:100',
            'investment_date' => 'required|date',
            'maturity_date' => 'required|date|after:investment_date',
            'status' => 'required|in:active,matured,cancelled',
            'payment_status' => 'required|in:pending,partial,completed',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $oldAmount = $investment->amount;
            $newAmount = $request->amount;
            $amountDifference = $newAmount - $oldAmount;
            
            // Calculate expected profit
            $expectedProfit = ($request->amount * $request->profit_percentage) / 100;
            
            $investment->update([
                'order_id' => $request->order_id,
                'investor_id' => $request->investor_id,
                'amount' => $request->amount,
                'profit_percentage' => $request->profit_percentage,
                'expected_profit' => $expectedProfit,
                'investment_date' => $request->investment_date,
                'maturity_date' => $request->maturity_date,
                'status' => $request->status,
                'payment_status' => $request->payment_status,
                'notes' => $request->notes,
            ]);

            // Update investor totals if amount changed
            if ($amountDifference != 0) {
                $investor = Investor::findOrFail($request->investor_id);
                $investor->increment('total_invested', $amountDifference);
                $investor->increment('current_balance', $amountDifference);
            }

            DB::commit();
            return redirect()->route('investments.show', $investment)
                ->with('success', 'Investment updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Failed to update investment: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Investment $investment)
    {
        DB::beginTransaction();
        try {
            // Update investor totals
            $investor = $investment->investor;
            $investor->decrement('total_invested', $investment->amount);
            $investor->decrement('current_balance', $investment->amount);
            
            $investment->delete();
            
            DB::commit();
            return redirect()->route('investments.index')
                ->with('success', 'Investment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to delete investment: ' . $e->getMessage());
        }
    }
}
