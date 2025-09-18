<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Client;
use App\Models\Investor;
use App\Models\Order;
use App\Models\Investment;
use App\Models\Approval;
use App\Models\Transaction;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create company
        $company = Company::create([
            'name' => 'Pro Traders Ltd',
            'legal_name' => 'Pro Traders Limited',
            'registration_number' => 'REG-2024-001',
            'tax_id' => 'TAX-2024-001',
            'address' => '123 Business Street, Financial District, City, Country',
            'phone' => '+1 (555) 123-4567',
            'email' => 'info@protraders.com',
            'website' => 'https://protraders.com',
            'description' => 'Leading investment management company',
            'is_active' => true,
        ]);

        // Create users with different roles
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@protraders.com',
            'password' => Hash::make('password'),
            'phone' => '+1 (555) 111-1111',
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super_admin');

        $accounts = User::create([
            'name' => 'Accounts Manager',
            'email' => 'accounts@protraders.com',
            'password' => Hash::make('password'),
            'phone' => '+1 (555) 222-2222',
            'is_active' => true,
        ]);
        $accounts->assignRole('accounts');

        $director = User::create([
            'name' => 'John Director',
            'email' => 'director@protraders.com',
            'password' => Hash::make('password'),
            'phone' => '+1 (555) 333-3333',
            'is_active' => true,
        ]);
        $director->assignRole('director');

        $md = User::create([
            'name' => 'Jane Managing Director',
            'email' => 'md@protraders.com',
            'password' => Hash::make('password'),
            'phone' => '+1 (555) 444-4444',
            'is_active' => true,
        ]);
        $md->assignRole('managing_director');

        $chairman = User::create([
            'name' => 'Robert Chairman',
            'email' => 'chairman@protraders.com',
            'password' => Hash::make('password'),
            'phone' => '+1 (555) 555-5555',
            'is_active' => true,
        ]);
        $chairman->assignRole('chairman');

        // Create investor users
        $investor1 = User::create([
            'name' => 'Alice Investor',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'phone' => '+1 (555) 666-6666',
            'is_active' => true,
        ]);
        $investor1->assignRole('investor');

        $investor2 = User::create([
            'name' => 'Bob Investor',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'phone' => '+1 (555) 777-7777',
            'is_active' => true,
        ]);
        $investor2->assignRole('investor');

        // Create client user
        $clientUser = User::create([
            'name' => 'Charlie Client',
            'email' => 'charlie@example.com',
            'password' => Hash::make('password'),
            'phone' => '+1 (555) 888-8888',
            'is_active' => true,
        ]);
        $clientUser->assignRole('client');

        // Create clients
        $client1 = Client::create([
            'name' => 'ABC Corporation',
            'email' => 'charlie@example.com',
            'phone' => '+1 (555) 888-8888',
            'company_name' => 'ABC Corporation',
            'address' => '456 Corporate Avenue, Business District',
            'contact_person' => 'Charlie Client',
            'is_active' => true,
        ]);

        $client2 = Client::create([
            'name' => 'XYZ Industries',
            'email' => 'xyz@example.com',
            'phone' => '+1 (555) 999-9999',
            'company_name' => 'XYZ Industries',
            'address' => '789 Industrial Park, Manufacturing Zone',
            'contact_person' => 'David Manager',
            'is_active' => true,
        ]);

        // Create investors
        $investorProfile1 = Investor::create([
            'user_id' => $investor1->id,
            'investor_code' => 'INV-ALICE',
            'full_name' => 'Alice Investor',
            'email' => 'alice@example.com',
            'phone' => '+1 (555) 666-6666',
            'address' => '100 Investment Street, Wealth District',
            'nid_number' => 'NID123456789',
            'bank_name' => 'First National Bank',
            'bank_account' => '1234567890',
            'bank_routing' => '021000021',
            'total_invested' => 50000.00,
            'total_profit' => 5000.00,
            'current_balance' => 55000.00,
            'is_active' => true,
        ]);

        $investorProfile2 = Investor::create([
            'user_id' => $investor2->id,
            'investor_code' => 'INV-BOB',
            'full_name' => 'Bob Investor',
            'email' => 'bob@example.com',
            'phone' => '+1 (555) 777-7777',
            'address' => '200 Finance Avenue, Capital City',
            'nid_number' => 'NID987654321',
            'bank_name' => 'Second National Bank',
            'bank_account' => '0987654321',
            'bank_routing' => '021000022',
            'total_invested' => 75000.00,
            'total_profit' => 7500.00,
            'current_balance' => 82500.00,
            'is_active' => true,
        ]);

        // Create orders
        $order1 = Order::create([
            'order_number' => 'ORD-TECH001',
            'client_id' => $client1->id,
            'created_by' => $accounts->id,
            'title' => 'Technology Investment Fund',
            'description' => 'Investment in emerging technology companies',
            'total_amount' => 100000.00,
            'profit_percentage' => 12.5,
            'start_date' => now()->subDays(30),
            'end_date' => now()->addDays(90),
            'status' => 'active',
            'payment_status' => 'completed',
            'notes' => 'High-growth technology sector investment',
        ]);

        $order2 = Order::create([
            'order_number' => 'ORD-REAL001',
            'client_id' => $client2->id,
            'created_by' => $accounts->id,
            'title' => 'Real Estate Development',
            'description' => 'Investment in commercial real estate development',
            'total_amount' => 200000.00,
            'profit_percentage' => 15.0,
            'start_date' => now()->subDays(15),
            'end_date' => now()->addDays(120),
            'status' => 'active',
            'payment_status' => 'completed',
            'notes' => 'Commercial real estate investment opportunity',
        ]);

        // Create investments
        $investment1 = Investment::create([
            'order_id' => $order1->id,
            'investor_id' => $investorProfile1->id,
            'amount' => 50000.00,
            'profit_percentage' => 12.5,
            'expected_profit' => 6250.00,
            'actual_profit' => 5000.00,
            'investment_date' => now()->subDays(30),
            'maturity_date' => now()->addDays(90),
            'status' => 'active',
            'payment_status' => 'completed',
        ]);

        $investment2 = Investment::create([
            'order_id' => $order2->id,
            'investor_id' => $investorProfile2->id,
            'amount' => 75000.00,
            'profit_percentage' => 15.0,
            'expected_profit' => 11250.00,
            'actual_profit' => 7500.00,
            'investment_date' => now()->subDays(15),
            'maturity_date' => now()->addDays(120),
            'status' => 'active',
            'payment_status' => 'completed',
        ]);

        // Create transactions
        Transaction::create([
            'transaction_id' => 'TXN-INV001',
            'investor_id' => $investorProfile1->id,
            'investment_id' => $investment1->id,
            'order_id' => $order1->id,
            'type' => 'investment',
            'entry_type' => 'debit',
            'amount' => 50000.00,
            'balance_after' => 50000.00,
            'description' => 'Investment in Technology Investment Fund',
            'created_by' => $accounts->id,
            'transaction_date' => now()->subDays(30),
        ]);

        Transaction::create([
            'transaction_id' => 'TXN-PROF001',
            'investor_id' => $investorProfile1->id,
            'investment_id' => $investment1->id,
            'order_id' => $order1->id,
            'type' => 'profit_payout',
            'entry_type' => 'credit',
            'amount' => 5000.00,
            'balance_after' => 55000.00,
            'description' => 'Profit payout from Technology Investment Fund',
            'created_by' => $accounts->id,
            'transaction_date' => now()->subDays(5),
        ]);

        Transaction::create([
            'transaction_id' => 'TXN-INV002',
            'investor_id' => $investorProfile2->id,
            'investment_id' => $investment2->id,
            'order_id' => $order2->id,
            'type' => 'investment',
            'entry_type' => 'debit',
            'amount' => 75000.00,
            'balance_after' => 75000.00,
            'description' => 'Investment in Real Estate Development',
            'created_by' => $accounts->id,
            'transaction_date' => now()->subDays(15),
        ]);

        Transaction::create([
            'transaction_id' => 'TXN-PROF002',
            'investor_id' => $investorProfile2->id,
            'investment_id' => $investment2->id,
            'order_id' => $order2->id,
            'type' => 'profit_payout',
            'entry_type' => 'credit',
            'amount' => 7500.00,
            'balance_after' => 82500.00,
            'description' => 'Profit payout from Real Estate Development',
            'created_by' => $accounts->id,
            'transaction_date' => now()->subDays(3),
        ]);

        // Create inquiries
        Inquiry::create([
            'inquiry_number' => 'INQ-TECH001',
            'client_id' => $client1->id,
            'subject' => 'Technology Investment Opportunity',
            'description' => 'We are interested in learning more about your technology investment opportunities.',
            'category' => 'Investment',
            'quantity' => '1',
            'specifications' => 'Looking for high-growth technology sector investments',
            'preferred_timeframe' => now()->addDays(30),
            'status' => 'completed',
            'assigned_to' => $accounts->id,
            'response' => 'Thank you for your interest. We have several technology investment opportunities available. Please contact us to discuss further.',
        ]);

        Inquiry::create([
            'inquiry_number' => 'INQ-REAL001',
            'client_id' => $client2->id,
            'subject' => 'Real Estate Investment Inquiry',
            'description' => 'We would like to explore real estate investment options.',
            'category' => 'Investment',
            'quantity' => '1',
            'specifications' => 'Commercial real estate development projects',
            'preferred_timeframe' => now()->addDays(45),
            'status' => 'in_progress',
            'assigned_to' => $accounts->id,
            'response' => 'We are currently reviewing your requirements and will provide detailed information soon.',
        ]);
    }
}
