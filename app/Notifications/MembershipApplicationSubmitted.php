<?php

namespace App\Notifications;

use App\Models\MembershipTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 120, 300];

    public function __construct(public MembershipTransaction $transaction)
    {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Membership '.$this->transaction->type.' received | PUIHAHA')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('We received your membership '.$this->transaction->type.'.')
            ->line('Reference number: '.$this->transaction->transaction_number)
            ->line('Total fee: PHP '.number_format((float) $this->transaction->amount_due, 2))
            ->line('Status when submitted: '.ucfirst($this->transaction->status))
            ->line('Your membership will be activated after full payment and administrator approval.')
            ->line('Keep this reference number for your records.')
            ->salutation('PUIHAHA');
    }
}
