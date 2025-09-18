<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class OrderApprovalRequest extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $approverRole;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $approverRole)
    {
        $this->order = $order;
        $this->approverRole = $approverRole;
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
            ->subject('Order Approval Required - ' . $this->order->order_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new order requires your approval.')
            ->line('Order Number: ' . $this->order->order_number)
            ->line('Title: ' . $this->order->title)
            ->line('Amount: $' . number_format($this->order->total_amount, 2))
            ->line('Your Role: ' . ucfirst(str_replace('_', ' ', $this->approverRole)))
            ->action('Review Order', url('/approvals'))
            ->line('Please review and approve or reject this order at your earliest convenience.')
            ->line('Thank you for using Pro Traders Ltd!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_approval_request',
            'title' => 'Order Approval Required',
            'message' => "Order {$this->order->order_number} requires your approval as {$this->approverRole}.",
            'data' => [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'approver_role' => $this->approverRole,
            ],
        ];
    }
}
