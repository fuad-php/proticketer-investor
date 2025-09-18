<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investor;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Order;
use App\Models\InvestmentStatement;
use App\Models\MoneyReceipt;
use App\Services\PdfGenerationService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvestorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view investors')->only(['index', 'show']);
        $this->middleware('permission:create investors')->only(['create', 'store']);
        $this->middleware('permission:edit investors')->only(['edit', 'update']);
        $this->middleware('permission:delete investors')->only(['destroy']);
        $this->middleware('role:investor')->only(['statements', 'downloadStatement', 'receipts', 'downloadReceipt']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $investors = Investor::with(['user', 'investments'])
            ->latest()
            ->paginate(15);

        return view('investors.index', compact('investors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::whereDoesntHave('investor')->get();
        
        return view('investors.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:investors,user_id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:investors,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'nid_number' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:50',
            'bank_routing' => 'nullable|string|max:20',
        ]);

        $investor = Investor::create([
            'user_id' => $request->user_id,
            'investor_code' => 'INV-' . strtoupper(Str::random(6)),
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'nid_number' => $request->nid_number,
            'bank_name' => $request->bank_name,
            'bank_account' => $request->bank_account,
            'bank_routing' => $request->bank_routing,
            'is_active' => true,
        ]);

        // Assign investor role to user
        $user = User::find($request->user_id);
        $user->assignRole('investor');

        return redirect()->route('investors.show', $investor)
            ->with('success', 'Investor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Investor $investor)
    {
        $investor->load(['user', 'investments.order', 'transactions']);
        
        $stats = [
            'total_invested' => $investor->total_invested,
            'total_profit' => $investor->total_profit,
            'current_balance' => $investor->current_balance,
            'active_investments' => $investor->investments()->where('status', 'active')->count(),
        ];

        $recent_investments = $investor->investments()
            ->with(['order'])
            ->latest()
            ->limit(5)
            ->get();

        $recent_transactions = $investor->transactions()
            ->latest()
            ->limit(10)
            ->get();

        return view('investors.show', compact('investor', 'stats', 'recent_investments', 'recent_transactions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Investor $investor)
    {
        return view('investors.edit', compact('investor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Investor $investor)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:investors,email,' . $investor->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'nid_number' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:50',
            'bank_routing' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $investor->update($request->all());

        return redirect()->route('investors.show', $investor)
            ->with('success', 'Investor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Investor $investor)
    {
        if ($investor->investments()->count() > 0) {
            return redirect()->route('investors.index')
                ->with('error', 'Cannot delete investor with existing investments.');
        }

        $investor->delete();

        return redirect()->route('investors.index')
            ->with('success', 'Investor deleted successfully.');
    }

    /**
     * Show investor statements
     */
    public function statements(Request $request)
    {
        $user = auth()->user();
        $investor = $user->investor;
        
        if (!$investor) {
            return redirect()->route('profile.edit')->with('error', 'Please complete your investor profile.');
        }

        $query = $investor->statements()->with(['order', 'investment']);

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('statement_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('statement_date', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $statements = $query->latest('statement_date')->paginate(20);

        // Get summary statistics
        $summary = [
            'total_statements' => $investor->statements()->count(),
            'published_statements' => $investor->statements()->published()->count(),
            'total_investments' => $investor->statements()->sum('total_investments'),
            'total_profits' => $investor->statements()->sum('total_profits'),
            'total_payouts' => $investor->statements()->sum('total_payouts'),
        ];

        return view('investor.statements', compact('statements', 'investor', 'summary'));
    }

    /**
     * Download investor statement as PDF
     */
    public function downloadStatement($id)
    {
        $user = auth()->user();
        $investor = $user->investor;
        
        if (!$investor) {
            abort(404);
        }

        $statement = $investor->statements()->findOrFail($id);
        
        // Generate PDF if not exists
        if (!$statement->pdf_path) {
            $pdfService = new PdfGenerationService();
            $pdfService->generateInvestmentStatement($statement);
            $statement->refresh();
        }
        
        // Return PDF download
        if ($statement->pdf_path && \Storage::disk('public')->exists($statement->pdf_path)) {
            return \Storage::disk('public')->download($statement->pdf_path, "statement-{$statement->statement_number}.pdf");
        }
        
        // Fallback: generate on-the-fly
        $pdf = Pdf::loadView('pdfs.investor-statement', [
            'statement' => $statement,
            'investor' => $investor,
            'company' => \App\Models\Company::first(),
        ]);
        
        return $pdf->download("statement-{$statement->statement_number}.pdf");
    }

    /**
     * Show investor receipts
     */
    public function receipts()
    {
        $user = auth()->user();
        $investor = $user->investor;
        
        if (!$investor) {
            return redirect()->route('profile.edit')->with('error', 'Please complete your investor profile.');
        }

        $receipts = $investor->transactions()
            ->where('type', 'investment')
            ->with(['investment.order'])
            ->latest()
            ->paginate(20);

        return view('investor.receipts', compact('receipts', 'investor'));
    }

    /**
     * Download money receipt as PDF
     */
    public function downloadReceipt($id)
    {
        $user = auth()->user();
        $investor = $user->investor;
        
        if (!$investor) {
            abort(404);
        }

        $receipt = $investor->receipts()->findOrFail($id);
        
        // Generate PDF if not exists
        if (!$receipt->pdf_path) {
            $pdfService = new PdfGenerationService();
            $pdfService->generateMoneyReceipt($receipt);
            $receipt->refresh();
        }
        
        // Return PDF download
        if ($receipt->pdf_path && \Storage::disk('public')->exists($receipt->pdf_path)) {
            return \Storage::disk('public')->download($receipt->pdf_path, "receipt-{$receipt->receipt_number}.pdf");
        }
        
        // Fallback: generate on-the-fly
        $pdf = Pdf::loadView('pdfs.money-receipt', [
            'receipt' => $receipt,
            'investor' => $investor,
            'company' => \App\Models\Company::first(),
        ]);
        
        return $pdf->download("receipt-{$receipt->receipt_number}.pdf");
    }

    /**
     * Show comprehensive investor dashboard
     */
    public function dashboard()
    {
        $user = auth()->user();
        $investor = $user->investor;
        
        if (!$investor) {
            return redirect()->route('profile.edit')->with('error', 'Please complete your investor profile.');
        }

        // Get comprehensive statistics
        $stats = $this->getInvestorStats($investor);
        
        // Get investment timeline
        $timeline = $this->getInvestmentTimeline($investor);
        
        // Get recent transactions
        $recentTransactions = $investor->transactions()
            ->with(['investment.order', 'investment'])
            ->latest()
            ->limit(10)
            ->get();
        
        // Get active investments
        $activeInvestments = $investor->activeInvestments()
            ->with(['order'])
            ->get();
        
        // Get upcoming payouts
        $upcomingPayouts = $investor->investments()
            ->where('next_payout_date', '<=', now()->addDays(30))
            ->where('status', 'active')
            ->with(['order'])
            ->get();
        
        // Get performance metrics
        $performance = $this->getPerformanceMetrics($investor);

        return view('investor.dashboard', compact(
            'investor', 
            'stats', 
            'timeline', 
            'recentTransactions', 
            'activeInvestments', 
            'upcomingPayouts', 
            'performance'
        ));
    }

    /**
     * Get investor statistics
     */
    private function getInvestorStats($investor)
    {
        return [
            'total_invested' => $investor->total_invested,
            'total_profit' => $investor->total_profit,
            'current_balance' => $investor->current_balance,
            'total_current_value' => $investor->total_current_value,
            'total_return_percentage' => $investor->total_return_percentage,
            'active_investments_count' => $investor->activeInvestments()->count(),
            'matured_investments_count' => $investor->maturedInvestments()->count(),
            'total_transactions_count' => $investor->transactions()->count(),
            'total_statements_count' => $investor->statements()->published()->count(),
            'total_receipts_count' => $investor->receipts()->verified()->count(),
        ];
    }

    /**
     * Get investment timeline
     */
    private function getInvestmentTimeline($investor)
    {
        $timeline = collect();
        
        // Add investments
        $investments = $investor->investments()
            ->with(['order'])
            ->orderBy('investment_date')
            ->get();
            
        foreach ($investments as $investment) {
            $timeline->push([
                'type' => 'investment',
                'date' => $investment->investment_date,
                'title' => "Investment in {$investment->order->title}",
                'amount' => $investment->amount,
                'status' => $investment->status,
                'data' => $investment,
            ]);
        }
        
        // Add payouts
        $payouts = $investor->transactions()
            ->where('type', 'profit_payout')
            ->with(['investment.order'])
            ->orderBy('transaction_date')
            ->get();
            
        foreach ($payouts as $payout) {
            $timeline->push([
                'type' => 'payout',
                'date' => $payout->transaction_date,
                'title' => "Profit Payout from {$payout->investment->order->title}",
                'amount' => $payout->amount,
                'status' => 'completed',
                'data' => $payout,
            ]);
        }
        
        // Add statements
        $statements = $investor->statements()
            ->published()
            ->with(['order'])
            ->orderBy('statement_date')
            ->get();
            
        foreach ($statements as $statement) {
            $timeline->push([
                'type' => 'statement',
                'date' => $statement->statement_date,
                'title' => "Statement Published for {$statement->order->title}",
                'amount' => $statement->total_profits,
                'status' => 'published',
                'data' => $statement,
            ]);
        }
        
        return $timeline->sortBy('date')->values();
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics($investor)
    {
        $investments = $investor->investments();
        
        return [
            'average_return_percentage' => $investments->avg('return_percentage') ?? 0,
            'best_performing_investment' => $investments->orderBy('return_percentage', 'desc')->first(),
            'worst_performing_investment' => $investments->orderBy('return_percentage', 'asc')->first(),
            'total_fees_paid' => $investments->sum('management_fee') + $investments->sum('performance_fee'),
            'net_profit_after_fees' => $investments->sum('net_profit'),
            'investment_duration_avg' => $this->calculateAverageInvestmentDuration($investments),
        ];
    }

    /**
     * Calculate average investment duration
     */
    private function calculateAverageInvestmentDuration($investments)
    {
        $totalDays = 0;
        $count = 0;
        
        foreach ($investments->get() as $investment) {
            $days = $investment->investment_date->diffInDays($investment->maturity_date);
            $totalDays += $days;
            $count++;
        }
        
        return $count > 0 ? round($totalDays / $count) : 0;
    }

    /**
     * Export investor data as CSV
     */
    public function exportData()
    {
        $user = auth()->user();
        $investor = $user->investor;
        
        if (!$investor) {
            abort(404);
        }

        $data = [
            'investments' => $investor->investments()->with(['order'])->get(),
            'transactions' => $investor->transactions()->with(['investment.order'])->get(),
            'statements' => $investor->statements()->published()->with(['order'])->get(),
            'receipts' => $investor->receipts()->verified()->get(),
        ];

        $filename = "investor_data_{$investor->investor_code}_" . date('Y-m-d') . ".csv";
        
        return response()->streamDownload(function() use ($data) {
            $handle = fopen('php://output', 'w');
            
            // Write investments
            fputcsv($handle, ['Type', 'Date', 'Description', 'Amount', 'Status']);
            foreach ($data['investments'] as $investment) {
                fputcsv($handle, [
                    'Investment',
                    $investment->investment_date->format('Y-m-d'),
                    $investment->order->title,
                    $investment->amount,
                    $investment->status,
                ]);
            }
            
            // Write transactions
            fputcsv($handle, ['Type', 'Date', 'Description', 'Amount', 'Balance After']);
            foreach ($data['transactions'] as $transaction) {
                fputcsv($handle, [
                    $transaction->type,
                    $transaction->transaction_date->format('Y-m-d'),
                    $transaction->description,
                    $transaction->amount,
                    $transaction->balance_after,
                ]);
            }
            
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
