<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Distribution extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_distribution';

    protected $fillable = [
        'mois',
        'montant_total',
        'date_distribution',
        'id_membre',
    ];

    /**
     * Relation vers le membre bénéficiaire de la distribution.
     */
    public function membre(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'id_membre', 'id_membre');
    }
}