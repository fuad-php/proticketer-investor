<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $notification->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #28a745;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .button {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
        .details {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <h2>{{ $notification->title }}</h2>
    </div>
    
    <div class="content">
        <p>Hello {{ $recipient->name }},</p>
        
        <p>{{ $notification->message }}</p>
        
        @if(isset($data['amount']))
        <div class="details">
            <h3>Investment Details:</h3>
            <p><strong>Amount:</strong> <span class="amount">${{ number_format($data['amount'], 2) }}</span></p>
            <p><strong>Investment ID:</strong> #{{ $data['investment_id'] ?? 'N/A' }}</p>
            <p><strong>Date:</strong> {{ now()->format('M d, Y H:i') }}</p>
            @if(isset($data['profit_percentage']))
            <p><strong>Expected Return:</strong> {{ $data['profit_percentage'] }}%</p>
            @endif
        </div>
        @endif
        
        <p>You can view your investment details and track its performance in your investor dashboard.</p>
        
        <a href="{{ route('investor.dashboard') }}" class="button">
            View Investment Dashboard
        </a>
        
        <p>If you have any questions about your investment, please contact our support team.</p>
        
        <p>Best regards,<br>
        {{ config('app.name') }} Team</p>
    </div>
    
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>
