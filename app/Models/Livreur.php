<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Livreur extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'livreurs';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nomLivreur',
        'prenomLivreur',
        'adresseLivreur',
        'emailLivreur',
        'CIN',
        'dateCINLivreur',
        'lieuCINLivreur',
        'dateNaissanceLivreur',
        'passwordLivreur',
        'etatInscriptionLivreur',
        'verify_email',
    ];

    protected $hidden = [
        'passwordLivreur',
        'remember_token',
    ];

    public function codes()
    {
        return $this->hasMany(CodeLivreur::class, 'idLivreur', 'id');
    }
}
