<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Accounting Ledger') }}
        </h2>
    </x-slot>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Accounting Ledger</h3>
                    <div>
                        <a href="{{ route('accounting.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Transaction
                        </a>
                        <a href="{{ route('accounting.reports') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Transactions</span>
                                    <span class="info-box-number">{{ number_format($stats['total_transactions']) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Credits</span>
                                    <span class="info-box-number">${{ number_format($stats['total_credits'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-arrow-down"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Debits</span>
                                    <span class="info-box-number">${{ number_format($stats['total_debits'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-balance-scale"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Current Balance</span>
                                    <span class="info-box-number">${{ number_format($stats['current_balance'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pending</span>
                                    <span class="info-box-number">{{ $stats['pending_transactions'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary"><i class="fas fa-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Rejected</span>
                                    <span class="info-box-number">{{ $stats['rejected_transactions'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>Reversed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="transaction_type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="investment" {{ request('transaction_type') == 'investment' ? 'selected' : '' }}>Investment</option>
                                    <option value="payout" {{ request('transaction_type') == 'payout' ? 'selected' : '' }}>Payout</option>
                                    <option value="fee" {{ request('transaction_type') == 'fee' ? 'selected' : '' }}>Fee</option>
                                    <option value="expense" {{ request('transaction_type') == 'expense' ? 'selected' : '' }}>Expense</option>
                                    <option value="revenue" {{ request('transaction_type') == 'revenue' ? 'selected' : '' }}>Revenue</option>
                                    <option value="adjustment" {{ request('transaction_type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="category" class="form-control">
                                    <option value="">All Categories</option>
                                    <option value="investment_income" {{ request('category') == 'investment_income' ? 'selected' : '' }}>Investment Income</option>
                                    <option value="management_fee" {{ request('category') == 'management_fee' ? 'selected' : '' }}>Management Fee</option>
                                    <option value="performance_fee" {{ request('category') == 'performance_fee' ? 'selected' : '' }}>Performance Fee</option>
                                    <option value="operating_expense" {{ request('category') == 'operating_expense' ? 'selected' : '' }}>Operating Expense</option>
                                    <option value="payout" {{ request('category') == 'payout' ? 'selected' : '' }}>Payout</option>
                                    <option value="refund" {{ request('category') == 'refund' ? 'selected' : '' }}>Refund</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="Start Date">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="End Date">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('accounting.index') }}" class="btn btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>

                    <!-- Transactions Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                <tr>
                                    <td>
                                        <a href="{{ route('accounting.show', $transaction) }}" class="text-primary">
                                            {{ $transaction->transaction_id }}
                                        </a>
                                    </td>
                                    <td>{{ $transaction->transaction_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $transaction->transaction_type_text }}</span>
                                    </td>
                                    <td>{{ $transaction->category_text }}</td>
                                    <td>{{ Str::limit($transaction->description, 50) }}</td>
                                    <td class="text-danger">
                                        @if($transaction->debit_amount > 0)
                                            ${{ number_format($transaction->debit_amount, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-success">
                                        @if($transaction->credit_amount > 0)
                                            ${{ number_format($transaction->credit_amount, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="font-weight-bold">${{ number_format($transaction->balance, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $transaction->status_badge_color }}">
                                            {{ $transaction->status_text }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('accounting.show', $transaction) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($transaction->isPending() && auth()->user()->can('approve accounting'))
                                                <form method="POST" action="{{ route('accounting.approve', $transaction) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this transaction?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('accounting.reject', $transaction) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject this transaction?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No transactions found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
