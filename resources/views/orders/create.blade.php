<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create New Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('orders.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Order Details -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium">Order Information</h3>
                                
                                <div>
                                    <x-input-label for="title" :value="__('Order Title')" />
                                    <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required autofocus />
                                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="description" :value="__('Description')" />
                                    <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description') }}</textarea>
                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="client_id" :value="__('Client')" />
                                    <select id="client_id" name="client_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Select a client (optional)</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                                {{ $client->name }} ({{ $client->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="total_amount" :value="__('Total Amount')" />
                                    <x-text-input id="total_amount" class="block mt-1 w-full" type="number" step="0.01" name="total_amount" :value="old('total_amount')" required />
                                    <x-input-error :messages="$errors->get('total_amount')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="profit_percentage" :value="__('Profit Percentage')" />
                                    <x-text-input id="profit_percentage" class="block mt-1 w-full" type="number" step="0.01" name="profit_percentage" :value="old('profit_percentage')" required />
                                    <x-input-error :messages="$errors->get('profit_percentage')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="start_date" :value="__('Start Date')" />
                                        <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date')" required />
                                        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="end_date" :value="__('End Date')" />
                                        <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date')" required />
                                        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="notes" :value="__('Notes')" />
                                    <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Investment Details -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium">Investment Allocation</h3>
                                
                                <div id="investors-container">
                                    <div class="investor-row border border-gray-300 dark:border-gray-600 rounded-lg p-4 mb-4">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="font-medium">Investor 1</h4>
                                            <button type="button" class="text-red-600 hover:text-red-800 remove-investor" style="display: none;">Remove</button>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 gap-4">
                                            <div>
                                                <x-input-label for="investors[0][investor_id]" :value="__('Investor')" />
                                                <select name="investors[0][investor_id]" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                                    <option value="">Select an investor</option>
                                                    @foreach($investors as $investor)
                                                        <option value="{{ $investor->id }}">
                                                            {{ $investor->full_name }} ({{ $investor->investor_code }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <div>
                                                <x-input-label for="investors[0][amount]" :value="__('Investment Amount')" />
                                                <x-text-input name="investors[0][amount]" class="block mt-1 w-full" type="number" step="0.01" required />
                                            </div>
                                            
                                            <div>
                                                <x-input-label for="investors[0][notes]" :value="__('Notes')" />
                                                <textarea name="investors[0][notes]" rows="2" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" id="add-investor" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Add Another Investor
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Create Order') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let investorCount = 1;
            
            document.getElementById('add-investor').addEventListener('click', function() {
                const container = document.getElementById('investors-container');
                const newRow = container.firstElementChild.cloneNode(true);
                
                // Update the investor number
                const investorNumber = investorCount + 1;
                newRow.querySelector('h4').textContent = `Investor ${investorNumber}`;
                
                // Update input names and IDs
                newRow.querySelectorAll('select, input, textarea').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        input.setAttribute('name', name.replace('[0]', `[${investorCount}]`));
                    }
                    input.value = '';
                });
                
                // Show remove button
                newRow.querySelector('.remove-investor').style.display = 'block';
                
                container.appendChild(newRow);
                investorCount++;
                
                // Update remove button visibility
                updateRemoveButtons();
            });
            
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-investor')) {
                    e.target.closest('.investor-row').remove();
                    updateRemoveButtons();
                }
            });
            
            function updateRemoveButtons() {
                const rows = document.querySelectorAll('.investor-row');
                rows.forEach((row, index) => {
                    const removeBtn = row.querySelector('.remove-investor');
                    if (rows.length > 1) {
                        removeBtn.style.display = 'block';
                    } else {
                        removeBtn.style.display = 'none';
                    }
                });
            }
        });
    </script>
</x-app-layout>
