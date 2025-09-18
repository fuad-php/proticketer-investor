<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Investor;
use App\Models\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;
use Exception;

class NotificationService
{
    protected $twilio;

    public function __construct()
    {
        if (config('services.twilio.sid') && config('services.twilio.token')) {
            $this->twilio = new TwilioClient(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );
        }
    }

    /**
     * Send email notification
     */
    public function sendEmail(Notification $notification): bool
    {
        try {
            $recipient = $this->getRecipient($notification);
            
            if (!$recipient || !$recipient->email) {
                $notification->markAsFailed('No email address found for recipient');
                return false;
            }

            // Check if user has email notifications enabled
            if ($recipient instanceof User && !$recipient->email_notifications) {
                $notification->markAsFailed('Email notifications disabled for user');
                return false;
            }

            $template = $this->getEmailTemplate($notification->channel);
            $subject = $this->getEmailSubject($notification->channel, $notification->title);
            
            Mail::send($template, [
                'notification' => $notification,
                'recipient' => $recipient,
                'data' => $notification->data,
            ], function ($message) use ($recipient, $subject) {
                $message->to($recipient->email, $recipient->name)
                        ->subject($subject);
            });

            $notification->markAsSent();
            return true;

        } catch (Exception $e) {
            Log::error('Email notification failed: ' . $e->getMessage());
            $notification->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS notification
     */
    public function sendSms(Notification $notification): bool
    {
        try {
            $recipient = $this->getRecipient($notification);
            
            if (!$recipient || !$recipient->phone) {
                $notification->markAsFailed('No phone number found for recipient');
                return false;
            }

            // Check if user has SMS notifications enabled
            if ($recipient instanceof User && !$recipient->sms_notifications) {
                $notification->markAsFailed('SMS notifications disabled for user');
                return false;
            }

            if (!$this->twilio) {
                $notification->markAsFailed('Twilio not configured');
                return false;
            }

            $message = $this->formatSmsMessage($notification);
            
            $twilioMessage = $this->twilio->messages->create(
                $recipient->phone,
                [
                    'from' => config('services.twilio.from'),
                    'body' => $message
                ]
            );

            $notification->markAsSent($twilioMessage->sid);
            return true;

        } catch (Exception $e) {
            Log::error('SMS notification failed: ' . $e->getMessage());
            $notification->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Send push notification (placeholder for future implementation)
     */
    public function sendPush(Notification $notification): bool
    {
        // Placeholder for push notification implementation
        // Could integrate with Firebase, OneSignal, etc.
        $notification->markAsFailed('Push notifications not implemented');
        return false;
    }

    /**
     * Send in-app notification
     */
    public function sendInApp(Notification $notification): bool
    {
        try {
            // In-app notifications are stored in database and displayed in UI
            $notification->markAsSent();
            return true;

        } catch (Exception $e) {
            Log::error('In-app notification failed: ' . $e->getMessage());
            $notification->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Send notification based on type
     */
    public function send(Notification $notification): bool
    {
        switch ($notification->type) {
            case 'email':
                return $this->sendEmail($notification);
            case 'sms':
                return $this->sendSms($notification);
            case 'push':
                return $this->sendPush($notification);
            case 'in_app':
                return $this->sendInApp($notification);
            default:
                $notification->markAsFailed('Unknown notification type');
                return false;
        }
    }

    /**
     * Send multiple notifications
     */
    public function sendMultiple(array $notifications): array
    {
        $results = [];
        
        foreach ($notifications as $notification) {
            $results[] = [
                'notification' => $notification,
                'success' => $this->send($notification)
            ];
        }

        return $results;
    }

    /**
     * Process pending notifications
     */
    public function processPending(): int
    {
        $notifications = Notification::pending()
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                      ->orWhere('scheduled_at', '<=', now());
            })
            ->limit(50)
            ->get();

        $processed = 0;

        foreach ($notifications as $notification) {
            if ($this->send($notification)) {
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Retry failed notifications
     */
    public function retryFailed(): int
    {
        $notifications = Notification::failed()
            ->where('can_retry', true)
            ->limit(20)
            ->get();

        $retried = 0;

        foreach ($notifications as $notification) {
            if ($notification->canRetry()) {
                if ($this->send($notification)) {
                    $retried++;
                }
            }
        }

        return $retried;
    }

    /**
     * Get recipient object
     */
    protected function getRecipient(Notification $notification)
    {
        switch ($notification->recipient_type) {
            case 'user':
                return User::find($notification->recipient_id);
            case 'investor':
                return Investor::find($notification->recipient_id);
            case 'client':
                return Client::find($notification->recipient_id);
            default:
                return null;
        }
    }

    /**
     * Get email template for channel
     */
    protected function getEmailTemplate(string $channel): string
    {
        $templates = [
            'approval' => 'emails.approval',
            'investment' => 'emails.investment',
            'inquiry' => 'emails.inquiry',
            'system' => 'emails.system',
            'statement' => 'emails.statement',
            'receipt' => 'emails.receipt',
            'default' => 'emails.default',
        ];

        return $templates[$channel] ?? $templates['default'];
    }

    /**
     * Get email subject for channel
     */
    protected function getEmailSubject(string $channel, string $title): string
    {
        $prefix = config('app.name');
        
        $subjects = [
            'approval' => "[{$prefix}] Approval Required: {$title}",
            'investment' => "[{$prefix}] Investment Update: {$title}",
            'inquiry' => "[{$prefix}] Inquiry Update: {$title}",
            'system' => "[{$prefix}] System Notification: {$title}",
            'statement' => "[{$prefix}] Statement Available: {$title}",
            'receipt' => "[{$prefix}] Receipt Available: {$title}",
            'default' => "[{$prefix}] {$title}",
        ];

        return $subjects[$channel] ?? $subjects['default'];
    }

    /**
     * Format SMS message
     */
    protected function formatSmsMessage(Notification $notification): string
    {
        $appName = config('app.name');
        $message = $notification->message;
        
        // Truncate if too long
        if (strlen($message) > 160) {
            $message = substr($message, 0, 157) . '...';
        }

        return "[{$appName}] {$message}";
    }

    /**
     * Create notification for approval
     */
    public function createApprovalNotification($approval, $action): Notification
    {
        $title = "Approval {$action}";
        $message = "Order #{$approval->order->order_number} has been {$action} by {$approval->approver->name}";
        
        return Notification::createForUser(
            $approval->order->creator,
            'email',
            'approval',
            $title,
            $message,
            ['approval_id' => $approval->id, 'order_id' => $approval->order_id]
        );
    }

    /**
     * Create notification for investment
     */
    public function createInvestmentNotification($investment, $action): Notification
    {
        $title = "Investment {$action}";
        $message = "Your investment of $" . number_format($investment->amount, 2) . " has been {$action}";
        
        return Notification::createForInvestor(
            $investment->investor,
            'email',
            'investment',
            $title,
            $message,
            ['investment_id' => $investment->id, 'amount' => $investment->amount]
        );
    }

    /**
     * Create notification for inquiry
     */
    public function createInquiryNotification($inquiry, $action): Notification
    {
        $title = "Inquiry {$action}";
        $message = "Your inquiry #{$inquiry->inquiry_number} has been {$action}";
        
        return Notification::createForClient(
            $inquiry->client,
            'email',
            'inquiry',
            $title,
            $message,
            ['inquiry_id' => $inquiry->id, 'inquiry_number' => $inquiry->inquiry_number]
        );
    }

    /**
     * Create notification for statement
     */
    public function createStatementNotification($statement): Notification
    {
        $title = "New Statement Available";
        $message = "Your investment statement for " . $statement->statement_date->format('M Y') . " is now available";
        
        return Notification::createForInvestor(
            $statement->investor,
            'email',
            'statement',
            $title,
            $message,
            ['statement_id' => $statement->id, 'statement_number' => $statement->statement_number]
        );
    }

    /**
     * Create notification for receipt
     */
    public function createReceiptNotification($receipt): Notification
    {
        $title = "New Receipt Available";
        $message = "Your receipt #{$receipt->receipt_number} for $" . number_format($receipt->amount, 2) . " is now available";
        
        return Notification::createForInvestor(
            $receipt->investor,
            'email',
            'receipt',
            $title,
            $message,
            ['receipt_id' => $receipt->id, 'receipt_number' => $receipt->receipt_number]
        );
    }
}
