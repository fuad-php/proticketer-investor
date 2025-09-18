<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Transaction Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Back Button -->
                    <div class="mb-6">
                        <a href="{{ route('transactions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            ← Back to Transactions
                        </a>
                    </div>

                    <!-- Transaction Details -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Transaction Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Transaction ID</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $transaction->transaction_id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($transaction->type === 'investment') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                            @elseif($transaction->type === 'payout') bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100
                                            @elseif($transaction->type === 'refund') bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100
                                            @endif">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Amount</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        <span class="@if($transaction->type === 'investment') text-green-600 dark:text-green-400 @else text-red-600 dark:text-red-400 @endif font-semibold">
                                            @if($transaction->type === 'investment') + @else - @endif
                                            ${{ number_format($transaction->amount, 2) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($transaction->status === 'completed') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                            @elseif($transaction->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                                            @elseif($transaction->status === 'failed') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100
                                            @endif">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Transaction Date</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $transaction->transaction_date->format('M d, Y H:i A') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created By</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $transaction->creator->name ?? 'System' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Investor Information -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Investor Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $transaction->investor->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $transaction->investor->email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $transaction->investor->phone ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Invested</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($transaction->investor->total_invested, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Profit</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($transaction->investor->total_profit, 2) }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Related Information -->
                    @if($transaction->investment || $transaction->order)
                    <div class="mt-6">
                        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Related Information</h3>
                            
                            @if($transaction->investment)
                            <div class="mb-4">
                                <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-2">Investment Details</h4>
                                <dl class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Investment Amount</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($transaction->investment->amount, 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Profit Percentage</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $transaction->investment->profit_percentage }}%</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Investment Date</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $transaction->investment->investment_date->format('M d, Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Maturity Date</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $transaction->investment->maturity_date->format('M d, Y') }}</dd>
                                    </div>
                                </dl>
                            </div>
                            @endif

                            @if($transaction->order)
                            <div>
                                <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-2">Order Details</h4>
                                <dl class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Order Number</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $transaction->order->order_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Order Title</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $transaction->order->title }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Amount</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($transaction->order->total_amount, 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($transaction->order->status === 'active') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($transaction->order->status === 'completed') bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100
                                                @endif">
                                                {{ ucfirst($transaction->order->status) }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Notes -->
                    @if($transaction->notes)
                    <div class="mt-6">
                        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Notes</h3>
                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $transaction->notes }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end space-x-3">
                        @can('edit transactions')
                        <a href="{{ route('transactions.edit', $transaction) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Edit Transaction
                        </a>
                        @endcan
                        
                        @can('delete transactions')
                        <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this transaction?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Delete Transaction
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
