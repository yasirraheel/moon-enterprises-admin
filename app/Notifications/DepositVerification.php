<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepositVerification extends Notification implements ShouldQueue
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
        $subject = $this->data->type == 'approve' ? __('misc.transfer_verified') : __('misc.transfer_not_verified');
        $line = $this->data->type == 'approve' 
            ? __('misc.info_transfer_verified', ['amount' => $this->data->amount]) 
            : __('misc.info_transfer_not_verified', ['amount' => $this->data->amount]);
        $action = $this->data->type == 'approve' ? __('misc.go_to_wallet') : __('misc.contact');
        $url = $this->data->type == 'approve' ? url('user/dashboard/add/funds') : url('contact');

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
