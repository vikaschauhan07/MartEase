<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EmailService extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $content;
    public $name;
    public $type;
    /**
     * Create a new message instance.
     */
    public function __construct($subject, $name,$content, $type = 1)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->type = $type;
        $this->name = $name;
    }

    public function build()
    {
        if($this->type == 1){
            return $this->subject($this->subject)
            ->view('mails.otp-mails')
            ->with(['otp' => $this->content, "name" => $this->name]);
        } else {
            return $this->subject($this->subject)
            ->view('mails.get-notified');
        }
    }

    /**
     * Get the message envelope.
     */
    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: 'Email Service',
    //     );
    // }

    /**
     * Get the message content definition.
     */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    // public function attachments(): array
    // {
    //     return [];
    // }
}
