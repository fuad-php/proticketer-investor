<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Client Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">{{ $client->name }}</h3>
                        <div class="flex space-x-2">
                            @can('edit clients')
                                <a href="{{ route('clients.edit', $client) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Edit Client
                                </a>
                            @endcan
                            <a href="{{ route('clients.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Back to Clients
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Client Information -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <h4 class="text-lg font-medium mb-4">Client Information</h4>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $client->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $client->email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $client->phone ?: 'Not provided' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Company</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $client->company_name ?: 'Not provided' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact Person</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $client->contact_person ?: 'Not specified' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                        <dd class="text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $client->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $client->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $client->created_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                    @if($client->address)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</dt>
                                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $client->address }}</dd>
                                        </div>
                                    @endif
                                    @if($client->notes)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $client->notes }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        <!-- Inquiries -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <h4 class="text-lg font-medium mb-4">Inquiries ({{ $client->inquiries->count() }})</h4>
                                @if($client->inquiries->count() > 0)
                                    <div class="space-y-3">
                                        @foreach($client->inquiries->take(5) as $inquiry)
                                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h5 class="font-medium">{{ $inquiry->subject }}</h5>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $inquiry->inquiry_number }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                            @if($inquiry->status === 'completed') bg-green-100 text-green-800
                                                            @elseif($inquiry->status === 'in_progress') bg-blue-100 text-blue-800
                                                            @elseif($inquiry->status === 'quoted') bg-yellow-100 text-yellow-800
                                                            @else bg-gray-100 text-gray-800
                                                            @endif">
                                                            {{ ucfirst($inquiry->status) }}
                                                        </span>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $inquiry->created_at->format('M d, Y') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @if($client->inquiries->count() > 5)
                                            <div class="text-center">
                                                <a href="{{ route('inquiries.index', ['client_id' => $client->id]) }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                                    View all {{ $client->inquiries->count() }} inquiries
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No inquiries</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This client hasn't submitted any inquiries yet.</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Quick Actions -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Quick Actions</h4>
                                <div class="space-y-2">
                                    <a href="mailto:{{ $client->email }}" class="block text-sm text-blue-800 dark:text-blue-200 hover:text-blue-900 dark:hover:text-blue-100">
                                        📧 Send Email
                                    </a>
                                    @if($client->phone)
                                        <a href="tel:{{ $client->phone }}" class="block text-sm text-blue-800 dark:text-blue-200 hover:text-blue-900 dark:hover:text-blue-100">
                                            📞 Call Client
                                        </a>
                                    @endif
                                    <a href="{{ route('inquiries.create', ['client_id' => $client->id]) }}" class="block text-sm text-blue-800 dark:text-blue-200 hover:text-blue-900 dark:hover:text-blue-100">
                                        📝 Create Inquiry
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
