<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LivreurCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $livreur;

    public function __construct($code, $livreur = null)
    {
        $this->code = $code;
        $this->livreur = $livreur;
    }

    public function build()
    {
        return $this->subject('Code de vérification Nir\'Shop')
                    ->markdown('emails.livreur.code')
                    ->with(['code' => $this->code]);
    }
}
