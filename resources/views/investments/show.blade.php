<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Investment Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">Investment #{{ $investment->id }}</h3>
                        <div class="flex space-x-2">
                            @can('edit investments')
                                <a href="{{ route('investments.edit', $investment) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Edit Investment
                                </a>
                            @endcan
                            <a href="{{ route('investments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Back to Investments
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Investment Information -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <h4 class="text-lg font-medium mb-4">Investment Information</h4>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Investment ID</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">#{{ $investment->id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Investor</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $investment->investor->full_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Investor Code</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $investment->investor->investor_code }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Order</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $investment->order->order_number }} - {{ $investment->order->title }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Investment Amount</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($investment->amount, 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Profit Percentage</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $investment->profit_percentage }}%</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Expected Profit</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($investment->expected_profit, 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Actual Profit</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($investment->actual_profit ?? 0, 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Investment Date</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $investment->investment_date->format('M d, Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Maturity Date</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $investment->maturity_date->format('M d, Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                        <dd class="text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($investment->status === 'active') bg-green-100 text-green-800
                                                @elseif($investment->status === 'matured') bg-blue-100 text-blue-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst($investment->status) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Status</dt>
                                        <dd class="text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($investment->payment_status === 'completed') bg-green-100 text-green-800
                                                @elseif($investment->payment_status === 'partial') bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst($investment->payment_status) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $investment->created_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                    @if($investment->notes)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $investment->notes }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        <!-- Related Information -->
                        <div class="space-y-6">
                            <!-- Investor Information -->
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <h4 class="text-lg font-medium mb-4">Investor Information</h4>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $investment->investor->full_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $investment->investor->email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $investment->investor->phone ?: 'Not provided' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Invested</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($investment->investor->total_invested, 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Profit</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($investment->investor->total_profit, 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Balance</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($investment->investor->current_balance, 2) }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Order Information -->
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <h4 class="text-lg font-medium mb-4">Order Information</h4>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Order Number</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $investment->order->order_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Title</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $investment->order->title }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Amount</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($investment->order->total_amount, 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Order Status</dt>
                                        <dd class="text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($investment->order->status === 'active') bg-green-100 text-green-800
                                                @elseif($investment->order->status === 'completed') bg-blue-100 text-blue-800
                                                @elseif($investment->order->status === 'cancelled') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($investment->order->status) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $investment->order->start_date->format('M d, Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $investment->order->end_date->format('M d, Y') }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Investment Performance -->
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <h4 class="text-lg font-medium mb-4">Performance Summary</h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Principal Amount:</span>
                                        <span class="font-medium">${{ number_format($investment->amount, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Expected Profit:</span>
                                        <span class="font-medium text-green-600">${{ number_format($investment->expected_profit, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Actual Profit:</span>
                                        <span class="font-medium {{ $investment->actual_profit > 0 ? 'text-green-600' : 'text-gray-600' }}">
                                            ${{ number_format($investment->actual_profit ?? 0, 2) }}
                                        </span>
                                    </div>
                                    <hr class="border-gray-300 dark:border-gray-600">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Total Expected Return:</span>
                                        <span class="font-medium text-blue-600">${{ number_format($investment->amount + $investment->expected_profit, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Total Actual Return:</span>
                                        <span class="font-medium {{ ($investment->actual_profit ?? 0) > 0 ? 'text-blue-600' : 'text-gray-600' }}">
                                            ${{ number_format($investment->amount + ($investment->actual_profit ?? 0), 2) }}
                                        </span>
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
