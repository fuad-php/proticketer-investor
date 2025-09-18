<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investment Statement - {{ $statement->statement_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        .company-logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 10px;
            color: #666;
        }
        .statement-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            color: #1f2937;
        }
        .investor-info {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #374151;
        }
        .info-value {
            color: #6b7280;
        }
        .statement-details {
            background-color: #f1f5f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-table th,
        .summary-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }
        .summary-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        .summary-table .amount {
            text-align: right;
            font-weight: bold;
        }
        .summary-table .total-row {
            background-color: #e5e7eb;
            font-weight: bold;
        }
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .transaction-table th,
        .transaction-table td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
        .transaction-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        .transaction-table .amount {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #d1d5db;
            padding-top: 20px;
            font-size: 10px;
            color: #6b7280;
        }
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 200px;
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
            height: 40px;
        }
        .signature-label {
            font-size: 10px;
            color: #6b7280;
        }
        .digital-signature {
            max-width: 150px;
            max-height: 50px;
        }
        .company-stamp {
            max-width: 100px;
            max-height: 100px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        @if($company && $company->logo_path)
            <img src="{{ public_path($company->logo_path) }}" alt="Company Logo" class="company-logo">
        @endif
        <div class="company-name">Pro Traders Ltd</div>
        <div class="company-details">
            Smart Investor & Client Portal<br>
            Statement Period: {{ $statement->period_start->format('M d, Y') }} - {{ $statement->period_end->format('M d, Y') }}
        </div>
    </div>

    <!-- Statement Title -->
    <div class="statement-title">INVESTMENT STATEMENT</div>

    <!-- Investor Information -->
    <div class="investor-info">
        <div class="info-row">
            <span class="info-label">Investor Code:</span>
            <span class="info-value">{{ $investor->investor_code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Full Name:</span>
            <span class="info-value">{{ $investor->full_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $investor->email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Statement Number:</span>
            <span class="info-value">{{ $statement->statement_number }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Statement Date:</span>
            <span class="info-value">{{ $statement->statement_date->format('M d, Y') }}</span>
        </div>
    </div>

    <!-- Statement Details -->
    <div class="statement-details">
        <div class="info-row">
            <span class="info-label">Investment Order:</span>
            <span class="info-value">{{ $statement->order->title }} ({{ $statement->order->order_number }})</span>
        </div>
        <div class="info-row">
            <span class="info-label">Investment Period:</span>
            <span class="info-value">{{ $statement->order->start_date->format('M d, Y') }} - {{ $statement->order->end_date->format('M d, Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Profit Percentage:</span>
            <span class="info-value">{{ $statement->order->profit_percentage }}%</span>
        </div>
    </div>

    <!-- Summary Table -->
    <table class="summary-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="amount">Amount ($)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Opening Balance</td>
                <td class="amount">{{ number_format($statement->opening_balance, 2) }}</td>
            </tr>
            <tr>
                <td>Total Investments</td>
                <td class="amount">{{ number_format($statement->total_investments, 2) }}</td>
            </tr>
            <tr>
                <td>Total Profits</td>
                <td class="amount">{{ number_format($statement->total_profits, 2) }}</td>
            </tr>
            <tr>
                <td>Total Payouts</td>
                <td class="amount">-{{ number_format($statement->total_payouts, 2) }}</td>
            </tr>
            <tr>
                <td>Management Fees</td>
                <td class="amount">-{{ number_format($statement->management_fees, 2) }}</td>
            </tr>
            <tr>
                <td>Performance Fees</td>
                <td class="amount">-{{ number_format($statement->performance_fees, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td><strong>Closing Balance</strong></td>
                <td class="amount"><strong>{{ number_format($statement->closing_balance, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Transaction Details -->
    @if($statement->transaction_summary && count($statement->transaction_summary) > 0)
        <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #374151;">Transaction Details</h3>
        <table class="transaction-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th class="amount">Amount</th>
                    <th class="amount">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statement->transaction_summary as $transaction)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($transaction['date'])->format('M d, Y') }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $transaction['type'])) }}</td>
                        <td>{{ $transaction['description'] }}</td>
                        <td class="amount">{{ $transaction['entry_type'] === 'credit' ? '+' : '-' }}{{ number_format($transaction['amount'], 2) }}</td>
                        <td class="amount">{{ number_format($transaction['balance_after'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>Important Notes:</strong></p>
        <ul>
            <li>This statement is generated automatically and is valid as of the statement date.</li>
            <li>All amounts are in USD unless otherwise specified.</li>
            <li>For any discrepancies, please contact our support team within 30 days.</li>
            <li>This statement is confidential and intended for the investor only.</li>
        </ul>
        
        <p style="margin-top: 15px;">
            <strong>Contact Information:</strong><br>
            Pro Traders Ltd<br>
            Email: support@protraders.com<br>
            Phone: +1 (555) 123-4567
        </p>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Authorized Signature</div>
            @if($statement->digital_signature_path)
                <img src="{{ public_path($statement->digital_signature_path) }}" alt="Digital Signature" class="digital-signature">
            @endif
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Date</div>
            <div style="margin-top: 10px;">{{ $statement->published_at ? $statement->published_at->format('M d, Y') : $statement->statement_date->format('M d, Y') }}</div>
        </div>
        <div class="signature-box">
            @if($company && $company->logo_path)
                <img src="{{ public_path($company->logo_path) }}" alt="Company Stamp" class="company-stamp">
            @endif
            <div class="signature-label">Company Stamp</div>
        </div>
    </div>
</body>
</html>