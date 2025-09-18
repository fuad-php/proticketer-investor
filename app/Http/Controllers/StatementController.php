<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InvestmentStatement;
use App\Models\MoneyReceipt;
use App\Models\Transaction;
use App\Models\Investment;
use App\Models\Order;
use App\Services\PdfGenerationService;
use Illuminate\Support\Facades\DB;

class StatementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view statements|create statements|approve statements')->only(['index', 'show']);
        $this->middleware('permission:create statements')->only(['create', 'store']);
        $this->middleware('permission:approve statements')->only(['approve', 'publish']);
    }

    /**
     * Display a listing of investment statements
     */
    public function index(Request $request)
    {
        $query = InvestmentStatement::with(['investor', 'order', 'investment', 'creator', 'approver']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by investor
        if ($request->filled('investor_id')) {
            $query->where('investor_id', $request->investor_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('statement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('statement_date', '<=', $request->date_to);
        }

        $statements = $query->latest('statement_date')->paginate(20);

        // Get statistics
        $stats = [
            'total' => InvestmentStatement::count(),
            'draft' => InvestmentStatement::where('status', 'draft')->count(),
            'approved' => InvestmentStatement::where('status', 'approved')->count(),
            'published' => InvestmentStatement::where('status', 'published')->count(),
        ];

        return view('statements.index', compact('statements', 'stats'));
    }

    /**
     * Display the specified statement
     */
    public function show(InvestmentStatement $statement)
    {
        $statement->load(['investor', 'order', 'investment', 'creator', 'approver']);
        
        return view('statements.show', compact('statement'));
    }

    /**
     * Approve a statement
     */
    public function approve(Request $request, InvestmentStatement $statement)
    {
        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        
        // Check if user can approve
        if (!$user->hasRole(['director', 'managing_director', 'chairman'])) {
            return back()->with('error', 'You are not authorized to approve statements.');
        }

        DB::beginTransaction();
        
        try {
            $pdfService = new PdfGenerationService();
            $pdfService->approveAndPublishStatement($statement, $user);

            DB::commit();

            return redirect()->route('statements.show', $statement)
                ->with('success', 'Statement approved and published successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to approve statement: ' . $e->getMessage());
        }
    }

    /**
     * Generate statement for an investment
     */
    public function generateForInvestment(Investment $investment)
    {
        $user = auth()->user();
        
        // Check if user can create statements
        if (!$user->hasPermissionTo('create statements')) {
            return back()->with('error', 'You are not authorized to create statements.');
        }

        DB::beginTransaction();
        
        try {
            // Get the latest transaction for this investment
            $transaction = $investment->transactions()->latest()->first();
            
            if (!$transaction) {
                return back()->with('error', 'No transactions found for this investment.');
            }

            $pdfService = new PdfGenerationService();
            $statement = $pdfService->createInvestmentStatement($transaction);

            DB::commit();

            return redirect()->route('statements.show', $statement)
                ->with('success', 'Statement generated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to generate statement: ' . $e->getMessage());
        }
    }

    /**
     * Generate receipt for a transaction
     */
    public function generateReceiptForTransaction(Transaction $transaction)
    {
        $user = auth()->user();
        
        // Check if user can create receipts
        if (!$user->hasPermissionTo('create statements')) {
            return back()->with('error', 'You are not authorized to create receipts.');
        }

        DB::beginTransaction();
        
        try {
            $pdfService = new PdfGenerationService();
            $receipt = $pdfService->createMoneyReceipt($transaction);

            DB::commit();

            return redirect()->route('receipts.show', $receipt)
                ->with('success', 'Receipt generated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to generate receipt: ' . $e->getMessage());
        }
    }

    /**
     * Download statement PDF
     */
    public function download(InvestmentStatement $statement)
    {
        $pdfService = new PdfGenerationService();
        
        // Generate PDF if not exists
        if (!$statement->pdf_path) {
            $pdfService->generateInvestmentStatement($statement);
            $statement->refresh();
        }
        
        // Return PDF download
        if ($statement->pdf_path && \Storage::disk('public')->exists($statement->pdf_path)) {
            return \Storage::disk('public')->download($statement->pdf_path, "statement-{$statement->statement_number}.pdf");
        }
        
        return back()->with('error', 'PDF file not found.');
    }
}