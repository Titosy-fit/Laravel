<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $client;

    public function __construct($code, $client)
    {
        $this->code = $code;
        $this->client = $client;
    }

    public function build()
    {
        return $this->subject('Votre code de validation')
                    ->view('emails.client_code');
    }
}
