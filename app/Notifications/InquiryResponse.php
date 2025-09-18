<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Inquiry;

class InquiryResponse extends Notification implements ShouldQueue
{
    use Queueable;

    protected $inquiry;
    protected $responderName;

    /**
     * Create a new notification instance.
     */
    public function __construct(Inquiry $inquiry, string $responderName)
    {
        $this->inquiry = $inquiry;
        $this->responderName = $responderName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Response to Your Inquiry - ' . $this->inquiry->inquiry_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We have responded to your inquiry.')
            ->line('Inquiry Number: ' . $this->inquiry->inquiry_number)
            ->line('Subject: ' . $this->inquiry->subject)
            ->line('Status: ' . ucfirst(str_replace('_', ' ', $this->inquiry->status)))
            ->line('Responded by: ' . $this->inquiry->assignedUser->name ?? $this->responderName)
            ->action('View Response', url('/client/inquiries'))
            ->line('Thank you for your interest in Pro Traders Ltd!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'inquiry_response',
            'title' => 'Inquiry Response Received',
            'message' => "Your inquiry {$this->inquiry->inquiry_number} has received a response.",
            'data' => [
                'inquiry_id' => $this->inquiry->id,
                'inquiry_number' => $this->inquiry->inquiry_number,
                'status' => $this->inquiry->status,
            ],
        ];
    }
}
