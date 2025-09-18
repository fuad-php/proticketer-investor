<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Order Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">Order #{{ $order->order_number }}</h3>
                        <div class="flex space-x-2">
                            @can('edit orders')
                                <a href="{{ route('orders.edit', $order) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Edit Order
                                </a>
                            @endcan
                            <a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Back to Orders
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Order Information -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <h4 class="text-lg font-medium mb-4">Order Information</h4>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Order Number</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $order->order_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Title</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $order->title }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $order->description ?: 'No description provided' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Client</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $order->client ? $order->client->name : 'No client assigned' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Amount</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($order->total_amount, 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Profit Percentage</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $order->profit_percentage }}%</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $order->start_date->format('M d, Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $order->end_date->format('M d, Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                        <dd class="text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($order->status === 'active') bg-green-100 text-green-800
                                                @elseif($order->status === 'completed') bg-blue-100 text-blue-800
                                                @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Status</dt>
                                        <dd class="text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($order->payment_status === 'completed') bg-green-100 text-green-800
                                                @elseif($order->payment_status === 'partial') bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst($order->payment_status) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created By</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $order->creator->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $order->created_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                    @if($order->notes)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $order->notes }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        <!-- Investments and Approvals -->
                        <div class="space-y-6">
                            <!-- Investments -->
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <h4 class="text-lg font-medium mb-4">Investments ({{ $order->investments->count() }})</h4>
                                @if($order->investments->count() > 0)
                                    <div class="space-y-3">
                                        @foreach($order->investments as $investment)
                                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h5 class="font-medium">{{ $investment->investor->full_name }}</h5>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $investment->investor->investor_code }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="font-medium">${{ number_format($investment->amount, 2) }}</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $investment->profit_percentage }}% profit</p>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($investment->status === 'active') bg-green-100 text-green-800
                                                        @elseif($investment->status === 'matured') bg-blue-100 text-blue-800
                                                        @else bg-red-100 text-red-800
                                                        @endif">
                                                        {{ ucfirst($investment->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 dark:text-gray-400">No investments found for this order.</p>
                                @endif
                            </div>

                            <!-- Approvals -->
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <h4 class="text-lg font-medium mb-4">Approval Status</h4>
                                @if($order->approvals->count() > 0)
                                    <div class="space-y-3">
                                        @foreach($order->approvals as $approval)
                                            <div class="flex justify-between items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg">
                                                <div>
                                                    <h5 class="font-medium">{{ ucfirst(str_replace('_', ' ', $approval->approver_role)) }}</h5>
                                                    @if($approval->approver)
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $approval->approver->name }}</p>
                                                    @endif
                                                </div>
                                                <div class="text-right">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($approval->status === 'approved') bg-green-100 text-green-800
                                                        @elseif($approval->status === 'rejected') bg-red-100 text-red-800
                                                        @elseif($approval->status === 'requested_changes') bg-yellow-100 text-yellow-800
                                                        @else bg-gray-100 text-gray-800
                                                        @endif">
                                                        {{ ucfirst($approval->status) }}
                                                    </span>
                                                    @if($approval->approved_at)
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $approval->approved_at->format('M d, Y H:i') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($approval->comments)
                                                <div class="ml-4 p-3 bg-gray-100 dark:bg-gray-600 rounded-lg">
                                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $approval->comments }}</p>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 dark:text-gray-400">No approval workflow initiated yet.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
