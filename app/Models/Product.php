<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $primaryKey = "idProduct";
    public $incrementing = true;
    protected $keyType = "int";
    protected $fillable = [
        "refProduct",
        "designProduct",
        "marqueProduct",
        "description",
        "imageProduct",
        "idSousCategorie",
        "stock",
        "idFournisseur",
    ];

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, "idFournisseur", "idFournisseur");
    }

    public function sousCategorie()
    {
        return $this->belongsTo(SousCategorieProduit::class, "idSousCategorie", "idSousCategorie");
    }
}
