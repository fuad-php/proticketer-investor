<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Reports & Analytics Dashboard') }}
        </h2>
    </x-slot>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Reports & Analytics Dashboard</h3>
                </div>
                <div class="card-body">
                    <!-- Key Metrics -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ number_format($stats['total_investors']) }}</h3>
                                    <p>Total Investors</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <a href="{{ route('investors.index') }}" class="small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>${{ number_format($stats['total_invested'], 0) }}</h3>
                                    <p>Total Invested</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <a href="{{ route('investments.index') }}" class="small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>${{ number_format($stats['total_profits'], 0) }}</h3>
                                    <p>Total Profits</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <a href="{{ route('reports.profit-summary') }}" class="small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $stats['pending_approvals'] }}</h3>
                                    <p>Pending Approvals</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <a href="{{ route('approvals.index') }}" class="small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Metrics -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Performance Metrics</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-primary"><i class="fas fa-percentage"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Average Return</span>
                                                    <span class="info-box-number">{{ number_format($performanceMetrics['average_return_percentage'], 2) }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-coins"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Fees Collected</span>
                                                    <span class="info-box-number">${{ number_format($performanceMetrics['total_fees_collected'], 0) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Avg Duration</span>
                                                    <span class="info-box-number">{{ number_format($performanceMetrics['average_investment_duration'], 0) }} days</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-trophy"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Best Return</span>
                                                    <span class="info-box-number">{{ $performanceMetrics['best_performing_investment'] ? number_format($performanceMetrics['best_performing_investment']->return_percentage, 2) . '%' : 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Monthly Overview</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Monthly Revenue</span>
                                                    <span class="info-box-number">${{ number_format($stats['monthly_revenue'], 0) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-danger"><i class="fas fa-arrow-down"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Monthly Expenses</span>
                                                    <span class="info-box-number">${{ number_format($stats['monthly_expenses'], 0) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-primary"><i class="fas fa-calculator"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Net Income</span>
                                                    <span class="info-box-number">${{ number_format($stats['monthly_revenue'] - $stats['monthly_expenses'], 0) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-question-circle"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Pending Inquiries</span>
                                                    <span class="info-box-number">{{ $stats['pending_inquiries'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trends Chart -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>12-Month Trends</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="trendsChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Recent Investments</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Investor</th>
                                                    <th>Amount</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentActivity['recent_investments'] as $investment)
                                                <tr>
                                                    <td>{{ $investment->investor->name }}</td>
                                                    <td>${{ number_format($investment->amount, 0) }}</td>
                                                    <td>{{ $investment->created_at->format('M d, Y') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Recent Orders</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Client</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentActivity['recent_orders'] as $order)
                                                <tr>
                                                    <td>{{ $order->client->name }}</td>
                                                    <td>${{ number_format($order->total_amount, 0) }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $order->status === 'active' ? 'success' : 'warning' }}">
                                                            {{ ucfirst($order->status) }}
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

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Quick Actions</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <a href="{{ route('reports.profit-summary') }}" class="btn btn-primary btn-block">
                                                <i class="fas fa-chart-line"></i> Profit Summary
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="{{ route('reports.cashflow') }}" class="btn btn-info btn-block">
                                                <i class="fas fa-exchange-alt"></i> Cash Flow
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="{{ route('reports.outstanding-payments') }}" class="btn btn-warning btn-block">
                                                <i class="fas fa-exclamation-triangle"></i> Outstanding Payments
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="{{ route('reports.approval-status') }}" class="btn btn-success btn-block">
                                                <i class="fas fa-check-circle"></i> Approval Status
                                            </a>
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
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trendsData = @json($trends);
    
    const ctx = document.getElementById('trendsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendsData.map(item => item.month),
            datasets: [{
                label: 'Investments',
                data: trendsData.map(item => item.investments),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Profits',
                data: trendsData.map(item => item.profits),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
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
