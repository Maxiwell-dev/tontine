<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cotisation;
use App\Models\Membre;
use Illuminate\Http\Request;

class CotisationController extends Controller
{
    /**
     * Espace Membre : Soumettre une cotisation (Passe en statut 'en_attente')
     */
    public function soumettreCotisation(Request $request)
    {
        $validated = $request->validate([
            'id_membre' => 'required|exists:membres,id_membre',
            'mois' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'montant' => 'required|numeric|min:0',
        ]);

        // Vérifier si une cotisation existe déjà pour ce mois
        $cotisationExiste = Cotisation::where('id_membre', $validated['id_membre'])
            ->where('mois', $validated['mois'])
            ->first();

        if ($cotisationExiste) {
            return response()->json([
                'message' => 'Une cotisation pour ce mois a déjà été soumise (statut : ' . $cotisationExiste->statut . ').'
            ], 422);
        }

        $cotisation = Cotisation::create([
            'id_membre' => $validated['id_membre'],
            'mois' => $validated['mois'],
            'montant' => $validated['montant'],
            'date_versement' => now(),
            'statut' => 'en_attente', // En attente de validation par l'admin
        ]);

        return response()->json([
            'message' => 'Cotisation soumise avec succès. En attente de validation par l\'administrateur.',
            'data' => $cotisation
        ], 201);
    }

    /**
     * Espace Membre : Consulter ses propres cotisations et ses restes à payer
     */
    public function mesCotisations(Request $request, $id_membre)
    {
        $cotisations = Cotisation::where('id_membre', $id_membre)
            ->orderBy('mois', 'desc')
            ->get();

        $moisActuel = date('Y-m');
        $cotisationMoisEnCours = Cotisation::where('id_membre', $id_membre)
            ->where('mois', $moisActuel)
            ->first();

        $estAjour = $cotisationMoisEnCours && $cotisationMoisEnCours->statut === 'validee';
        $enAttente = $cotisationMoisEnCours && $cotisationMoisEnCours->statut === 'en_attente';

        return response()->json([
            'id_membre' => $id_membre,
            'statut_mois_actuel' => $estAjour ? 'A_JOUR' : ($enAttente ? 'EN_ATTENTE_VALIDATION' : 'A_PAYER'),
            'cotisations' => $cotisations,
        ], 200);
    }

    /**
     * Espace Admin : Liste des cotisations en attente de validation (Notifications Admin)
     */
    public function cotisationsEnAttente()
    {
        $enAttente = Cotisation::with('membre')
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'nombre_notifications' => $enAttente->count(),
            'demandes' => $enAttente
        ], 200);
    }

    /**
     * Espace Admin : Valider ou Rejeter une cotisation
     */
    public function validerCotisation(Request $request, $id_cotisation)
    {
        $validated = $request->validate([
            'action' => 'required|in:valider,rejeter',
        ]);

        $cotisation = Cotisation::findOrFail($id_cotisation);

        if ($validated['action'] === 'valider') {
            $cotisation->update(['statut' => 'validee']);
            $message = 'Cotisation validée avec succès.';
        } else {
            $cotisation->update(['statut' => 'rejetee']);
            $message = 'Cotisation rejetée.';
        }

        return response()->json([
            'message' => $message,
            'data' => $cotisation->load('membre')
        ], 200);
    }

    /**
     * Statut global du mois (ne compte QUE les cotisations VALIDÉES)
     */
    public function statutMoisGlobal(Request $request)
    {
        $mois = $request->query('mois', date('Y-m'));

        $totalMembres = Membre::count();
        $cotisationsValidees = Cotisation::with('membre')
            ->where('mois', $mois)
            ->where('statut', 'validee')
            ->get();

        $idsMembresAjour = $cotisationsValidees->pluck('id_membre')->toArray();
        $membresManquants = Membre::whereNotIn('id_membre', $idsMembresAjour)->get();

        $nombreCotisants = $cotisationsValidees->count();
        $totalCollecte = $cotisationsValidees->sum('montant');

        $estDebloque = ($totalMembres > 0) && ($nombreCotisants === $totalMembres);

        return response()->json([
            'mois' => $mois,
            'total_membres' => $totalMembres,
            'nombre_cotisants_valides' => $nombreCotisants,
            'total_collecte' => $totalCollecte,
            'est_debloque' => $estDebloque,
            'cotisations_validees' => $cotisationsValidees,
            'membres_manquants' => $membresManquants,
        ], 200);
    }
}