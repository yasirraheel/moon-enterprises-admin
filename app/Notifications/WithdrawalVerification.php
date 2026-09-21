<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalVerification extends Notification implements ShouldQueue
{
    use Queueable;
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(public $data)
    {
        $this->data = (object) $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = $this->data->type == 'approved' ? 'Withdrawal Approved' : 'Withdrawal Rejected';
        $line = $this->data->type == 'approved' 
            ? 'Your withdrawal request of $' . number_format($this->data->amount, 2) . ' has been approved and processed.' 
            : 'Your withdrawal request of $' . number_format($this->data->amount, 2) . ' has been rejected.';
        $action = $this->data->type == 'approved' ? 'View Transaction' : 'Contact Support';
        $url = $this->data->type == 'approved' ? url('user/dashboard') : url('contact');

        return (new MailMessage)
            ->subject($subject)
            ->line($line)
            ->action($action, $url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
