<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Investment;
use App\Models\Investor;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Approval;
use App\Models\AccountingLedger;
use App\Models\InvestmentStatement;
use App\Models\MoneyReceipt;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view reports|export reports');
    }

    public function index()
    {
        $stats = $this->getDashboardStats();
        $recentActivity = $this->getRecentActivity();
        $performanceMetrics = $this->getPerformanceMetrics();
        $trends = $this->getTrends();
        
        return view('reports.index', compact('stats', 'recentActivity', 'performanceMetrics', 'trends'));
    }

    public function investorStatements(Request $request)
    {
        $query = InvestmentStatement::with(['investor', 'order', 'investment']);

        if ($request->filled('investor_id')) {
            $query->where('investor_id', $request->investor_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('statement_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $statements = $query->latest('statement_date')->paginate(20);
        $investors = Investor::where('is_active', true)->get();

        return view('reports.investor-statements', compact('statements', 'investors'));
    }

    public function profitSummary(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', now()->endOfYear()->toDateString());

        $summary = $this->getProfitSummary($startDate, $endDate);
        $monthlyBreakdown = $this->getMonthlyProfitBreakdown($startDate, $endDate);
        $investorBreakdown = $this->getInvestorProfitBreakdown($startDate, $endDate);
        $categoryBreakdown = $this->getCategoryProfitBreakdown($startDate, $endDate);

        return view('reports.profit-summary', compact('summary', 'monthlyBreakdown', 'investorBreakdown', 'categoryBreakdown', 'startDate', 'endDate'));
    }

    public function cashflow(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', now()->endOfYear()->toDateString());

        $cashflow = $this->getCashflowReport($startDate, $endDate);
        $monthlyCashflow = $this->getMonthlyCashflow($startDate, $endDate);
        $projectedCashflow = $this->getProjectedCashflow($endDate);

        return view('reports.cashflow', compact('cashflow', 'monthlyCashflow', 'projectedCashflow', 'startDate', 'endDate'));
    }

    public function outstandingPayments(Request $request)
    {
        $query = Investment::with(['investor', 'order'])
            ->where('status', 'active')
            ->where('next_payout_date', '<=', now());

        if ($request->filled('investor_id')) {
            $query->where('investor_id', $request->investor_id);
        }

        if ($request->filled('overdue_only')) {
            $query->where('next_payout_date', '<', now());
        }

        $outstandingPayments = $query->get();
        $investors = Investor::where('is_active', true)->get();

        return view('reports.outstanding-payments', compact('outstandingPayments', 'investors'));
    }

    public function approvalStatus(Request $request)
    {
        $query = Approval::with(['order.client', 'order.creator', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('approver_role')) {
            $query->where('approver_role', $request->approver_role);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $approvals = $query->latest()->paginate(20);
        $stats = $this->getApprovalStats();

        return view('reports.approval-status', compact('approvals', 'stats'));
    }

    public function clientInquiries(Request $request)
    {
        $query = Inquiry::with(['client', 'assignedUser']);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $inquiries = $query->latest()->paginate(20);
        $clients = Client::where('is_active', true)->get();
        $stats = $this->getInquiryStats();

        return view('reports.client-inquiries', compact('inquiries', 'clients', 'stats'));
    }

    public function exportPdf(Request $request)
    {
        $reportType = $request->get('report_type', 'profit-summary');
        $startDate = $request->get('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', now()->endOfYear()->toDateString());

        $data = $this->getReportData($reportType, $startDate, $endDate);
        $company = \App\Models\Company::first();

        $pdf = Pdf::loadView("reports.pdf.{$reportType}", compact('data', 'company', 'startDate', 'endDate'));
        
        return $pdf->download("{$reportType}-report-{$startDate}-to-{$endDate}.pdf");
    }

    public function exportCsv(Request $request)
    {
        $reportType = $request->get('report_type', 'profit-summary');
        $startDate = $request->get('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', now()->endOfYear()->toDateString());

        $data = $this->getReportData($reportType, $startDate, $endDate);

        $filename = "{$reportType}-report-{$startDate}-to-{$endDate}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data, $reportType) {
            $file = fopen('php://output', 'w');
            
            // Write headers based on report type
            $this->writeCsvHeaders($file, $reportType);
            
            // Write data based on report type
            $this->writeCsvData($file, $data, $reportType);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getDashboardStats()
    {
        return [
            'total_investors' => Investor::where('is_active', true)->count(),
            'total_clients' => Client::where('is_active', true)->count(),
            'total_orders' => Order::count(),
            'active_investments' => Investment::where('status', 'active')->count(),
            'total_invested' => Investment::where('status', 'active')->sum('amount'),
            'total_profits' => Investment::where('status', 'active')->sum('actual_profit'),
            'pending_approvals' => Approval::where('status', 'pending')->count(),
            'pending_inquiries' => Inquiry::where('status', 'received')->count(),
            'monthly_revenue' => AccountingLedger::approved()
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->whereIn('category', ['investment_income', 'management_fee', 'performance_fee'])
                ->sum('credit_amount'),
            'monthly_expenses' => AccountingLedger::approved()
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->where('category', 'operating_expense')
                ->sum('debit_amount'),
        ];
    }

    private function getRecentActivity()
    {
        return [
            'recent_investments' => Investment::with('investor')->latest()->limit(5)->get(),
            'recent_orders' => Order::with('client')->latest()->limit(5)->get(),
            'recent_approvals' => Approval::with(['order.client', 'approver'])->latest()->limit(5)->get(),
            'recent_inquiries' => Inquiry::with('client')->latest()->limit(5)->get(),
        ];
    }

    private function getPerformanceMetrics()
    {
        $totalInvested = Investment::where('status', 'active')->sum('amount');
        $totalProfits = Investment::where('status', 'active')->sum('actual_profit');
        $averageReturn = $totalInvested > 0 ? ($totalProfits / $totalInvested) * 100 : 0;

        return [
            'average_return_percentage' => $averageReturn,
            'best_performing_investment' => Investment::where('status', 'active')
                ->orderBy('return_percentage', 'desc')
                ->first(),
            'worst_performing_investment' => Investment::where('status', 'active')
                ->orderBy('return_percentage', 'asc')
                ->first(),
            'total_fees_collected' => AccountingLedger::approved()
                ->whereIn('category', ['management_fee', 'performance_fee'])
                ->sum('credit_amount'),
            'average_investment_duration' => $this->calculateAverageInvestmentDuration(),
        ];
    }

    private function getTrends()
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('Y-m'));
        }

        $monthlyData = $months->map(function ($month) {
            $date = Carbon::createFromFormat('Y-m', $month);
            return [
                'month' => $date->format('M Y'),
                'investments' => Investment::whereYear('investment_date', $date->year)
                    ->whereMonth('investment_date', $date->month)
                    ->sum('amount'),
                'profits' => Investment::whereYear('investment_date', $date->year)
                    ->whereMonth('investment_date', $date->month)
                    ->sum('actual_profit'),
                'orders' => Order::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        });

        return $monthlyData;
    }

    private function getProfitSummary($startDate, $endDate)
    {
        return [
            'total_revenue' => AccountingLedger::approved()
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->whereIn('category', ['investment_income', 'management_fee', 'performance_fee'])
                ->sum('credit_amount'),
            'total_expenses' => AccountingLedger::approved()
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('category', 'operating_expense')
                ->sum('debit_amount'),
            'net_profit' => 0, // Will be calculated
            'gross_margin' => 0, // Will be calculated
            'operating_margin' => 0, // Will be calculated
        ];
    }

    private function getMonthlyProfitBreakdown($startDate, $endDate)
    {
        return AccountingLedger::approved()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as month, 
                        SUM(CASE WHEN category IN ("investment_income", "management_fee", "performance_fee") THEN credit_amount ELSE 0 END) as revenue,
                        SUM(CASE WHEN category = "operating_expense" THEN debit_amount ELSE 0 END) as expenses')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $item->net_profit = $item->revenue - $item->expenses;
                return $item;
            });
    }

    private function getInvestorProfitBreakdown($startDate, $endDate)
    {
        return Investment::with('investor')
            ->whereBetween('investment_date', [$startDate, $endDate])
            ->get()
            ->groupBy('investor_id')
            ->map(function ($investments) {
                $investor = $investments->first()->investor;
                return [
                    'investor' => $investor,
                    'total_invested' => $investments->sum('amount'),
                    'total_profit' => $investments->sum('actual_profit'),
                    'return_percentage' => $investments->sum('amount') > 0 ? 
                        ($investments->sum('actual_profit') / $investments->sum('amount')) * 100 : 0,
                ];
            });
    }

    private function getCategoryProfitBreakdown($startDate, $endDate)
    {
        return AccountingLedger::approved()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(credit_amount) as credits, SUM(debit_amount) as debits')
            ->groupBy('category')
            ->get()
            ->map(function ($item) {
                $item->net_amount = $item->credits - $item->debits;
                return $item;
            });
    }

    private function getCashflowReport($startDate, $endDate)
    {
        return [
            'operating_cashflow' => AccountingLedger::approved()
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->whereIn('transaction_type', ['investment', 'payout', 'fee'])
                ->sum('credit_amount') - AccountingLedger::approved()
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->whereIn('transaction_type', ['investment', 'payout', 'fee'])
                ->sum('debit_amount'),
            'investing_cashflow' => 0, // Placeholder for future implementation
            'financing_cashflow' => 0, // Placeholder for future implementation
        ];
    }

    private function getMonthlyCashflow($startDate, $endDate)
    {
        return AccountingLedger::approved()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as month, 
                        SUM(credit_amount) as inflows, 
                        SUM(debit_amount) as outflows')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $item->net_cashflow = $item->inflows - $item->outflows;
                return $item;
            });
    }

    private function getProjectedCashflow($endDate)
    {
        // Get upcoming payouts
        $upcomingPayouts = Investment::where('status', 'active')
            ->where('next_payout_date', '>', $endDate)
            ->sum('payout_amount');

        return [
            'upcoming_payouts' => $upcomingPayouts,
            'projected_revenue' => 0, // Placeholder for future implementation
            'projected_expenses' => 0, // Placeholder for future implementation
        ];
    }

    private function getApprovalStats()
    {
        return [
            'pending' => Approval::where('status', 'pending')->count(),
            'approved' => Approval::where('status', 'approved')->count(),
            'rejected' => Approval::where('status', 'rejected')->count(),
            'requested_changes' => Approval::where('status', 'requested_changes')->count(),
            'average_approval_time' => $this->calculateAverageApprovalTime(),
        ];
    }

    private function getInquiryStats()
    {
        return [
            'total' => Inquiry::count(),
            'received' => Inquiry::where('status', 'received')->count(),
            'in_progress' => Inquiry::where('status', 'in_progress')->count(),
            'quoted' => Inquiry::where('status', 'quoted')->count(),
            'completed' => Inquiry::where('status', 'completed')->count(),
            'closed' => Inquiry::where('status', 'closed')->count(),
            'average_response_time' => $this->calculateAverageResponseTime(),
        ];
    }

    private function calculateAverageInvestmentDuration()
    {
        $investments = Investment::where('status', 'matured')
            ->whereNotNull('maturity_date')
            ->get();

        if ($investments->isEmpty()) {
            return 0;
        }

        $totalDays = $investments->sum(function ($investment) {
            return $investment->investment_date->diffInDays($investment->maturity_date);
        });

        return $totalDays / $investments->count();
    }

    private function calculateAverageApprovalTime()
    {
        $approvals = Approval::where('status', 'approved')
            ->whereNotNull('approved_at')
            ->get();

        if ($approvals->isEmpty()) {
            return 0;
        }

        $totalHours = $approvals->sum(function ($approval) {
            return $approval->created_at->diffInHours($approval->approved_at);
        });

        return $totalHours / $approvals->count();
    }

    private function calculateAverageResponseTime()
    {
        $inquiries = Inquiry::where('status', 'completed')
            ->whereNotNull('response_date')
            ->get();

        if ($inquiries->isEmpty()) {
            return 0;
        }

        $totalHours = $inquiries->sum(function ($inquiry) {
            return $inquiry->created_at->diffInHours($inquiry->response_date);
        });

        return $totalHours / $inquiries->count();
    }

    private function getReportData($reportType, $startDate, $endDate)
    {
        switch ($reportType) {
            case 'profit-summary':
                return [
                    'summary' => $this->getProfitSummary($startDate, $endDate),
                    'monthlyBreakdown' => $this->getMonthlyProfitBreakdown($startDate, $endDate),
                    'investorBreakdown' => $this->getInvestorProfitBreakdown($startDate, $endDate),
                ];
            case 'cashflow':
                return [
                    'cashflow' => $this->getCashflowReport($startDate, $endDate),
                    'monthlyCashflow' => $this->getMonthlyCashflow($startDate, $endDate),
                ];
            default:
                return [];
        }
    }

    private function writeCsvHeaders($file, $reportType)
    {
        switch ($reportType) {
            case 'profit-summary':
                fputcsv($file, ['Month', 'Revenue', 'Expenses', 'Net Profit']);
                break;
            case 'cashflow':
                fputcsv($file, ['Month', 'Inflows', 'Outflows', 'Net Cashflow']);
                break;
            default:
                fputcsv($file, ['Data']);
        }
    }

    private function writeCsvData($file, $data, $reportType)
    {
        switch ($reportType) {
            case 'profit-summary':
                foreach ($data['monthlyBreakdown'] as $item) {
                    fputcsv($file, [$item->month, $item->revenue, $item->expenses, $item->net_profit]);
                }
                break;
            case 'cashflow':
                foreach ($data['monthlyCashflow'] as $item) {
                    fputcsv($file, [$item->month, $item->inflows, $item->outflows, $item->net_cashflow]);
                }
                break;
            default:
                fputcsv($file, ['No data available']);
        }
    }
}