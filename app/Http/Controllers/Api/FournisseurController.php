<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\CodeFournisseur;
use App\Models\Client;
use App\Mail\FournisseurCodeMail;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\Mail;


class FournisseurController extends Controller
{
    public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        "nomFournisseur" => "required|string|max:255",
        "prenomFournisseur" => "required|string|max:255",
        "nomEntreprise" => "required|string|max:255",
        "emailFRN" => "required|email",
        "passwordFRN" => "required|string|min:6|confirmed",
    ]);

    if ($validator->fails()) {
        return response()->json([
            "status" => "error",
            "errors" => $validator->errors(),
        ], 422);
    }

    // 🔹 Vérifier email unique dans fournisseurs + clients
    $emailExists = Fournisseur::where('emailFRN', $request->emailFRN)->exists()
        || Client::where('email', $request->emailFRN)->exists();

    if ($emailExists) {
        return response()->json([
            "status" => "error",
            "errors" => [
                "emailFRN" => ["Cet email est déjà utilisé par un autre utilisateur."]
            ]
        ], 422);
    }

    // ✅ Création du fournisseur
    $fournisseur = Fournisseur::create([
        "nomFournisseur" => $request->nomFournisseur,
        "prenomFournisseur" => $request->prenomFournisseur,
        "nomEntreprise" => $request->nomEntreprise,
        "nif"  => $request->nif,
        "emailFRN" => $request->emailFRN,
        "passwordFRN" => Hash::make($request->passwordFRN),
        "etatInscription" => "en attente",
        "verify_email"  => "non",
    ]);

    // ✅ Génération du code
    $randomCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    // ✅ Sauvegarde du code dans la table code_fournisseurs
    CodeFournisseur::create([
        "typeCodeFournisseur" => "validation_email",
        "codeFournisseur" => $randomCode,
        "idFournisseur" => $fournisseur->idFournisseur,
    ]);

    // ✅ Envoi de l'email
    try {
        Mail::to($fournisseur->emailFRN)->send(new FournisseurCodeMail($randomCode, $fournisseur));
    } catch (\Exception $e) {
        return response()->json([
            "status" => "error",
            "message" => "Erreur lors de l'envoi de l'email : " . $e->getMessage(),
        ], 500);
    }

    return response()->json([
        "status" => "success",
        "message" => "Inscription réussie. Vérifiez votre email pour le code de confirmation.",
        "fournisseur" => $fournisseur,
        "code" => $randomCode // à retirer en production
    ], 201);
}

    // ==================== LOGOUT ====================
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            "status" => "success",
            "message" => "Déconnexion réussie."
        ]);
    }

    // Liste fournisseurs "en attente"
    public function getEnAttente()
    {
        $fournisseurs = Fournisseur::where('etatInscription', 'en attente')->get();
        return response()->json($fournisseurs);
    }

    // Modifier l'état : "validé" ou "rejeté"
    public function updateEtat(Request $request, $id)
    {
        $request->validate([
            'etat' => 'required|in:validé,rejeté'
        ]);

        $fournisseur = Fournisseur::findOrFail($id);
        $fournisseur->etatInscription = $request->etat;
        $fournisseur->save();

        if ($request->etat === "validé") {
            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            CodeFournisseur::create([
                "typeCodeFournisseur" => "fournisseur",
                "codeFournisseur" => $code,
                "idFournisseur" => $fournisseur->idFournisseur
            ]);
        }

        return response()->json([
            'status' => 'success',
            'fournisseur' => $fournisseur
        ]);
    }

    // 🔹 Vérification email avec code
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "emailFRN"        => "required|email|exists:fournisseurs,emailFRN",
            "codeFournisseur" => "required|string|size:6",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "errors" => $validator->errors()
            ], 422);
        }

        $fournisseur = Fournisseur::where("emailFRN", $request->emailFRN)->first();

        if (!$fournisseur) {
            return response()->json([
                "status" => "error",
                "message" => "Fournisseur introuvable."
            ], 404);
        }

        $code = CodeFournisseur::where("idFournisseur", $fournisseur->idFournisseur)
            ->where("codeFournisseur", $request->codeFournisseur)
            ->latest()
            ->first();

        if (!$code) {
            return response()->json([
                "status" => "error",
                "message" => "Code incorrect ou expiré."
            ], 400);
        }

        $fournisseur->verify_email = "oui";
        $fournisseur->save();

        $code->delete();

        $token = $fournisseur->createToken('fournisseur-token')->plainTextToken;

        return response()->json([
            "status"      => "success",
            "message"     => "Email vérifié avec succès.",
            "fournisseur" => $fournisseur,
            "token" => $token,
        ], 200);
    }
}
