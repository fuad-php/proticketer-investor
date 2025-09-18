<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Investment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('investments.update', $investment) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Investment Details -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium">Investment Information</h3>
                                
                                <div>
                                    <x-input-label for="order_id" :value="__('Order')" />
                                    <select id="order_id" name="order_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                        <option value="">Select an order</option>
                                        @foreach($orders as $order)
                                            <option value="{{ $order->id }}" {{ old('order_id', $investment->order_id) == $order->id ? 'selected' : '' }}>
                                                {{ $order->order_number }} - {{ $order->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('order_id')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="investor_id" :value="__('Investor')" />
                                    <select id="investor_id" name="investor_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                        <option value="">Select an investor</option>
                                        @foreach($investors as $investor)
                                            <option value="{{ $investor->id }}" {{ old('investor_id', $investment->investor_id) == $investor->id ? 'selected' : '' }}>
                                                {{ $investor->full_name }} ({{ $investor->investor_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('investor_id')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="amount" :value="__('Investment Amount')" />
                                    <x-text-input id="amount" class="block mt-1 w-full" type="number" step="0.01" name="amount" :value="old('amount', $investment->amount)" required />
                                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="profit_percentage" :value="__('Profit Percentage')" />
                                    <x-text-input id="profit_percentage" class="block mt-1 w-full" type="number" step="0.01" name="profit_percentage" :value="old('profit_percentage', $investment->profit_percentage)" required />
                                    <x-input-error :messages="$errors->get('profit_percentage')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="investment_date" :value="__('Investment Date')" />
                                        <x-text-input id="investment_date" class="block mt-1 w-full" type="date" name="investment_date" :value="old('investment_date', $investment->investment_date->format('Y-m-d'))" required />
                                        <x-input-error :messages="$errors->get('investment_date')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="maturity_date" :value="__('Maturity Date')" />
                                        <x-text-input id="maturity_date" class="block mt-1 w-full" type="date" name="maturity_date" :value="old('maturity_date', $investment->maturity_date->format('Y-m-d'))" required />
                                        <x-input-error :messages="$errors->get('maturity_date')" class="mt-2" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="status" :value="__('Status')" />
                                    <select id="status" name="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="active" {{ old('status', $investment->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="matured" {{ old('status', $investment->status) == 'matured' ? 'selected' : '' }}>Matured</option>
                                        <option value="cancelled" {{ old('status', $investment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="payment_status" :value="__('Payment Status')" />
                                    <select id="payment_status" name="payment_status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="pending" {{ old('payment_status', $investment->payment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="partial" {{ old('payment_status', $investment->payment_status) == 'partial' ? 'selected' : '' }}>Partial</option>
                                        <option value="completed" {{ old('payment_status', $investment->payment_status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('payment_status')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="actual_profit" :value="__('Actual Profit (Optional)')" />
                                    <x-text-input id="actual_profit" class="block mt-1 w-full" type="number" step="0.01" name="actual_profit" :value="old('actual_profit', $investment->actual_profit)" />
                                    <x-input-error :messages="$errors->get('actual_profit')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="notes" :value="__('Notes')" />
                                    <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('notes', $investment->notes) }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Investment Summary -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium">Investment Summary</h3>
                                
                                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                    <div class="space-y-4">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Investment Amount:</span>
                                            <span class="font-medium" id="summary-amount">${{ number_format($investment->amount, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Profit Percentage:</span>
                                            <span class="font-medium" id="summary-percentage">{{ $investment->profit_percentage }}%</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Expected Profit:</span>
                                            <span class="font-medium text-green-600" id="summary-profit">${{ number_format($investment->expected_profit, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Actual Profit:</span>
                                            <span class="font-medium text-blue-600" id="summary-actual">${{ number_format($investment->actual_profit ?? 0, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Total Expected Return:</span>
                                            <span class="font-medium text-purple-600" id="summary-total">${{ number_format($investment->amount + $investment->expected_profit, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Current Investment Details -->
                                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                    <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Current Investment Details</h4>
                                    <div class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                                        <p><strong>Investor:</strong> {{ $investment->investor->full_name }} ({{ $investment->investor->investor_code }})</p>
                                        <p><strong>Order:</strong> {{ $investment->order->order_number }} - {{ $investment->order->title }}</p>
                                        <p><strong>Created:</strong> {{ $investment->created_at->format('M d, Y H:i') }}</p>
                                        <p><strong>Last Updated:</strong> {{ $investment->updated_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>

                                <!-- Warning -->
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                                    <h4 class="font-medium text-yellow-900 dark:text-yellow-100 mb-2">Important Notes</h4>
                                    <ul class="text-sm text-yellow-800 dark:text-yellow-200 space-y-1">
                                        <li>• Changing the investment amount will update the investor's totals</li>
                                        <li>• Expected profit will be recalculated automatically</li>
                                        <li>• Actual profit can be updated when profits are realized</li>
                                        <li>• Status changes should reflect the current investment state</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('investments.show', $investment) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Update Investment') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const amountInput = document.getElementById('amount');
            const percentageInput = document.getElementById('profit_percentage');
            const actualProfitInput = document.getElementById('actual_profit');
            const summaryAmount = document.getElementById('summary-amount');
            const summaryPercentage = document.getElementById('summary-percentage');
            const summaryProfit = document.getElementById('summary-profit');
            const summaryActual = document.getElementById('summary-actual');
            const summaryTotal = document.getElementById('summary-total');

            function updateSummary() {
                const amount = parseFloat(amountInput.value) || 0;
                const percentage = parseFloat(percentageInput.value) || 0;
                const actualProfit = parseFloat(actualProfitInput.value) || 0;
                const expectedProfit = (amount * percentage) / 100;
                const totalExpected = amount + expectedProfit;
                const totalActual = amount + actualProfit;

                summaryAmount.textContent = '$' + amount.toFixed(2);
                summaryPercentage.textContent = percentage + '%';
                summaryProfit.textContent = '$' + expectedProfit.toFixed(2);
                summaryActual.textContent = '$' + actualProfit.toFixed(2);
                summaryTotal.textContent = '$' + totalExpected.toFixed(2);
            }

            amountInput.addEventListener('input', updateSummary);
            percentageInput.addEventListener('input', updateSummary);
            actualProfitInput.addEventListener('input', updateSummary);
        });
    </script>
</x-app-layout>
