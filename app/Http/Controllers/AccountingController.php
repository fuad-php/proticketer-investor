<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountingLedger;
use App\Models\Investor;
use App\Models\Client;
use App\Models\Order;
use App\Models\Investment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AccountingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view accounting')->only(['index', 'show', 'reports']);
        $this->middleware('permission:create accounting')->only(['create', 'store']);
        $this->middleware('permission:approve accounting')->only(['approve', 'reject']);
        $this->middleware('permission:reverse accounting')->only(['reverse']);
    }

    public function index(Request $request)
    {
        $query = AccountingLedger::with(['investor', 'client', 'createdBy', 'approvedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by transaction type
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        // Filter by investor
        if ($request->filled('investor_id')) {
            $query->where('investor_id', $request->investor_id);
        }

        // Filter by client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Search by description or transaction ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest('transaction_date')->paginate(20);

        // Get statistics
        $stats = $this->getAccountingStats($request);

        // Get filter options
        $investors = Investor::where('is_active', true)->get();
        $clients = Client::where('is_active', true)->get();

        return view('accounting.index', compact('transactions', 'stats', 'investors', 'clients'));
    }

    public function create()
    {
        $investors = Investor::where('is_active', true)->get();
        $clients = Client::where('is_active', true)->get();
        $orders = Order::where('status', 'active')->get();
        $investments = Investment::where('status', 'active')->get();

        return view('accounting.create', compact('investors', 'clients', 'orders', 'investments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|string|in:investment,payout,fee,expense,revenue,adjustment',
            'category' => 'required|string',
            'description' => 'required|string|max:255',
            'debit_amount' => 'required_without:credit_amount|numeric|min:0',
            'credit_amount' => 'required_without:debit_amount|numeric|min:0',
            'investor_id' => 'nullable|exists:investors,id',
            'client_id' => 'nullable|exists:clients,id',
            'payment_method' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Calculate new balance
            $currentBalance = AccountingLedger::getCurrentBalance();
            $newBalance = $currentBalance + $request->credit_amount - $request->debit_amount;

            $transaction = AccountingLedger::create([
                'transaction_id' => AccountingLedger::generateTransactionId(),
                'transaction_date' => $request->transaction_date,
                'transaction_type' => $request->transaction_type,
                'category' => $request->category,
                'description' => $request->description,
                'debit_amount' => $request->debit_amount ?? 0,
                'credit_amount' => $request->credit_amount ?? 0,
                'balance' => $newBalance,
                'reference_type' => $request->reference_type,
                'reference_id' => $request->reference_id,
                'investor_id' => $request->investor_id,
                'client_id' => $request->client_id,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
                'status' => 'pending',
            ]);

            DB::commit();

            return redirect()->route('accounting.show', $transaction)
                ->with('success', 'Transaction created successfully and pending approval.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Failed to create transaction: ' . $e->getMessage());
        }
    }

    public function show(AccountingLedger $transaction)
    {
        $transaction->load(['investor', 'client', 'createdBy', 'approvedBy', 'reversedBy']);
        return view('accounting.show', compact('transaction'));
    }

    public function approve(AccountingLedger $transaction)
    {
        if (!$transaction->isPending()) {
            return back()->with('error', 'Only pending transactions can be approved.');
        }

        $transaction->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Transaction approved successfully.');
    }

    public function reject(AccountingLedger $transaction)
    {
        if (!$transaction->isPending()) {
            return back()->with('error', 'Only pending transactions can be rejected.');
        }

        $transaction->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Transaction rejected successfully.');
    }

    public function reverse(AccountingLedger $transaction, Request $request)
    {
        $request->validate([
            'reversal_reason' => 'required|string|max:255',
        ]);

        if (!$transaction->isApproved()) {
            return back()->with('error', 'Only approved transactions can be reversed.');
        }

        DB::beginTransaction();
        try {
            // Create reversal entry
            $currentBalance = AccountingLedger::getCurrentBalance();
            $reversalBalance = $currentBalance - $transaction->credit_amount + $transaction->debit_amount;

            AccountingLedger::create([
                'transaction_id' => AccountingLedger::generateTransactionId(),
                'transaction_date' => now()->toDateString(),
                'transaction_type' => 'adjustment',
                'category' => 'reversal',
                'description' => 'Reversal of ' . $transaction->transaction_id . ': ' . $request->reversal_reason,
                'debit_amount' => $transaction->credit_amount,
                'credit_amount' => $transaction->debit_amount,
                'balance' => $reversalBalance,
                'reference_type' => 'accounting_ledger',
                'reference_id' => $transaction->id,
                'investor_id' => $transaction->investor_id,
                'client_id' => $transaction->client_id,
                'notes' => $request->reversal_reason,
                'created_by' => Auth::id(),
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Mark original transaction as reversed
            $transaction->update([
                'status' => 'reversed',
                'reversal_reason' => $request->reversal_reason,
                'reversed_by' => Auth::id(),
                'reversed_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Transaction reversed successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to reverse transaction: ' . $e->getMessage());
        }
    }

    public function reports(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        // Get comprehensive reports
        $reports = [
            'income_statement' => $this->getIncomeStatement($startDate, $endDate),
            'balance_sheet' => $this->getBalanceSheet($endDate),
            'cash_flow' => $this->getCashFlow($startDate, $endDate),
            'investor_summary' => $this->getInvestorSummary($startDate, $endDate),
            'client_summary' => $this->getClientSummary($startDate, $endDate),
            'category_breakdown' => $this->getCategoryBreakdown($startDate, $endDate),
            'monthly_trends' => $this->getMonthlyTrends($startDate, $endDate),
        ];

        return view('accounting.reports', compact('reports', 'startDate', 'endDate'));
    }

    private function getAccountingStats($request)
    {
        $query = AccountingLedger::approved();
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        return [
            'total_transactions' => $query->count(),
            'total_debits' => $query->sum('debit_amount'),
            'total_credits' => $query->sum('credit_amount'),
            'current_balance' => AccountingLedger::getCurrentBalance(),
            'pending_transactions' => AccountingLedger::pending()->count(),
            'rejected_transactions' => AccountingLedger::rejected()->count(),
        ];
    }

    private function getIncomeStatement($startDate, $endDate)
    {
        $revenues = AccountingLedger::approved()
            ->byDateRange($startDate, $endDate)
            ->whereIn('category', ['investment_income', 'management_fee', 'performance_fee'])
            ->sum('credit_amount');

        $expenses = AccountingLedger::approved()
            ->byDateRange($startDate, $endDate)
            ->where('category', 'operating_expense')
            ->sum('debit_amount');

        return [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'net_income' => $revenues - $expenses,
        ];
    }

    private function getBalanceSheet($date)
    {
        $assets = AccountingLedger::approved()
            ->where('transaction_date', '<=', $date)
            ->whereIn('category', ['investment_income', 'management_fee', 'performance_fee'])
            ->sum('credit_amount');

        $liabilities = AccountingLedger::approved()
            ->where('transaction_date', '<=', $date)
            ->where('category', 'operating_expense')
            ->sum('debit_amount');

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $assets - $liabilities,
        ];
    }

    private function getCashFlow($startDate, $endDate)
    {
        $cashInflows = AccountingLedger::approved()
            ->byDateRange($startDate, $endDate)
            ->whereIn('transaction_type', ['investment', 'revenue'])
            ->sum('credit_amount');

        $cashOutflows = AccountingLedger::approved()
            ->byDateRange($startDate, $endDate)
            ->whereIn('transaction_type', ['payout', 'expense'])
            ->sum('debit_amount');

        return [
            'inflows' => $cashInflows,
            'outflows' => $cashOutflows,
            'net_cash_flow' => $cashInflows - $cashOutflows,
        ];
    }

    private function getInvestorSummary($startDate, $endDate)
    {
        return AccountingLedger::approved()
            ->byDateRange($startDate, $endDate)
            ->whereNotNull('investor_id')
            ->with('investor')
            ->get()
            ->groupBy('investor_id')
            ->map(function ($transactions) {
                $investor = $transactions->first()->investor;
                return [
                    'investor' => $investor,
                    'total_investments' => $transactions->where('transaction_type', 'investment')->sum('credit_amount'),
                    'total_payouts' => $transactions->where('transaction_type', 'payout')->sum('debit_amount'),
                    'total_fees' => $transactions->whereIn('category', ['management_fee', 'performance_fee'])->sum('debit_amount'),
                    'net_position' => $transactions->sum('credit_amount') - $transactions->sum('debit_amount'),
                ];
            });
    }

    private function getClientSummary($startDate, $endDate)
    {
        return AccountingLedger::approved()
            ->byDateRange($startDate, $endDate)
            ->whereNotNull('client_id')
            ->with('client')
            ->get()
            ->groupBy('client_id')
            ->map(function ($transactions) {
                $client = $transactions->first()->client;
                return [
                    'client' => $client,
                    'total_transactions' => $transactions->count(),
                    'total_amount' => $transactions->sum('credit_amount') - $transactions->sum('debit_amount'),
                ];
            });
    }

    private function getCategoryBreakdown($startDate, $endDate)
    {
        return AccountingLedger::approved()
            ->byDateRange($startDate, $endDate)
            ->selectRaw('category, SUM(credit_amount) as total_credits, SUM(debit_amount) as total_debits')
            ->groupBy('category')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category,
                    'total_credits' => $item->total_credits,
                    'total_debits' => $item->total_debits,
                    'net_amount' => $item->total_credits - $item->total_debits,
                ];
            });
    }

    private function getMonthlyTrends($startDate, $endDate)
    {
        return AccountingLedger::approved()
            ->byDateRange($startDate, $endDate)
            ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as month, SUM(credit_amount) as credits, SUM(debit_amount) as debits')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month,
                    'credits' => $item->credits,
                    'debits' => $item->debits,
                    'net' => $item->credits - $item->debits,
                ];
            });
    }
}
