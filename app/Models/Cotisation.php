<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cotisation extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_cotisation';

   protected $fillable = [
    'id_membre',
    'mois',
    'montant',
    'date_versement',
    'statut', // 'en_attente', 'validee', 'rejetee'
];

    /**
     * Relation vers le membre qui a cotisé.
     */
    public function membre(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'id_membre', 'id_membre');
    }
}