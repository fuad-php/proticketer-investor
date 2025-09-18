<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Investor;
use App\Models\Investment;
use App\Models\Order;
use App\Notifications\ProfitPayout;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view transactions')->only(['index', 'show']);
        $this->middleware('permission:create transactions')->only(['create', 'store']);
        $this->middleware('permission:edit transactions')->only(['edit', 'update']);
        $this->middleware('permission:delete transactions')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['investor', 'investment.order', 'creator']);

        // Filter by investor
        if ($request->filled('investor_id')) {
            $query->where('investor_id', $request->investor_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        $transactions = $query->latest('transaction_date')->paginate(20);
        $investors = Investor::where('is_active', true)->get();

        return view('transactions.index', compact('transactions', 'investors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $investors = Investor::where('is_active', true)->get();
        $investments = Investment::where('status', 'active')->get();
        $orders = Order::where('status', 'active')->get();
        
        return view('transactions.create', compact('investors', 'investments', 'orders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'investor_id' => 'required|exists:investors,id',
            'investment_id' => 'nullable|exists:investments,id',
            'order_id' => 'nullable|exists:orders,id',
            'type' => 'required|in:investment,profit_payout,principal_return,fee,adjustment',
            'entry_type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'reference_number' => 'nullable|string|max:100',
            'transaction_date' => 'required|date',
        ]);

        DB::beginTransaction();
        
        try {
            $investor = Investor::findOrFail($request->investor_id);
            
            // Calculate balance after transaction
            $balanceAfter = $request->entry_type === 'credit' 
                ? $investor->current_balance + $request->amount
                : $investor->current_balance - $request->amount;

            // Create transaction
            $transaction = Transaction::create([
                'transaction_id' => 'TXN-' . strtoupper(Str::random(8)),
                'investor_id' => $request->investor_id,
                'investment_id' => $request->investment_id,
                'order_id' => $request->order_id,
                'type' => $request->type,
                'entry_type' => $request->entry_type,
                'amount' => $request->amount,
                'balance_after' => $balanceAfter,
                'description' => $request->description,
                'reference_number' => $request->reference_number,
                'created_by' => auth()->id(),
                'transaction_date' => $request->transaction_date,
            ]);

            // Update investor balance
            if ($request->entry_type === 'credit') {
                $investor->increment('current_balance', $request->amount);
                
                // Update profit if it's a profit payout
                if ($request->type === 'profit_payout') {
                    $investor->increment('total_profit', $request->amount);
                    
                    // Notify investor about profit payout
                    if ($investor->user) {
                        $investor->user->notify(new ProfitPayout($transaction));
                    }
                }
            } else {
                $investor->decrement('current_balance', $request->amount);
            }

            DB::commit();

            return redirect()->route('transactions.show', $transaction)
                ->with('success', 'Transaction created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Failed to create transaction: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $transaction->load(['investor', 'investment.order', 'order', 'creator']);
        
        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        $investors = Investor::where('is_active', true)->get();
        $investments = Investment::where('status', 'active')->get();
        $orders = Order::where('status', 'active')->get();
        
        return view('transactions.edit', compact('transaction', 'investors', 'investments', 'orders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'investor_id' => 'required|exists:investors,id',
            'investment_id' => 'nullable|exists:investments,id',
            'order_id' => 'nullable|exists:orders,id',
            'type' => 'required|in:investment,profit_payout,principal_return,fee,adjustment',
            'entry_type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'reference_number' => 'nullable|string|max:100',
            'transaction_date' => 'required|date',
        ]);

        DB::beginTransaction();
        
        try {
            $investor = Investor::findOrFail($request->investor_id);
            
            // Revert old transaction effects
            if ($transaction->entry_type === 'credit') {
                $investor->decrement('current_balance', $transaction->amount);
                if ($transaction->type === 'profit_payout') {
                    $investor->decrement('total_profit', $transaction->amount);
                }
            } else {
                $investor->increment('current_balance', $transaction->amount);
            }

            // Calculate new balance after transaction
            $balanceAfter = $request->entry_type === 'credit' 
                ? $investor->current_balance + $request->amount
                : $investor->current_balance - $request->amount;

            // Update transaction
            $transaction->update([
                'investor_id' => $request->investor_id,
                'investment_id' => $request->investment_id,
                'order_id' => $request->order_id,
                'type' => $request->type,
                'entry_type' => $request->entry_type,
                'amount' => $request->amount,
                'balance_after' => $balanceAfter,
                'description' => $request->description,
                'reference_number' => $request->reference_number,
                'transaction_date' => $request->transaction_date,
            ]);

            // Apply new transaction effects
            if ($request->entry_type === 'credit') {
                $investor->increment('current_balance', $request->amount);
                if ($request->type === 'profit_payout') {
                    $investor->increment('total_profit', $request->amount);
                }
            } else {
                $investor->decrement('current_balance', $request->amount);
            }

            DB::commit();

            return redirect()->route('transactions.show', $transaction)
                ->with('success', 'Transaction updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Failed to update transaction: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        DB::beginTransaction();
        
        try {
            $investor = $transaction->investor;
            
            // Revert transaction effects
            if ($transaction->entry_type === 'credit') {
                $investor->decrement('current_balance', $transaction->amount);
                if ($transaction->type === 'profit_payout') {
                    $investor->decrement('total_profit', $transaction->amount);
                }
            } else {
                $investor->increment('current_balance', $transaction->amount);
            }

            $transaction->delete();

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('transactions.index')
                ->with('error', 'Failed to delete transaction: ' . $e->getMessage());
        }
    }
}
