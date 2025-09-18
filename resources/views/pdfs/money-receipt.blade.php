<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Money Receipt - {{ $receipt->receipt_number }}</title>
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
        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            color: #1f2937;
            text-decoration: underline;
        }
        .receipt-info {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            color: #374151;
            width: 150px;
        }
        .info-value {
            color: #6b7280;
            flex: 1;
        }
        .amount-section {
            background-color: #f1f5f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .amount-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .amount-value {
            font-size: 28px;
            font-weight: bold;
            color: #059669;
        }
        .receipt-details {
            background-color: #fef3c7;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #f59e0b;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table th,
        .details-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }
        .details-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        .details-table .amount {
            text-align: right;
            font-weight: bold;
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
        .verification-badge {
            background-color: #10b981;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }
        .receipt-border {
            border: 2px solid #2563eb;
            border-radius: 10px;
            padding: 20px;
            margin: 10px;
        }
    </style>
</head>
<body>
    <div class="receipt-border">
        <!-- Header -->
        <div class="header">
            @if($company && $company->logo_path)
                <img src="{{ public_path($company->logo_path) }}" alt="Company Logo" class="company-logo">
            @endif
            <div class="company-name">Pro Traders Ltd</div>
            <div class="company-details">
                Smart Investor & Client Portal<br>
                Official Money Receipt
            </div>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">MONEY RECEIPT</div>

        @if($receipt->is_verified)
            <div class="verification-badge">✓ VERIFIED</div>
        @endif

        <!-- Receipt Information -->
        <div class="receipt-info">
            <div class="info-row">
                <span class="info-label">Receipt Number:</span>
                <span class="info-value">{{ $receipt->receipt_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Receipt Date:</span>
                <span class="info-value">{{ $receipt->receipt_date->format('M d, Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Investor Code:</span>
                <span class="info-value">{{ $investor->investor_code }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Investor Name:</span>
                <span class="info-value">{{ $investor->full_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Transaction ID:</span>
                <span class="info-value">{{ $receipt->transaction->transaction_id }}</span>
            </div>
        </div>

        <!-- Amount Section -->
        <div class="amount-section">
            <div class="amount-label">Amount Received</div>
            <div class="amount-value">${{ number_format($receipt->amount, 2) }}</div>
        </div>

        <!-- Receipt Details -->
        <div class="receipt-details">
            <h3 style="margin-top: 0; color: #92400e;">Receipt Details</h3>
            <table class="details-table">
                <tr>
                    <th>Description</th>
                    <td>{{ $receipt->description ?: ucfirst(str_replace('_', ' ', $receipt->receipt_type)) . ' Payment' }}</td>
                </tr>
                <tr>
                    <th>Receipt Type</th>
                    <td>{{ ucfirst(str_replace('_', ' ', $receipt->receipt_type)) }}</td>
                </tr>
                <tr>
                    <th>Payment Method</th>
                    <td>{{ $receipt->payment_method ?: 'Bank Transfer' }}</td>
                </tr>
                @if($receipt->reference_number)
                <tr>
                    <th>Reference Number</th>
                    <td>{{ $receipt->reference_number }}</td>
                </tr>
                @endif
                @if($receipt->investment)
                <tr>
                    <th>Investment Order</th>
                    <td>{{ $receipt->investment->order->title }} ({{ $receipt->investment->order->order_number }})</td>
                </tr>
                @endif
                <tr>
                    <th>Amount</th>
                    <td class="amount">${{ number_format($receipt->amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Transaction Details -->
        @if($receipt->transaction)
        <div style="margin-bottom: 20px;">
            <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #374151;">Transaction Information</h3>
            <table class="details-table">
                <tr>
                    <th>Transaction ID</th>
                    <td>{{ $receipt->transaction->transaction_id }}</td>
                </tr>
                <tr>
                    <th>Transaction Date</th>
                    <td>{{ $receipt->transaction->transaction_date->format('M d, Y H:i:s') }}</td>
                </tr>
                <tr>
                    <th>Transaction Type</th>
                    <td>{{ ucfirst(str_replace('_', ' ', $receipt->transaction->type)) }}</td>
                </tr>
                <tr>
                    <th>Entry Type</th>
                    <td>{{ ucfirst($receipt->transaction->entry_type) }}</td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td>{{ $receipt->transaction->description }}</td>
                </tr>
                <tr>
                    <th>Balance After</th>
                    <td class="amount">${{ number_format($receipt->transaction->balance_after, 2) }}</td>
                </tr>
            </table>
        </div>
        @endif

        <!-- Verification Information -->
        @if($receipt->is_verified && $receipt->verifier)
        <div style="background-color: #ecfdf5; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #10b981;">
            <h3 style="margin-top: 0; color: #065f46;">Verification Details</h3>
            <div class="info-row">
                <span class="info-label">Verified By:</span>
                <span class="info-value">{{ $receipt->verifier->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Verification Date:</span>
                <span class="info-value">{{ $receipt->verified_at->format('M d, Y H:i:s') }}</span>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>Important Notes:</strong></p>
            <ul>
                <li>This receipt serves as official proof of payment received.</li>
                <li>Please keep this receipt for your records.</li>
                <li>For any discrepancies, please contact our support team within 7 days.</li>
                <li>This receipt is confidential and intended for the investor only.</li>
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
                @if($receipt->digital_signature_path)
                    <img src="{{ public_path($receipt->digital_signature_path) }}" alt="Digital Signature" class="digital-signature">
                @endif
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Date</div>
                <div style="margin-top: 10px;">{{ $receipt->receipt_date->format('M d, Y') }}</div>
            </div>
            <div class="signature-box">
                @if($receipt->company_stamp_path)
                    <img src="{{ public_path($receipt->company_stamp_path) }}" alt="Company Stamp" class="company-stamp">
                @elseif($company && $company->logo_path)
                    <img src="{{ public_path($company->logo_path) }}" alt="Company Stamp" class="company-stamp">
                @endif
                <div class="signature-label">Company Stamp</div>
            </div>
        </div>
    </div>
</body>
</html>