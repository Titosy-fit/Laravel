<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SousCategorieProduit extends Model

{
    use HasFactory;

    protected $primaryKey = "idSousCategorie";
    public $incrementing = true;
    protected $keyType = "int";

    protected $fillable = [
        "nomSousCategorie",
        "imageSousCategorie",
        "categorieProduit_id"
    ]; 

    public function categorieProduit()
    {
        return $this->belongsTo(CategorieProduit::class, "categorieProduit_id");
    }

    public function products()
    {
        return $this->hasMany(Product::class, "idSousCategorie", "idSousCategorie");
    }
}