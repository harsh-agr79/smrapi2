<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $token;
    public $email;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $token, $email)
    {
        $this->user = $user;
        $this->token = $token;
        $this->email = $email;
    }

    public function build()
    {
        // Encrypt the email and generate the reset URL
        $resetUrl = "https://www.samarmart.com/resetpwd/".$this->email."/".$this->token;

        return $this->subject('Samar Mart - Password Reset Request')
                    ->markdown('emails.forgotpassword')
                    ->with([
                        'name' => $this->user->name,
                        'resetUrl' => $resetUrl,
                    ]);
    }
}
