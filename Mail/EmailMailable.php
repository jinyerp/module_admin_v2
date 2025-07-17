<?php

namespace Jiny\Admin\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;

class EmailMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectText;
    public $contentHtml;
    public $fromEmail;
    public $fromName;
    public $toEmail;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $content, $fromEmail, $fromName, $toEmail)
    {
        $this->subjectText = $subject;
        $this->contentHtml = $content;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->toEmail = $toEmail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromEmail, $this->fromName),
            to: [new Address($this->toEmail)],
            subject: $this->subjectText,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: $this->contentHtml,
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
