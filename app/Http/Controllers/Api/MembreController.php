<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membre;
use Illuminate\Http\Request;

class MembreController extends Controller
{
    /**
     * Essentielle : Lister les membres triés par ordre de passage.
     */
    public function index()
    {
        $membres = Membre::orderBy('ordre_tour', 'asc')->get();
        return response()->json($membres, 200);
    }

    /**
     * Ajouter un nouveau membre.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'ordre_tour' => 'required|integer|unique:membres,ordre_tour',
        ]);

        $membre = Membre::create($validated);

        return response()->json([
            'message' => 'Membre ajouté avec succès.',
            'data' => $membre
        ], 201);
    }
}