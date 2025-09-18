<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Reject Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium">Reject Order #{{ $approval->order->order_number }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Please provide a reason for rejecting this order.</p>
                    </div>

                    <form method="POST" action="{{ route('approvals.reject', $approval) }}">
                        @csrf
                        
                        <!-- Order Summary -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg mb-6">
                            <h4 class="text-lg font-medium mb-4">Order Summary</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Title:</span>
                                    <span class="font-medium">{{ $approval->order->title }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Client:</span>
                                    <span class="font-medium">{{ $approval->order->client ? $approval->order->client->name : 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Amount:</span>
                                    <span class="font-medium">${{ number_format($approval->order->total_amount, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Profit %:</span>
                                    <span class="font-medium">{{ $approval->order->profit_percentage }}%</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Start Date:</span>
                                    <span class="font-medium">{{ $approval->order->start_date->format('M d, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">End Date:</span>
                                    <span class="font-medium">{{ $approval->order->end_date->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Investments -->
                        @if($approval->order->investments->count() > 0)
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg mb-6">
                                <h4 class="text-lg font-medium mb-4">Investments ({{ $approval->order->investments->count() }})</h4>
                                <div class="space-y-3">
                                    @foreach($approval->order->investments as $investment)
                                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                            <div class="flex justify-between items-center">
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

                        <!-- Rejection Reason -->
                        <div class="mb-6">
                            <x-input-label for="comments" :value="__('Rejection Reason')" />
                            <textarea id="comments" name="comments" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Please provide a detailed reason for rejecting this order..." required>{{ old('comments') }}</textarea>
                            <x-input-error :messages="$errors->get('comments')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This reason will be shared with the order creator and other stakeholders.</p>
                        </div>

                        <!-- Warning -->
                        <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                        Rejection Warning
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                        <p>By rejecting this order, you confirm that:</p>
                                        <ul class="list-disc list-inside mt-1 space-y-1">
                                            <li>The order will be marked as rejected</li>
                                            <li>All associated investments will be cancelled</li>
                                            <li>Investors will be notified of the rejection</li>
                                            <li>The order creator will receive your feedback</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('approvals.show', $approval) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Reject Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
