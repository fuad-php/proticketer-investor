<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Transaction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Back Button -->
                    <div class="mb-6">
                        <a href="{{ route('transactions.show', $transaction) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            ← Back to Transaction
                        </a>
                    </div>

                    <!-- Edit Form -->
                    <form method="POST" action="{{ route('transactions.update', $transaction) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Investor Selection -->
                            <div>
                                <label for="investor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Investor *</label>
                                <select name="investor_id" id="investor_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select an investor</option>
                                    @foreach($investors as $investor)
                                        <option value="{{ $investor->id }}" {{ (old('investor_id', $transaction->investor_id) == $investor->id) ? 'selected' : '' }}>
                                            {{ $investor->name }} ({{ $investor->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('investor_id')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Transaction Type -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction Type *</label>
                                <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select transaction type</option>
                                    <option value="investment" {{ (old('type', $transaction->type) == 'investment') ? 'selected' : '' }}>Investment</option>
                                    <option value="payout" {{ (old('type', $transaction->type) == 'payout') ? 'selected' : '' }}>Payout</option>
                                    <option value="refund" {{ (old('type', $transaction->type) == 'refund') ? 'selected' : '' }}>Refund</option>
                                    <option value="fee" {{ (old('type', $transaction->type) == 'fee') ? 'selected' : '' }}>Fee</option>
                                </select>
                                @error('type')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Amount -->
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount *</label>
                                <input type="number" name="amount" id="amount" step="0.01" min="0" required value="{{ old('amount', $transaction->amount) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('amount')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Transaction Date -->
                            <div>
                                <label for="transaction_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction Date *</label>
                                <input type="date" name="transaction_date" id="transaction_date" required value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('transaction_date')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Investment Selection -->
                            <div>
                                <label for="investment_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Related Investment</label>
                                <select name="investment_id" id="investment_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select an investment (optional)</option>
                                    @foreach($investments as $investment)
                                        <option value="{{ $investment->id }}" {{ (old('investment_id', $transaction->investment_id) == $investment->id) ? 'selected' : '' }}>
                                            {{ $investment->order->title ?? 'Investment' }} - ${{ number_format($investment->amount, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('investment_id')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Order Selection -->
                            <div>
                                <label for="order_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Related Order</label>
                                <select name="order_id" id="order_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select an order (optional)</option>
                                    @foreach($orders as $order)
                                        <option value="{{ $order->id }}" {{ (old('order_id', $transaction->order_id) == $order->id) ? 'selected' : '' }}>
                                            {{ $order->order_number }} - {{ $order->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('order_id')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status *</label>
                                <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="pending" {{ (old('status', $transaction->status) == 'pending') ? 'selected' : '' }}>Pending</option>
                                    <option value="completed" {{ (old('status', $transaction->status) == 'completed') ? 'selected' : '' }}>Completed</option>
                                    <option value="failed" {{ (old('status', $transaction->status) == 'failed') ? 'selected' : '' }}>Failed</option>
                                </select>
                                @error('status')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                                <select name="payment_method" id="payment_method" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select payment method</option>
                                    <option value="bank_transfer" {{ (old('payment_method', $transaction->payment_method) == 'bank_transfer') ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="credit_card" {{ (old('payment_method', $transaction->payment_method) == 'credit_card') ? 'selected' : '' }}>Credit Card</option>
                                    <option value="debit_card" {{ (old('payment_method', $transaction->payment_method) == 'debit_card') ? 'selected' : '' }}>Debit Card</option>
                                    <option value="cash" {{ (old('payment_method', $transaction->payment_method) == 'cash') ? 'selected' : '' }}>Cash</option>
                                    <option value="check" {{ (old('payment_method', $transaction->payment_method) == 'check') ? 'selected' : '' }}>Check</option>
                                    <option value="other" {{ (old('payment_method', $transaction->payment_method) == 'other') ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('payment_method')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Reference Number -->
                        <div>
                            <label for="reference_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reference Number</label>
                            <input type="text" name="reference_number" id="reference_number" value="{{ old('reference_number', $transaction->reference_number) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Transaction reference or tracking number">
                            @error('reference_number')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                            <textarea name="notes" id="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Additional notes about this transaction">{{ old('notes', $transaction->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('transactions.show', $transaction) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Update Transaction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
