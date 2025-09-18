<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cash Flow Report') }}
        </h2>
    </x-slot>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Cash Flow Report</h3>
                    <div>
                        <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <a href="{{ route('reports.export-pdf', ['report_type' => 'cashflow', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                        <a href="{{ route('reports.export-csv', ['report_type' => 'cashflow', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-success">
                            <i class="fas fa-file-csv"></i> Export CSV
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
                                <button type="submit" class="btn btn-primary form-control">Update Report</button>
                            </div>
                        </div>
                    </form>

                    <!-- Cash Flow Summary -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Operating Cash Flow</span>
                                    <span class="info-box-number">${{ number_format($cashflow['operating_cashflow'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-building"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Investing Cash Flow</span>
                                    <span class="info-box-number">${{ number_format($cashflow['investing_cashflow'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-hand-holding-usd"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Financing Cash Flow</span>
                                    <span class="info-box-number">${{ number_format($cashflow['financing_cashflow'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Cash Flow -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Monthly Cash Flow</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Month</th>
                                                    <th>Cash Inflows</th>
                                                    <th>Cash Outflows</th>
                                                    <th>Net Cash Flow</th>
                                                    <th>Cumulative Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $cumulativeBalance = 0; @endphp
                                                @foreach($monthlyCashflow as $month)
                                                @php $cumulativeBalance += $month->net_cashflow; @endphp
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $month->month)->format('M Y') }}</td>
                                                    <td class="text-success">${{ number_format($month->inflows, 2) }}</td>
                                                    <td class="text-danger">${{ number_format($month->outflows, 2) }}</td>
                                                    <td class="font-weight-bold {{ $month->net_cashflow >= 0 ? 'text-success' : 'text-danger' }}">
                                                        ${{ number_format($month->net_cashflow, 2) }}
                                                    </td>
                                                    <td class="font-weight-bold {{ $cumulativeBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                                        ${{ number_format($cumulativeBalance, 2) }}
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

                    <!-- Projected Cash Flow -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Projected Cash Flow</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Upcoming Payouts</span>
                                                    <span class="info-box-number">${{ number_format($projectedCashflow['upcoming_payouts'], 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Projected Revenue</span>
                                                    <span class="info-box-number">${{ number_format($projectedCashflow['projected_revenue'], 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-danger"><i class="fas fa-arrow-down"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Projected Expenses</span>
                                                    <span class="info-box-number">${{ number_format($projectedCashflow['projected_expenses'], 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cash Flow Chart -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Cash Flow Trend Chart</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="cashflowChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyData = @json($monthlyCashflow);
    
    const ctx = document.getElementById('cashflowChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => {
                const date = new Date(item.month + '-01');
                return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Cash Inflows',
                data: monthlyData.map(item => item.inflows),
                borderColor: 'rgba(40, 167, 69, 1)',
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                tension: 0.1
            }, {
                label: 'Cash Outflows',
                data: monthlyData.map(item => item.outflows),
                borderColor: 'rgba(220, 53, 69, 1)',
                backgroundColor: 'rgba(220, 53, 69, 0.2)',
                tension: 0.1
            }, {
                label: 'Net Cash Flow',
                data: monthlyData.map(item => item.net_cashflow),
                borderColor: 'rgba(0, 123, 255, 1)',
                backgroundColor: 'rgba(0, 123, 255, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
</x-app-layout>
