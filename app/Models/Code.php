<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    use HasFactory;
    protected $fillable = [
        "typeCode",
        "code",
        "client_id",
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
