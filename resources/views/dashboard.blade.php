<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }} - Welcome, {{ Auth::user()->name }}!
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Role-based Dashboard Content -->
            @if(Auth::user()->hasRole('super_admin'))
                <!-- Super Admin Dashboard -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Investors</dt>
                                        <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $stats['total_investors'] ?? 0 }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Investments</dt>
                                        <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">${{ number_format($stats['total_aum'] ?? 0, 2) }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Pending Approvals</dt>
                                        <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $stats['pending_approvals'] ?? 0 }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Clients</dt>
                                        <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $stats['total_clients'] ?? 0 }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">System Overview</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Welcome to the Pro Traders Ltd Smart Investor & Client Portal. As a Super Admin, you have full access to all system features including user management, investment oversight, and system security.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Manage Users
                            </a>
                            <a href="{{ route('reports.index') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                View Reports
                            </a>
                        </div>
                    </div>
                </div>

            @elseif(Auth::user()->hasRole('investor'))
                <!-- Investor Dashboard -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Investment Portfolio</h3>
                        @if(isset($investor) && $investor)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-green-600">${{ number_format($stats['total_invested'] ?? 0, 2) }}</div>
                                    <div class="text-sm text-gray-500">Total Invested</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-blue-600">${{ number_format($stats['total_profit'] ?? 0, 2) }}</div>
                                    <div class="text-sm text-gray-500">Total Profit</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-purple-600">${{ number_format($stats['current_balance'] ?? 0, 2) }}</div>
                                    <div class="text-sm text-gray-500">Current Balance</div>
                                </div>
                            </div>
                            <div class="mt-6">
                                <a href="{{ route('investor.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Full Dashboard
                                </a>
                                <a href="{{ route('investor.statements') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    View Statements
                                </a>
                                <a href="{{ route('investor.receipts') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Download Receipts
                                </a>
                            </div>
                        @else
                            <p class="text-gray-600 dark:text-gray-400">Please complete your investor profile to view your investment details.</p>
                        @endif
                    </div>
                </div>

            @elseif(Auth::user()->hasRole('client'))
                <!-- Client Dashboard -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Client Portal</h3>
                        @if(isset($client) && $client)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-blue-600">{{ $stats['total_inquiries'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-500">Total Inquiries</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending_inquiries'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-500">Pending</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-green-600">{{ $stats['completed_inquiries'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-500">Completed</div>
                                </div>
                            </div>
                        @endif
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Welcome to your client portal. You can submit inquiries and track their status here.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('client.inquiries') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                View My Inquiries
                            </a>
                        </div>
                    </div>
                </div>

            @else
                <!-- Default Dashboard for other roles -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Welcome to Pro Traders Ltd</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            You're logged in as {{ ucfirst(str_replace('_', ' ', Auth::user()->roles->first()->name ?? 'User')) }}. 
                            Use the navigation menu to access your available features.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
