<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prix extends Model
{
    use HasFactory;

    protected $table = 'prixs';
    protected $primaryKey = 'id';
    protected $fillable = ['idProduct', 'prix', 'dateModification'];
    public $timestamps = true;

    protected $dates = [
        'dateModification',
        'created_at',
        'updated_at',
    ];

    public function produit()
    {
        return $this->belongsTo(Product::class, 'idProduct', 'idProduct');
    }
}
