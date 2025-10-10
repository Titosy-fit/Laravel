<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeFournisseur extends Model
{
    use HasFactory;

    protected $table = 'code_fournisseurs'; // nom exact de la table
    protected $primaryKey = 'id';           // clé primaire réelle
    public $incrementing = true;            // c'est un auto-incrément
    protected $keyType = 'int';

    protected $fillable = [
        'typeCodeFournisseur',
        'codeFournisseur',
        'idFournisseur'
    ];

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, "idFournisseur", "idFournisseur");
    }
}
