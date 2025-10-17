<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use SerializesModels;

    public $contactData;

    /**
     * Create a new message instance.
     *
     * @param  array  $contactData
     * @return void
     */
    public function __construct($contactData)
    {
        $this->contactData = $contactData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('New Contact Message')
            ->view('emails.contact')
            ->with('contactData', $this->contactData);
    }
}
