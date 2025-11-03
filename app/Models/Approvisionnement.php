<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approvisionnement extends Model
{
    use HasFactory;

    protected $table = 'approvisionnements';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'idProduct',
        'qteAppro',
        'dateAppro',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'idProduct', 'idProduct');
    }
}