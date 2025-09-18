<?php

namespace App\Services;

use App\Models\InvestmentStatement;
use App\Models\MoneyReceipt;
use App\Models\Company;
use App\Models\Transaction;
use App\Models\Investment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PdfGenerationService
{
    /**
     * Generate investment statement PDF
     */
    public function generateInvestmentStatement(InvestmentStatement $statement): string
    {
        $investor = $statement->investor;
        $company = Company::first();
        
        // Get transaction summary for the statement period
        $transactions = $investor->transactions()
            ->whereBetween('transaction_date', [$statement->period_start, $statement->period_end])
            ->orderBy('transaction_date')
            ->get();
        
        $transactionSummary = $transactions->map(function ($transaction) {
            return [
                'date' => $transaction->transaction_date->format('Y-m-d'),
                'type' => $transaction->type,
                'description' => $transaction->description,
                'amount' => $transaction->amount,
                'entry_type' => $transaction->entry_type,
                'balance_after' => $transaction->balance_after,
            ];
        })->toArray();
        
        // Update statement with transaction summary
        $statement->update(['transaction_summary' => $transactionSummary]);
        
        // Generate PDF
        $pdf = Pdf::loadView('pdfs.investor-statement', [
            'statement' => $statement,
            'investor' => $investor,
            'company' => $company,
        ]);
        
        // Set PDF options
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);
        
        // Generate filename
        $filename = "statement_{$statement->statement_number}_" . date('Y-m-d') . ".pdf";
        $filepath = "statements/{$filename}";
        
        // Save PDF to storage
        Storage::disk('public')->put($filepath, $pdf->output());
        
        // Update statement with PDF path
        $statement->update(['pdf_path' => $filepath]);
        
        return $filepath;
    }
    
    /**
     * Generate money receipt PDF
     */
    public function generateMoneyReceipt(MoneyReceipt $receipt): string
    {
        $investor = $receipt->investor;
        $company = Company::first();
        
        // Generate PDF
        $pdf = Pdf::loadView('pdfs.money-receipt', [
            'receipt' => $receipt,
            'investor' => $investor,
            'company' => $company,
        ]);
        
        // Set PDF options
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);
        
        // Generate filename
        $filename = "receipt_{$receipt->receipt_number}_" . date('Y-m-d') . ".pdf";
        $filepath = "receipts/{$filename}";
        
        // Save PDF to storage
        Storage::disk('public')->put($filepath, $pdf->output());
        
        // Update receipt with PDF path
        $receipt->update(['pdf_path' => $filepath]);
        
        return $filepath;
    }
    
    /**
     * Create investment statement from transaction
     */
    public function createInvestmentStatement(Transaction $transaction): InvestmentStatement
    {
        $investor = $transaction->investor;
        $investment = $transaction->investment;
        $order = $investment->order;
        
        // Calculate statement period (monthly)
        $statementDate = now();
        $periodStart = $statementDate->copy()->startOfMonth();
        $periodEnd = $statementDate->copy()->endOfMonth();
        
        // Get opening balance (previous month end)
        $previousStatement = $investor->statements()
            ->where('statement_date', '<', $periodStart)
            ->orderBy('statement_date', 'desc')
            ->first();
        
        $openingBalance = $previousStatement ? $previousStatement->closing_balance : 0;
        
        // Calculate totals for the period
        $periodTransactions = $investor->transactions()
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->get();
        
        $totalInvestments = $periodTransactions->where('type', 'investment')->sum('amount');
        $totalProfits = $periodTransactions->where('type', 'profit_payout')->sum('amount');
        $totalPayouts = $periodTransactions->where('type', 'principal_return')->sum('amount');
        $managementFees = $periodTransactions->where('type', 'fee')->where('description', 'like', '%management%')->sum('amount');
        $performanceFees = $periodTransactions->where('type', 'fee')->where('description', 'like', '%performance%')->sum('amount');
        
        $closingBalance = $openingBalance + $totalInvestments + $totalProfits - $totalPayouts - $managementFees - $performanceFees;
        
        // Create statement
        $statement = InvestmentStatement::create([
            'statement_number' => InvestmentStatement::generateStatementNumber(),
            'investor_id' => $investor->id,
            'order_id' => $order->id,
            'investment_id' => $investment->id,
            'statement_date' => $statementDate,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'total_investments' => $totalInvestments,
            'total_profits' => $totalProfits,
            'total_payouts' => $totalPayouts,
            'management_fees' => $managementFees,
            'performance_fees' => $performanceFees,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);
        
        return $statement;
    }
    
    /**
     * Create money receipt from transaction
     */
    public function createMoneyReceipt(Transaction $transaction): MoneyReceipt
    {
        $investor = $transaction->investor;
        
        // Determine receipt type based on transaction type
        $receiptType = match($transaction->type) {
            'investment' => 'investment',
            'profit_payout' => 'profit_payout',
            'principal_return' => 'principal_return',
            default => 'investment',
        };
        
        // Create receipt
        $receipt = MoneyReceipt::create([
            'receipt_number' => MoneyReceipt::generateReceiptNumber(),
            'investor_id' => $investor->id,
            'transaction_id' => $transaction->id,
            'investment_id' => $transaction->investment_id,
            'receipt_type' => $receiptType,
            'amount' => $transaction->amount,
            'payment_method' => 'Bank Transfer', // Default, can be updated
            'reference_number' => $transaction->reference_number,
            'receipt_date' => $transaction->transaction_date,
            'description' => $transaction->description,
            'is_verified' => false,
            'created_by' => auth()->id(),
        ]);
        
        return $receipt;
    }
    
    /**
     * Generate and store digital signature
     */
    public function generateDigitalSignature(string $text, string $signerName): string
    {
        // Create a simple digital signature image
        $width = 300;
        $height = 100;
        $image = imagecreate($width, $height);
        
        // Set colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $blue = imagecolorallocate($image, 37, 99, 235);
        
        // Fill background
        imagefill($image, 0, 0, $white);
        
        // Add border
        imagerectangle($image, 0, 0, $width-1, $height-1, $black);
        
        // Add text
        $font = 3; // Built-in font
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2 - 10;
        
        imagestring($image, $font, $x, $y, $text, $black);
        
        // Add signer name
        $signerWidth = imagefontwidth($font) * strlen($signerName);
        $signerX = ($width - $signerWidth) / 2;
        $signerY = $y + $textHeight + 5;
        
        imagestring($image, $font, $signerX, $signerY, $signerName, $blue);
        
        // Add date
        $date = date('Y-m-d H:i:s');
        $dateWidth = imagefontwidth($font) * strlen($date);
        $dateX = ($width - $dateWidth) / 2;
        $dateY = $signerY + $textHeight + 5;
        
        imagestring($image, $font, $dateX, $dateY, $date, $black);
        
        // Save image
        $filename = "signature_" . uniqid() . ".png";
        $filepath = "signatures/{$filename}";
        
        Storage::disk('public')->makeDirectory('signatures');
        imagepng($image, storage_path("app/public/{$filepath}"));
        imagedestroy($image);
        
        return $filepath;
    }
    
    /**
     * Generate company stamp
     */
    public function generateCompanyStamp(Company $company): string
    {
        $width = 200;
        $height = 200;
        $image = imagecreate($width, $height);
        
        // Set colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $red = imagecolorallocate($image, 220, 38, 38);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        // Fill background
        imagefill($image, 0, 0, $white);
        
        // Draw circle
        imageellipse($image, $width/2, $height/2, $width-20, $height-20, $red);
        imageellipse($image, $width/2, $height/2, $width-30, $height-30, $red);
        
        // Add company name
        $font = 3;
        $companyName = $company->name;
        $textWidth = imagefontwidth($font) * strlen($companyName);
        $textHeight = imagefontheight($font);
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2 - 20;
        
        imagestring($image, $font, $x, $y, $companyName, $black);
        
        // Add "OFFICIAL STAMP"
        $stampText = "OFFICIAL STAMP";
        $stampWidth = imagefontwidth($font) * strlen($stampText);
        $stampX = ($width - $stampWidth) / 2;
        $stampY = $y + $textHeight + 10;
        
        imagestring($image, $font, $stampX, $stampY, $stampText, $red);
        
        // Add date
        $date = date('Y-m-d');
        $dateWidth = imagefontwidth($font) * strlen($date);
        $dateX = ($width - $dateWidth) / 2;
        $dateY = $stampY + $textHeight + 10;
        
        imagestring($image, $font, $dateX, $dateY, $date, $black);
        
        // Save image
        $filename = "stamp_" . uniqid() . ".png";
        $filepath = "stamps/{$filename}";
        
        Storage::disk('public')->makeDirectory('stamps');
        imagepng($image, storage_path("app/public/{$filepath}"));
        imagedestroy($image);
        
        return $filepath;
    }
    
    /**
     * Approve and publish statement
     */
    public function approveAndPublishStatement(InvestmentStatement $statement, User $approver): void
    {
        DB::beginTransaction();
        
        try {
            // Generate digital signature
            $signaturePath = $this->generateDigitalSignature(
                "Approved by {$approver->name}",
                $approver->name
            );
            
            // Update statement
            $statement->update([
                'status' => 'published',
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'published_at' => now(),
                'digital_signature_path' => $signaturePath,
            ]);
            
            // Generate final PDF
            $this->generateInvestmentStatement($statement);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    /**
     * Verify money receipt
     */
    public function verifyMoneyReceipt(MoneyReceipt $receipt, User $verifier): void
    {
        DB::beginTransaction();
        
        try {
            // Generate digital signature
            $signaturePath = $this->generateDigitalSignature(
                "Verified by {$verifier->name}",
                $verifier->name
            );
            
            // Generate company stamp
            $company = Company::first();
            $stampPath = $this->generateCompanyStamp($company);
            
            // Update receipt
            $receipt->update([
                'is_verified' => true,
                'verified_by' => $verifier->id,
                'verified_at' => now(),
                'digital_signature_path' => $signaturePath,
                'company_stamp_path' => $stampPath,
            ]);
            
            // Generate final PDF
            $this->generateMoneyReceipt($receipt);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}