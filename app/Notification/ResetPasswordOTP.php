<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\VonageMessage;

class ResetPasswordOTP extends Notification
{
    use Queueable;

    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        // Kirim via email dan WhatsApp
        return ['mail', 'vonage'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Kode OTP Reset Password')
            ->line('Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.')
            ->line('Kode OTP Anda: ' . $this->otp)
            ->line('Kode ini akan kadaluarsa dalam 10 menit.')
            ->line('Jika Anda tidak meminta reset password, abaikan email ini.');
    }

    public function toVonage($notifiable)
    {
        return (new VonageMessage)
            ->content('Kode OTP reset password Anda: ' . $this->otp . '. Berlaku 10 menit.');
    }
}
