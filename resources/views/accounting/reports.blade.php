<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Accounting Reports') }}
        </h2>
    </x-slot>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Accounting Reports</h3>
                    <div>
                        <a href="{{ route('accounting.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Ledger
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Date Range Filter -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label>End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary form-control">Update Reports</button>
                            </div>
                        </div>
                    </form>

                    <!-- Income Statement -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Income Statement ({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }})</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Revenues</span>
                                                    <span class="info-box-number">${{ number_format($reports['income_statement']['revenues'], 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-danger"><i class="fas fa-arrow-down"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Expenses</span>
                                                    <span class="info-box-number">${{ number_format($reports['income_statement']['expenses'], 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-primary"><i class="fas fa-calculator"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Net Income</span>
                                                    <span class="info-box-number">${{ number_format($reports['income_statement']['net_income'], 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Sheet -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Balance Sheet (As of {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }})</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-building"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Assets</span>
                                                    <span class="info-box-number">${{ number_format($reports['balance_sheet']['assets'], 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Liabilities</span>
                                                    <span class="info-box-number">${{ number_format($reports['balance_sheet']['liabilities'], 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-balance-scale"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Equity</span>
                                                    <span class="info-box-number">${{ number_format($reports['balance_sheet']['equity'], 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cash Flow -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Cash Flow Statement ({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }})</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Cash Inflows</span>
                                                    <span class="info-box-number">${{ number_format($reports['cash_flow']['inflows'], 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-danger"><i class="fas fa-arrow-down"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Cash Outflows</span>
                                                    <span class="info-box-number">${{ number_format($reports['cash_flow']['outflows'], 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-primary"><i class="fas fa-exchange-alt"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Net Cash Flow</span>
                                                    <span class="info-box-number">${{ number_format($reports['cash_flow']['net_cash_flow'], 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Breakdown -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Category Breakdown</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Total Credits</th>
                                                    <th>Total Debits</th>
                                                    <th>Net Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($reports['category_breakdown'] as $category)
                                                <tr>
                                                    <td>{{ ucwords(str_replace('_', ' ', $category['category'])) }}</td>
                                                    <td class="text-success">${{ number_format($category['total_credits'], 2) }}</td>
                                                    <td class="text-danger">${{ number_format($category['total_debits'], 2) }}</td>
                                                    <td class="font-weight-bold {{ $category['net_amount'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                        ${{ number_format($category['net_amount'], 2) }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Trends -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Monthly Trends</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Month</th>
                                                    <th>Credits</th>
                                                    <th>Debits</th>
                                                    <th>Net</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($reports['monthly_trends'] as $trend)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $trend['month'])->format('M Y') }}</td>
                                                    <td class="text-success">${{ number_format($trend['credits'], 2) }}</td>
                                                    <td class="text-danger">${{ number_format($trend['debits'], 2) }}</td>
                                                    <td class="font-weight-bold {{ $trend['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                        ${{ number_format($trend['net'], 2) }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Investor Summary -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Investor Summary</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Investor</th>
                                                    <th>Total Investments</th>
                                                    <th>Total Payouts</th>
                                                    <th>Total Fees</th>
                                                    <th>Net Position</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($reports['investor_summary'] as $summary)
                                                <tr>
                                                    <td>{{ $summary['investor']->name }}</td>
                                                    <td class="text-success">${{ number_format($summary['total_investments'], 2) }}</td>
                                                    <td class="text-danger">${{ number_format($summary['total_payouts'], 2) }}</td>
                                                    <td class="text-warning">${{ number_format($summary['total_fees'], 2) }}</td>
                                                    <td class="font-weight-bold {{ $summary['net_position'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                        ${{ number_format($summary['net_position'], 2) }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Client Summary -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Client Summary</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Client</th>
                                                    <th>Total Transactions</th>
                                                    <th>Total Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($reports['client_summary'] as $summary)
                                                <tr>
                                                    <td>{{ $summary['client']->name }}</td>
                                                    <td>{{ $summary['total_transactions'] }}</td>
                                                    <td class="font-weight-bold {{ $summary['total_amount'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                        ${{ number_format($summary['total_amount'], 2) }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
