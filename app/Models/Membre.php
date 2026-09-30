<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membre extends Model
{
    use HasFactory;

    // Définition de la clé primaire personnalisée
    protected $primaryKey = 'id_membre';

    protected $fillable = [
        'nom',
        'telephone',
        'ordre_tour',
    ];

    /**
     * Un membre a plusieurs cotisations.
     */
    public function cotisations(): HasMany
    {
        return $this->hasMany(Cotisation::class, 'id_membre', 'id_membre');
    }

    /**
     * Un membre a plusieurs distributions (bénéficiaire).
     */
    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class, 'id_membre', 'id_membre');
    }
}