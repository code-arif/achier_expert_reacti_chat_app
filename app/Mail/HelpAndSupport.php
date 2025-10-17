<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HelpAndSupport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $helpandSupportData;
    public function __construct($helpandSupportData)
    {
        $this->helpandSupportData = $helpandSupportData;
    }
    

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Help And Support',
        );
    }

   
    public function build()
    {
        return $this->subject('New Contact Message')
            ->view('emails.helpandsupport')
            ->with('helpandSupportData', $this->helpandSupportData);
    }
}
