<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AccountingLedger;
use App\Models\User;
use App\Models\Investor;
use App\Models\Client;
use App\Models\Order;
use App\Models\Investment;

class AccountingDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = User::where('email', 'accounts@protraders.com')->first();
        $investor1 = Investor::first();
        $investor2 = Investor::skip(1)->first();
        $client1 = Client::first();
        $client2 = Client::skip(1)->first();
        $order1 = Order::first();
        $order2 = Order::skip(1)->first();

        // Create sample accounting ledger entries
        $entries = [
            // Investment income
            [
                'transaction_id' => AccountingLedger::generateTransactionId(),
                'transaction_date' => now()->subDays(30),
                'transaction_type' => 'investment',
                'category' => 'investment_income',
                'description' => 'Investment from ' . $investor1->full_name,
                'debit_amount' => 0,
                'credit_amount' => 50000.00,
                'balance' => 50000.00,
                'reference_type' => 'investment',
                'reference_id' => 1,
                'investor_id' => $investor1->id,
                'created_by' => $accounts->id,
                'status' => 'approved',
                'approved_by' => $accounts->id,
                'approved_at' => now()->subDays(30),
            ],
            [
                'transaction_id' => AccountingLedger::generateTransactionId(),
                'transaction_date' => now()->subDays(15),
                'transaction_type' => 'investment',
                'category' => 'investment_income',
                'description' => 'Investment from ' . $investor2->full_name,
                'debit_amount' => 0,
                'credit_amount' => 75000.00,
                'balance' => 125000.00,
                'reference_type' => 'investment',
                'reference_id' => 2,
                'investor_id' => $investor2->id,
                'created_by' => $accounts->id,
                'status' => 'approved',
                'approved_by' => $accounts->id,
                'approved_at' => now()->subDays(15),
            ],
            // Management fees
            [
                'transaction_id' => AccountingLedger::generateTransactionId(),
                'transaction_date' => now()->subDays(25),
                'transaction_type' => 'fee',
                'category' => 'management_fee',
                'description' => 'Management fee for ' . $order1->title,
                'debit_amount' => 0,
                'credit_amount' => 2500.00,
                'balance' => 127500.00,
                'reference_type' => 'order',
                'reference_id' => $order1->id,
                'created_by' => $accounts->id,
                'status' => 'approved',
                'approved_by' => $accounts->id,
                'approved_at' => now()->subDays(25),
            ],
            [
                'transaction_id' => AccountingLedger::generateTransactionId(),
                'transaction_date' => now()->subDays(10),
                'transaction_type' => 'fee',
                'category' => 'management_fee',
                'description' => 'Management fee for ' . $order2->title,
                'debit_amount' => 0,
                'credit_amount' => 3750.00,
                'balance' => 131250.00,
                'reference_type' => 'order',
                'reference_id' => $order2->id,
                'created_by' => $accounts->id,
                'status' => 'approved',
                'approved_by' => $accounts->id,
                'approved_at' => now()->subDays(10),
            ],
            // Performance fees
            [
                'transaction_id' => AccountingLedger::generateTransactionId(),
                'transaction_date' => now()->subDays(5),
                'transaction_type' => 'fee',
                'category' => 'performance_fee',
                'description' => 'Performance fee for ' . $order1->title,
                'debit_amount' => 0,
                'credit_amount' => 1000.00,
                'balance' => 132250.00,
                'reference_type' => 'order',
                'reference_id' => $order1->id,
                'created_by' => $accounts->id,
                'status' => 'approved',
                'approved_by' => $accounts->id,
                'approved_at' => now()->subDays(5),
            ],
            [
                'transaction_id' => AccountingLedger::generateTransactionId(),
                'transaction_date' => now()->subDays(3),
                'transaction_type' => 'fee',
                'category' => 'performance_fee',
                'description' => 'Performance fee for ' . $order2->title,
                'debit_amount' => 0,
                'credit_amount' => 1500.00,
                'balance' => 133750.00,
                'reference_type' => 'order',
                'reference_id' => $order2->id,
                'created_by' => $accounts->id,
                'status' => 'approved',
                'approved_by' => $accounts->id,
                'approved_at' => now()->subDays(3),
            ],
            // Operating expenses
            [
                'transaction_id' => AccountingLedger::generateTransactionId(),
                'transaction_date' => now()->subDays(20),
                'transaction_type' => 'expense',
                'category' => 'operating_expense',
                'description' => 'Office rent for current month',
                'debit_amount' => 5000.00,
                'credit_amount' => 0,
                'balance' => 128750.00,
                'created_by' => $accounts->id,
                'status' => 'approved',
                'approved_by' => $accounts->id,
                'approved_at' => now()->subDays(20),
            ],
            [
                'transaction_id' => AccountingLedger::generateTransactionId(),
                'transaction_date' => now()->subDays(15),
                'transaction_type' => 'expense',
                'category' => 'operating_expense',
                'description' => 'Staff salaries',
                'debit_amount' => 15000.00,
                'credit_amount' => 0,
                'balance' => 113750.00,
                'created_by' => $accounts->id,
                'status' => 'approved',
                'approved_by' => $accounts->id,
                'approved_at' => now()->subDays(15),
            ],
            [
                'transaction_id' => AccountingLedger::generateTransactionId(),
                'transaction_date' => now()->subDays(10),
                'transaction_type' => 'expense',
                'category' => 'operating_expense',
                'description' => 'Marketing and advertising',
                'debit_amount' => 3000.00,
                'credit_amount' => 0,
                'balance' => 110750.00,
                'created_by' => $accounts->id,
                'status' => 'approved',
                'approved_by' => $accounts->id,
                'approved_at' => now()->subDays(10),
            ],
            [
                'transaction_id' => AccountingLedger::generateTransactionId(),
                'transaction_date' => now()->subDays(5),
                'transaction_type' => 'expense',
                'category' => 'operating_expense',
                'description' => 'Utilities and internet',
                'debit_amount' => 1500.00,
                'credit_amount' => 0,
                'balance' => 109250.00,
                'created_by' => $accounts->id,
                'status' => 'approved',
                'approved_by' => $accounts->id,
                'approved_at' => now()->subDays(5),
            ],
        ];

        foreach ($entries as $entry) {
            AccountingLedger::create($entry);
        }
    }
}
