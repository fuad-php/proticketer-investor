<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Investment;
use App\Models\Investor;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Transaction;
use App\Models\Approval;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get dashboard data based on user role
        if ($user->hasRole('super_admin')) {
            $data = $this->getSuperAdminData();
        } elseif ($user->hasRole('accounts')) {
            $data = $this->getAccountsData();
        } elseif ($user->hasRole(['director', 'managing_director', 'chairman'])) {
            $data = $this->getApproverData();
        } elseif ($user->hasRole('investor')) {
            $data = $this->getInvestorData();
        } elseif ($user->hasRole('client')) {
            $data = $this->getClientData();
        } elseif ($user->hasRole('auditor')) {
            $data = $this->getAuditorData();
        } else {
            $data = [];
        }
        
        return view('dashboard', $data);
    }

    private function getSuperAdminData()
    {
        return [
            'stats' => [
                'total_investors' => Investor::count(),
                'total_clients' => Client::count(),
                'total_orders' => Order::count(),
                'total_investments' => Investment::count(),
                'total_transactions' => Transaction::count(),
                'pending_approvals' => Approval::where('status', 'pending')->count(),
                'total_aum' => Investment::sum('amount'),
                'total_profit' => Investment::sum('actual_profit'),
            ],
            'recent_orders' => Order::with(['client', 'creator'])
                ->latest()
                ->limit(5)
                ->get(),
            'pending_approvals' => Approval::with(['order', 'approver'])
                ->where('status', 'pending')
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }

    private function getAccountsData()
    {
        return [
            'stats' => [
                'total_orders' => Order::count(),
                'pending_approvals' => Approval::where('status', 'pending')->count(),
                'total_investments' => Investment::count(),
                'total_transactions' => Transaction::count(),
            ],
            'recent_orders' => Order::with(['client', 'creator'])
                ->latest()
                ->limit(10)
                ->get(),
            'pending_approvals' => Approval::with(['order', 'approver'])
                ->where('status', 'pending')
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }

    private function getApproverData()
    {
        $user = auth()->user();
        
        return [
            'stats' => [
                'pending_approvals' => Approval::where('approver_id', $user->id)
                    ->where('status', 'pending')
                    ->count(),
                'total_approved' => Approval::where('approver_id', $user->id)
                    ->where('status', 'approved')
                    ->count(),
            ],
            'pending_approvals' => Approval::with(['order', 'approver'])
                ->where('approver_id', $user->id)
                ->where('status', 'pending')
                ->latest()
                ->get(),
        ];
    }

    private function getInvestorData()
    {
        $user = auth()->user();
        $investor = $user->investor;
        
        if (!$investor) {
            return ['investor' => null];
        }

        return [
            'investor' => $investor,
            'stats' => [
                'total_invested' => $investor->total_invested,
                'total_profit' => $investor->total_profit,
                'current_balance' => $investor->current_balance,
                'active_investments' => $investor->investments()->where('status', 'active')->count(),
            ],
            'recent_investments' => $investor->investments()
                ->with(['order'])
                ->latest()
                ->limit(5)
                ->get(),
            'recent_transactions' => $investor->transactions()
                ->latest()
                ->limit(10)
                ->get(),
        ];
    }

    private function getClientData()
    {
        $user = auth()->user();
        
        // Find client by email
        $client = Client::where('email', $user->email)->first();
        
        if (!$client) {
            return ['client' => null];
        }

        return [
            'client' => $client,
            'stats' => [
                'total_inquiries' => $client->inquiries()->count(),
                'pending_inquiries' => $client->inquiries()->where('status', 'received')->count(),
                'completed_inquiries' => $client->inquiries()->where('status', 'completed')->count(),
            ],
            'recent_inquiries' => $client->inquiries()
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }

    private function getAuditorData()
    {
        return [
            'stats' => [
                'total_transactions' => Transaction::count(),
                'total_orders' => Order::count(),
                'total_investments' => Investment::count(),
                'total_investors' => Investor::count(),
            ],
            'recent_transactions' => Transaction::with(['investor', 'creator'])
                ->latest()
                ->limit(10)
                ->get(),
        ];
    }
}
