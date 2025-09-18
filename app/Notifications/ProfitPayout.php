<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Transaction;

class ProfitPayout extends Notification implements ShouldQueue
{
    use Queueable;

    protected $transaction;

    /**
     * Create a new notification instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
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
            ->subject('Profit Payout - $' . number_format($this->transaction->amount, 2))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Congratulations! You have received a profit payout.')
            ->line('Transaction ID: ' . $this->transaction->transaction_id)
            ->line('Amount: $' . number_format($this->transaction->amount, 2))
            ->line('Description: ' . $this->transaction->description)
            ->line('Date: ' . $this->transaction->transaction_date->format('d M Y'))
            ->line('Current Balance: $' . number_format($this->transaction->balance_after, 2))
            ->action('View Statement', url('/investor/statements'))
            ->line('Thank you for your investment with Pro Traders Ltd!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'profit_payout',
            'title' => 'Profit Payout Received',
            'message' => "You have received a profit payout of $" . number_format($this->transaction->amount, 2) . ".",
            'data' => [
                'transaction_id' => $this->transaction->id,
                'amount' => $this->transaction->amount,
                'balance_after' => $this->transaction->balance_after,
            ],
        ];
    }
}
