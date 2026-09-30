<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cotisation;
use App\Models\Distribution;
use App\Models\Membre;
use Illuminate\Http\Request;

class DistributionController extends Controller
{
    /**
     * Souhaitable : Consulter l'historique des mois déjà distribués.
     */
    public function index()
    {
        $distributions = Distribution::with('membre')
            ->orderBy('date_distribution', 'desc')
            ->get();

        return response()->json($distributions, 200);
    }

    /**
     * Essentielle / Bonus : Désigner le bénéficiaire et clôturer le mois.
     * BLOQUE la désignation tant que la cotisation n'est pas complète.
     */
    public function designerBeneficiaire(Request $request)
    {
        $validated = $request->validate([
            'mois' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'id_membre' => 'nullable|exists:membres,id_membre', // Optionnel si calcul automatique
        ]);

        $mois = $validated['mois'];

        // 1. Vérification : Le mois est-il déjà clôturé ?
        $dejaDistribue = Distribution::where('mois', $mois)->exists();
        if ($dejaDistribue) {
            return response()->json([
                'message' => 'Le bénéficiaire pour ce mois a déjà été désigné.'
            ], 422);
        }

        // 2. Vérification : Tous les membres ont-ils cotisé ?
        $totalMembres = Membre::count();
        $nombreCotisants = Cotisation::where('mois', $mois)->count();

        if ($totalMembres === 0 || $nombreCotisants < $totalMembres) {
            return response()->json([
                'message' => 'Impossible de désigner le bénéficiaire : tous les membres n\'ont pas encore cotisé pour ce mois.',
                'cotisants' => $nombreCotisants,
                'total_membres' => $totalMembres,
                'statut' => 'BLOKE'
            ], 403); // Code HTTP 403 Forbidden / Blocage
        }

        // 3. Calcul du montant total collecté
        $montantTotal = Cotisation::where('mois', $mois)->sum('montant');

        // 4. Choix ou Calcul automatique du bénéficiaire (Bonus)
        $beneficiaireId = $validated['id_membre'] ?? null;

        if (!$beneficiaireId) {
            // Récupérer les membres qui n'ont pas encore reçu de distribution
            $idsBeneficiairesPasses = Distribution::pluck('id_membre')->toArray();
            
            $prochainMembre = Membre::whereNotIn('id_membre', $idsBeneficiairesPasses)
                ->orderBy('ordre_tour', 'asc')
                ->first();

            // Si tous les membres ont déjà reçu une fois, on repart du premier selon ordre_tour
            if (!$prochainMembre) {
                $prochainMembre = Membre::orderBy('ordre_tour', 'asc')->first();
            }

            $beneficiaireId = $prochainMembre->id_membre;
        }

        // 5. Clôture et enregistrement de la distribution
        $distribution = Distribution::create([
            'mois' => $mois,
            'id_membre' => $beneficiaireId,
            'montant_total' => $montantTotal,
            'date_distribution' => now(),
        ]);

        return response()->json([
            'message' => 'Bénéficiaire désigné et mois clôturé avec succès !',
            'data' => $distribution->load('membre')
        ], 201);
    }
}