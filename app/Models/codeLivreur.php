<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeLivreur extends Model
{
    use HasFactory;

    protected $table = 'code_livreurs';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'typeCodeLivreur',
        'codeLivreur',
        'idLivreur',
    ];

    public function livreur()
    {
        return $this->belongsTo(Livreur::class, 'idLivreur', 'id');
    }

    // Méthode utilitaire pour générer et stocker un code à 6 chiffres
    public static function generateFor(int $idLivreur, string $type = 'email'): self
    {
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        return self::create([
            'typeCodeLivreur' => $type,
            'codeLivreur'     => $code,
            'idLivreur'       => $idLivreur,
        ]);
    }
}
