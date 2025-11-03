<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FournisseurCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $fournisseur;

    public function __construct($code, $fournisseur)
    {
        $this->code = $code;
        $this->fournisseur = $fournisseur;
    }

    public function build()
    {
        return $this->subject('Votre code de validation')
                    ->view('emails.fournisseur_code');
    }
}
