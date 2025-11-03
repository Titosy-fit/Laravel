<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    protected $fillable = [
        "refProduct",
        "designProduct",
        "marqueProduct",
        "description",
        "imageProduct",
        "imageDetailProduct",
        "idSousCategorie",
        "stock",
        "idFournisseur",
    ];
    
    protected $primaryKey = 'idProduct';

    protected $casts = [
        'imageDetailProduct' => 'array',
    ];

    // Ajoutez l'attribut appends pour inclure les accesseurs dans JSON
    protected $appends = ['stock_prod', 'dernier_prix'];
    
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, "idFournisseur", "idFournisseur");
    }

    public function sousCategorie()
    {
        return $this->belongsTo(SousCategorieProduit::class, "idSousCategorie", "idSousCategorie");
    }

    public function dernierPrix()
    {
        return $this->hasOne(Prix::class, 'idProduct', 'idProduct')->latestOfMany('dateModification');
    }

    public function approvisionnements()
    {
        return $this->hasMany(Approvisionnement::class, 'idProduct', 'idProduct');
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'idProduct', 'idProduct');
    }

    // Accesseur pour le stock calculé
    public function getStockProdAttribute()
    {
        $totalAppro = $this->approvisionnements()->sum('qteAppro');
        $totalCom = $this->commandes()->sum('qteCom');
        return max(0, $totalAppro - $totalCom);
    }

    // Accesseur pour le dernier prix
    public function getDernierPrixAttribute()
    {
        $dernierPrix = $this->dernierPrix()->first();
        return $dernierPrix ? $dernierPrix->prix : 0;
    }

    // Accesseur pour les détails du dernier prix
    public function getDernierPrixDetailsAttribute()
    {
        $dernierPrix = $this->dernierPrix()->first();
        return $dernierPrix ? [
            'prix' => $dernierPrix->prix,
            'dateModification' => $dernierPrix->dateModification
        ] : null;
    }
}