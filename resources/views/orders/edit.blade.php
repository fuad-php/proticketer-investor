<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('orders.update', $order) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Order Details -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium">Order Information</h3>
                                
                                <div>
                                    <x-input-label for="title" :value="__('Order Title')" />
                                    <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $order->title)" required autofocus />
                                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="description" :value="__('Description')" />
                                    <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $order->description) }}</textarea>
                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="client_id" :value="__('Client')" />
                                    <select id="client_id" name="client_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Select a client (optional)</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" {{ old('client_id', $order->client_id) == $client->id ? 'selected' : '' }}>
                                                {{ $client->name }} ({{ $client->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="total_amount" :value="__('Total Amount')" />
                                    <x-text-input id="total_amount" class="block mt-1 w-full" type="number" step="0.01" name="total_amount" :value="old('total_amount', $order->total_amount)" required />
                                    <x-input-error :messages="$errors->get('total_amount')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="profit_percentage" :value="__('Profit Percentage')" />
                                    <x-text-input id="profit_percentage" class="block mt-1 w-full" type="number" step="0.01" name="profit_percentage" :value="old('profit_percentage', $order->profit_percentage)" required />
                                    <x-input-error :messages="$errors->get('profit_percentage')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="start_date" :value="__('Start Date')" />
                                        <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', $order->start_date->format('Y-m-d'))" required />
                                        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="end_date" :value="__('End Date')" />
                                        <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date', $order->end_date->format('Y-m-d'))" required />
                                        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="status" :value="__('Status')" />
                                    <select id="status" name="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="draft" {{ old('status', $order->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="active" {{ old('status', $order->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="completed" {{ old('status', $order->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ old('status', $order->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="payment_status" :value="__('Payment Status')" />
                                    <select id="payment_status" name="payment_status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="pending" {{ old('payment_status', $order->payment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="partial" {{ old('payment_status', $order->payment_status) == 'partial' ? 'selected' : '' }}>Partial</option>
                                        <option value="completed" {{ old('payment_status', $order->payment_status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('payment_status')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="notes" :value="__('Notes')" />
                                    <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('notes', $order->notes) }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Current Investments -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium">Current Investments</h3>
                                
                                @if($order->investments->count() > 0)
                                    <div class="space-y-4">
                                        @foreach($order->investments as $investment)
                                            <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                                                <div class="flex justify-between items-center mb-3">
                                                    <h4 class="font-medium">{{ $investment->investor->full_name }}</h4>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($investment->status === 'active') bg-green-100 text-green-800
                                                        @elseif($investment->status === 'matured') bg-blue-100 text-blue-800
                                                        @else bg-red-100 text-red-800
                                                        @endif">
                                                        {{ ucfirst($investment->status) }}
                                                    </span>
                                                </div>
                                                
                                                <div class="grid grid-cols-2 gap-4 text-sm">
                                                    <div>
                                                        <span class="text-gray-500 dark:text-gray-400">Amount:</span>
                                                        <span class="font-medium">${{ number_format($investment->amount, 2) }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500 dark:text-gray-400">Profit %:</span>
                                                        <span class="font-medium">{{ $investment->profit_percentage }}%</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500 dark:text-gray-400">Expected Profit:</span>
                                                        <span class="font-medium">${{ number_format($investment->expected_profit, 2) }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500 dark:text-gray-400">Actual Profit:</span>
                                                        <span class="font-medium">${{ number_format($investment->actual_profit ?? 0, 2) }}</span>
                                                    </div>
                                                </div>
                                                
                                                @if($investment->notes)
                                                    <div class="mt-3">
                                                        <span class="text-gray-500 dark:text-gray-400 text-sm">Notes:</span>
                                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $investment->notes }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <p class="text-gray-500 dark:text-gray-400">No investments found for this order.</p>
                                    </div>
                                @endif

                                <!-- Approval Status -->
                                @if($order->approvals->count() > 0)
                                    <div class="mt-6">
                                        <h4 class="text-lg font-medium mb-3">Approval Status</h4>
                                        <div class="space-y-2">
                                            @foreach($order->approvals as $approval)
                                                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                    <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $approval->approver_role)) }}</span>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($approval->status === 'approved') bg-green-100 text-green-800
                                                        @elseif($approval->status === 'rejected') bg-red-100 text-red-800
                                                        @elseif($approval->status === 'requested_changes') bg-yellow-100 text-yellow-800
                                                        @else bg-gray-100 text-gray-800
                                                        @endif">
                                                        {{ ucfirst($approval->status) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Update Order') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
