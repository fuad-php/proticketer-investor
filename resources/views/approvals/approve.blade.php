<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Approve Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium">Approve Order #{{ $approval->order->order_number }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Please review the order details and add any comments before approving.</p>
                    </div>

                    <form method="POST" action="{{ route('approvals.approve', $approval) }}">
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

                        <!-- Comments -->
                        <div class="mb-6">
                            <x-input-label for="comments" :value="__('Comments (Optional)')" />
                            <textarea id="comments" name="comments" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Add any comments about this approval...">{{ old('comments') }}</textarea>
                            <x-input-error :messages="$errors->get('comments')" class="mt-2" />
                        </div>

                        <!-- Confirmation -->
                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                                        Approval Confirmation
                                    </h3>
                                    <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                        <p>By approving this order, you confirm that:</p>
                                        <ul class="list-disc list-inside mt-1 space-y-1">
                                            <li>All order details are accurate</li>
                                            <li>Investment allocations are appropriate</li>
                                            <li>Terms and conditions are acceptable</li>
                                            <li>The order can proceed to the next stage</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('approvals.show', $approval) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Approve Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
