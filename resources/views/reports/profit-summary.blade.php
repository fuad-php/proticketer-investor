<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profit Summary Report') }}
        </h2>
    </x-slot>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Profit Summary Report</h3>
                    <div>
                        <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <a href="{{ route('reports.export-pdf', ['report_type' => 'profit-summary', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                        <a href="{{ route('reports.export-csv', ['report_type' => 'profit-summary', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-success">
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

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Revenue</span>
                                    <span class="info-box-number">${{ number_format($summary['total_revenue'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-arrow-down"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Expenses</span>
                                    <span class="info-box-number">${{ number_format($summary['total_expenses'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-calculator"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Net Profit</span>
                                    <span class="info-box-number">${{ number_format($summary['total_revenue'] - $summary['total_expenses'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-percentage"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Profit Margin</span>
                                    <span class="info-box-number">{{ $summary['total_revenue'] > 0 ? number_format((($summary['total_revenue'] - $summary['total_expenses']) / $summary['total_revenue']) * 100, 2) : 0 }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Breakdown -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Monthly Profit Breakdown</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Month</th>
                                                    <th>Revenue</th>
                                                    <th>Expenses</th>
                                                    <th>Net Profit</th>
                                                    <th>Profit Margin</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($monthlyBreakdown as $month)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $month->month)->format('M Y') }}</td>
                                                    <td class="text-success">${{ number_format($month->revenue, 2) }}</td>
                                                    <td class="text-danger">${{ number_format($month->expenses, 2) }}</td>
                                                    <td class="font-weight-bold {{ $month->net_profit >= 0 ? 'text-success' : 'text-danger' }}">
                                                        ${{ number_format($month->net_profit, 2) }}
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $month->revenue > 0 && ($month->net_profit / $month->revenue) >= 0.1 ? 'success' : 'warning' }}">
                                                            {{ $month->revenue > 0 ? number_format(($month->net_profit / $month->revenue) * 100, 1) : 0 }}%
                                                        </span>
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

                    <!-- Investor Breakdown -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Investor Profit Breakdown</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Investor</th>
                                                    <th>Total Invested</th>
                                                    <th>Total Profit</th>
                                                    <th>Return Percentage</th>
                                                    <th>Performance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($investorBreakdown as $breakdown)
                                                <tr>
                                                    <td>{{ $breakdown['investor']->name }}</td>
                                                    <td>${{ number_format($breakdown['total_invested'], 2) }}</td>
                                                    <td class="font-weight-bold {{ $breakdown['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                        ${{ number_format($breakdown['total_profit'], 2) }}
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $breakdown['return_percentage'] >= 10 ? 'success' : ($breakdown['return_percentage'] >= 5 ? 'warning' : 'danger') }}">
                                                            {{ number_format($breakdown['return_percentage'], 2) }}%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($breakdown['return_percentage'] >= 15)
                                                            <span class="badge badge-success">Excellent</span>
                                                        @elseif($breakdown['return_percentage'] >= 10)
                                                            <span class="badge badge-primary">Good</span>
                                                        @elseif($breakdown['return_percentage'] >= 5)
                                                            <span class="badge badge-warning">Average</span>
                                                        @else
                                                            <span class="badge badge-danger">Below Average</span>
                                                        @endif
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

                    <!-- Category Breakdown -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Category Breakdown</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Total Credits</th>
                                                    <th>Total Debits</th>
                                                    <th>Net Amount</th>
                                                    <th>Percentage of Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($categoryBreakdown as $category)
                                                <tr>
                                                    <td>{{ ucwords(str_replace('_', ' ', $category->category)) }}</td>
                                                    <td class="text-success">${{ number_format($category->credits, 2) }}</td>
                                                    <td class="text-danger">${{ number_format($category->debits, 2) }}</td>
                                                    <td class="font-weight-bold {{ $category->net_amount >= 0 ? 'text-success' : 'text-danger' }}">
                                                        ${{ number_format($category->net_amount, 2) }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $total = $summary['total_revenue'] + $summary['total_expenses'];
                                                            $percentage = $total > 0 ? (($category->credits + $category->debits) / $total) * 100 : 0;
                                                        @endphp
                                                        {{ number_format($percentage, 1) }}%
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

                    <!-- Profit Chart -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Profit Trend Chart</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="profitChart" height="100"></canvas>
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
    const monthlyData = @json($monthlyBreakdown);
    
    const ctx = document.getElementById('profitChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthlyData.map(item => {
                const date = new Date(item.month + '-01');
                return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Revenue',
                data: monthlyData.map(item => item.revenue),
                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }, {
                label: 'Expenses',
                data: monthlyData.map(item => item.expenses),
                backgroundColor: 'rgba(220, 53, 69, 0.8)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 1
            }, {
                label: 'Net Profit',
                data: monthlyData.map(item => item.net_profit),
                backgroundColor: 'rgba(0, 123, 255, 0.8)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
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
