<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory;

    protected $table = 'admins';

    protected $primaryKey = 'idAdmin';

    public $timestamps = false;

    protected $fillable = [
        'nomAdmin',
        'emailAdmin',
        'passAdmin',
    ];

    // Pour que Laravel comprenne que c’est le mot de passe
    protected $hidden = [
        'passAdmin',
    ];

    // Désactiver la colonne "password" par défaut
    public function getAuthPassword()
    {
        return $this->passAdmin;
    }
}
