<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Approval Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">Approval for Order #{{ $approval->order->order_number }}</h3>
                        <div class="flex space-x-2">
                            @if($approval->status === 'pending' && auth()->user()->id === $approval->approver_id)
                                <a href="{{ route('approvals.approve', $approval) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Approve
                                </a>
                                <a href="{{ route('approvals.reject', $approval) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Reject
                                </a>
                            @endif
                            <a href="{{ route('approvals.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Back to Approvals
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Approval Information -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <h4 class="text-lg font-medium mb-4">Approval Information</h4>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Approval ID</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">#{{ $approval->id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Approver Role</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $approval->approver_role)) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                        <dd class="text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($approval->status === 'approved') bg-green-100 text-green-800
                                                @elseif($approval->status === 'rejected') bg-red-100 text-red-800
                                                @elseif($approval->status === 'requested_changes') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($approval->status) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $approval->created_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                    @if($approval->approved_at)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Approved At</dt>
                                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $approval->approved_at->format('M d, Y H:i') }}</dd>
                                        </div>
                                    @endif
                                    @if($approval->comments)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Comments</dt>
                                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $approval->comments }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        <!-- Order Information -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <h4 class="text-lg font-medium mb-4">Order Information</h4>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Order Number</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $approval->order->order_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Title</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $approval->order->title }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $approval->order->description ?: 'No description provided' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Client</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $approval->order->client ? $approval->order->client->name : 'No client assigned' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Amount</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($approval->order->total_amount, 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Profit Percentage</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $approval->order->profit_percentage }}%</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $approval->order->start_date->format('M d, Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $approval->order->end_date->format('M d, Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created By</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $approval->order->creator->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Order Status</dt>
                                        <dd class="text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($approval->order->status === 'active') bg-green-100 text-green-800
                                                @elseif($approval->order->status === 'completed') bg-blue-100 text-blue-800
                                                @elseif($approval->order->status === 'cancelled') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($approval->order->status) }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Investments -->
                            @if($approval->order->investments->count() > 0)
                                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                    <h4 class="text-lg font-medium mb-4">Investments ({{ $approval->order->investments->count() }})</h4>
                                    <div class="space-y-3">
                                        @foreach($approval->order->investments as $investment)
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
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
