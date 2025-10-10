<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategorieProduit extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomCategorie',
        'photoCategorie',
    ];

    public function sousCategories()
    {
        return $this->hasMany(SousCategorieProduit::class, "categorieProduit_id")
                    ->with("products"); // on charge aussi les produits
    }
}
