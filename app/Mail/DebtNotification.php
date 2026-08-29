<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DebtNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $groupName;
    public $amount;

    /**
     * Create a new message instance.
     */
    public function __construct($userName, $groupName, $amount)
    {
        $this->userName = $userName;
        $this->groupName = $groupName;
        $this->amount = $amount;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Podsetnik - dugujete novac u grupi "' . $this->groupName . '"')
            ->view('emails.debt-notification');
    }
}
