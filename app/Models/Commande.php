<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $table = 'commandes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'idProduct',
        'idClient',
        'qteCom',
        'dateCom',
        'modeLivraison',
        'lieuLivraison',
    ];

    protected $casts = [
        'dateCom' => 'datetime',
        'qteCom' => 'decimal:2',
    ];

    /**
     * Relation avec le produit
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'idProduct', 'idProduct');
    }

    /**
     * Relation avec le client
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'idClient');
    }

    /**
     * Accesseur pour le mode de livraison
     */
    public function getModeLivraisonAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * Scope pour les commandes à livrer
     */
    public function scopeALivrer($query)
    {
        return $query->where('modeLivraison', 'à livrer');
    }

    /**
     * Scope pour les commandes à récupérer
     */
    public function scopeARecuperer($query)
    {
        return $query->where('modeLivraison', 'à recuperer');
    }

    /**
     * Scope pour les commandes d'un client spécifique
     */
    public function scopeParClient($query, $clientId)
    {
        return $query->where('idClient', $clientId);
    }

    /**
     * Vérifie si la commande est à livrer
     */
    public function estALivrer()
    {
        return $this->modeLivraison === 'à livrer';
    }

    /**
     * Vérifie si la commande est à récupérer
     */
    public function estARecuperer()
    {
        return $this->modeLivraison === 'à recuperer';
    }
}