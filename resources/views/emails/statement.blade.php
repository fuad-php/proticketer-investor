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
            background-color: #17a2b8;
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
            background-color: #17a2b8;
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
        .highlight {
            background-color: #e7f3ff;
            padding: 10px;
            border-left: 4px solid #17a2b8;
            margin: 15px 0;
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
        
        <div class="highlight">
            <p><strong>Your investment statement is now available for download.</strong></p>
        </div>
        
        @if(isset($data['statement_number']))
        <div class="details">
            <h3>Statement Details:</h3>
            <p><strong>Statement Number:</strong> #{{ $data['statement_number'] }}</p>
            <p><strong>Period:</strong> {{ $data['period_start'] ?? 'N/A' }} to {{ $data['period_end'] ?? 'N/A' }}</p>
            <p><strong>Generated:</strong> {{ now()->format('M d, Y H:i') }}</p>
        </div>
        @endif
        
        <p>This statement contains detailed information about your investments, including:</p>
        <ul>
            <li>Current investment balances</li>
            <li>Profit and loss statements</li>
            <li>Transaction history</li>
            <li>Performance metrics</li>
        </ul>
        
        <a href="{{ route('investor.statements') }}" class="button">
            Download Statement
        </a>
        
        <p>Please keep this statement for your records. If you have any questions about your investments, please contact our support team.</p>
        
        <p>Best regards,<br>
        {{ config('app.name') }} Team</p>
    </div>
    
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>
