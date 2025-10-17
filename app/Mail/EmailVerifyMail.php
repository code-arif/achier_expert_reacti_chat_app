<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class EmailVerifyMail extends Mailable
{
    use Queueable, SerializesModels;

    public int $otp;
    public string $userName;
    public string $headerMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(int $otp, string $userName, string $message)
    {
        $this->otp = $otp;
        $this->userName = $userName;
        $this->headerMessage = $message;

        // Queue configuration
        // $this->onQueue('emails'); // Separate queue for emails
        // $this->afterCommit(); // Send after database transaction commits
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            // from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: $this->headerMessage,
            // Anti-spam headers
            tags: ['verification', 'otp'],
            metadata: [
                'type' => 'email_verification',
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otpmail',
            // text: 'emails.otpmail-text', // Plain text version for better deliverability
            with: [
                'otp' => $this->otp,
                'userName' => $this->userName,
                'expiryMinutes' => 5,
                'appName' => config('app.name'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
