<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Fournisseur extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $primaryKey = 'idFournisseur';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        "nomFournisseur",
        "prenomFournisseur",
        "nomEntreprise",
        "nif",
        "stat",
        "rcs",
        "emailFRN",
        "passwordFRN",
        "etatInscription",
        'verify_email',
    ];

    protected $hidden = [
        'passwordFRN',
    ];

    public function codes()
    {
        return $this->hasMany(CodeFournisseur::class, "idFournisseur", "idFournisseur");
    }
}
