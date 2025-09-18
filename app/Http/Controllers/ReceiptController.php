<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MoneyReceipt;
use App\Models\Transaction;
use App\Services\PdfGenerationService;
use Illuminate\Support\Facades\DB;

class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view receipts|create receipts|verify receipts')->only(['index', 'show']);
        $this->middleware('permission:create receipts')->only(['create', 'store']);
        $this->middleware('permission:verify receipts')->only(['verify']);
    }

    /**
     * Display a listing of money receipts
     */
    public function index(Request $request)
    {
        $query = MoneyReceipt::with(['investor', 'transaction', 'investment', 'creator', 'verifier']);

        // Filter by status
        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->boolean('is_verified'));
        }

        // Filter by receipt type
        if ($request->filled('receipt_type')) {
            $query->where('receipt_type', $request->receipt_type);
        }

        // Filter by investor
        if ($request->filled('investor_id')) {
            $query->where('investor_id', $request->investor_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('receipt_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('receipt_date', '<=', $request->date_to);
        }

        $receipts = $query->latest('receipt_date')->paginate(20);

        // Get statistics
        $stats = [
            'total' => MoneyReceipt::count(),
            'verified' => MoneyReceipt::verified()->count(),
            'unverified' => MoneyReceipt::unverified()->count(),
            'investment_receipts' => MoneyReceipt::investment()->count(),
            'profit_payout_receipts' => MoneyReceipt::profitPayout()->count(),
        ];

        return view('receipts.index', compact('receipts', 'stats'));
    }

    /**
     * Display the specified receipt
     */
    public function show(MoneyReceipt $receipt)
    {
        $receipt->load(['investor', 'transaction', 'investment', 'creator', 'verifier']);
        
        return view('receipts.show', compact('receipt'));
    }

    /**
     * Verify a receipt
     */
    public function verify(Request $request, MoneyReceipt $receipt)
    {
        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        
        // Check if user can verify
        if (!$user->hasRole(['accounts', 'director', 'managing_director', 'chairman'])) {
            return back()->with('error', 'You are not authorized to verify receipts.');
        }

        if ($receipt->is_verified) {
            return back()->with('error', 'Receipt is already verified.');
        }

        DB::beginTransaction();
        
        try {
            $pdfService = new PdfGenerationService();
            $pdfService->verifyMoneyReceipt($receipt, $user);

            DB::commit();

            return redirect()->route('receipts.show', $receipt)
                ->with('success', 'Receipt verified successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to verify receipt: ' . $e->getMessage());
        }
    }

    /**
     * Download receipt PDF
     */
    public function download(MoneyReceipt $receipt)
    {
        $pdfService = new PdfGenerationService();
        
        // Generate PDF if not exists
        if (!$receipt->pdf_path) {
            $pdfService->generateMoneyReceipt($receipt);
            $receipt->refresh();
        }
        
        // Return PDF download
        if ($receipt->pdf_path && \Storage::disk('public')->exists($receipt->pdf_path)) {
            return \Storage::disk('public')->download($receipt->pdf_path, "receipt-{$receipt->receipt_number}.pdf");
        }
        
        return back()->with('error', 'PDF file not found.');
    }

    /**
     * Bulk generate receipts for transactions
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'exists:transactions,id',
        ]);

        $user = auth()->user();
        
        // Check if user can create receipts
        if (!$user->hasPermissionTo('create receipts')) {
            return back()->with('error', 'You are not authorized to create receipts.');
        }

        $generatedCount = 0;
        $errors = [];

        DB::beginTransaction();
        
        try {
            foreach ($request->transaction_ids as $transactionId) {
                try {
                    $transaction = Transaction::findOrFail($transactionId);
                    
                    // Check if receipt already exists
                    if ($transaction->receipts()->exists()) {
                        continue;
                    }

                    $pdfService = new PdfGenerationService();
                    $pdfService->createMoneyReceipt($transaction);
                    $generatedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Failed to generate receipt for transaction {$transactionId}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Successfully generated {$generatedCount} receipts.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to generate receipts: ' . $e->getMessage());
        }
    }
}