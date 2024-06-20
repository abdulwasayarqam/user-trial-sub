<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SubscriptionConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $planName;
    public $pdf;

    public function __construct($user, $planName, $pdf)
    {
        $this->user = $user;
        $this->planName = $planName;
        $this->pdf = $pdf;
    }

    public function build()
    {
        return $this->view('emails.subscription_confirmation')
                    ->subject('Subscription Confirmation')
                    ->attachData($this->pdf, 'subscription_confirmation.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
