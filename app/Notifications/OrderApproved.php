<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class OrderApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $approverName;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $approverName)
    {
        $this->order = $order;
        $this->approverName = $approverName;
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
            ->subject('Order Approved - ' . $this->order->order_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Great news! Your order has been approved.')
            ->line('Order Number: ' . $this->order->order_number)
            ->line('Title: ' . $this->order->title)
            ->line('Amount: $' . number_format($this->order->total_amount, 2))
            ->line('Approved by: ' . $this->approverName)
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('Your investment is now active and will be processed according to the terms.')
            ->line('Thank you for choosing Pro Traders Ltd!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_approved',
            'title' => 'Order Approved',
            'message' => "Order {$this->order->order_number} has been approved by {$this->approverName}.",
            'data' => [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'approver_name' => $this->approverName,
            ],
        ];
    }
}
