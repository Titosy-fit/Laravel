<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livreur;
use App\Models\CodeLivreur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\LivreurCodeMail;

class LivreurController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "nomLivreur" => "required|string|max:255",
            "prenomLivreur" => "required|string|max:255",
            "CIN" => "nullable|string|max:100",
            "emailLivreur" => "required|email|unique:livreurs,emailLivreur",
            "passwordLivreur" => "required|string|min:6|confirmed",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "errors" => $validator->errors(),
            ], 422);
        }

        $livreur = Livreur::create([
            "nomLivreur" => $request->nomLivreur,
            "prenomLivreur" => $request->prenomLivreur,
            "adresseLivreur" => $request->adresseLivreur ?? null,
            "emailLivreur" => $request->emailLivreur,
            "CIN" => $request->CIN ?? null,
            "passwordLivreur" => Hash::make($request->passwordLivreur),
            "etatInscriptionLivreur" => "en attente",
            "verify_email" => "non",
        ]);

        return response()->json([
            "status" => "success",
            "message" => "Inscription réussie. En attente de validation.",
            "livreur" => $livreur,
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            "status" => "success",
            "message" => "Déconnexion réussie."
        ]);
    }

    public function getEnAttente()
    {
        $livreurs = Livreur::where('etatInscriptionLivreur', 'en attente')->get();
        return response()->json($livreurs);
    }

    // Admin valide/rejette un livreur ; si validé on génère et on envoie le code
    public function updateEtat(Request $request, $id)
    {
        $request->validate([
            'etat' => 'required|in:validé,rejeté'
        ]);

        $livreur = Livreur::findOrFail($id);
        $livreur->etatInscriptionLivreur = $request->etat;
        $livreur->save();

        if ($request->etat === "validé") {
            
            $codeModel = CodeLivreur::generateFor($livreur->id, 'email');

                        try {
                Mail::to($livreur->emailLivreur)->send(new LivreurCodeMail($codeModel->codeLivreur, $livreur));
            } catch (\Exception $e) {
                
                \Log::error('Envoi mail code livreur échoué: '.$e->getMessage());
            }
        }

        return response()->json([
            'status' => 'success',
            'livreur' => $livreur
        ]);
    }

    // Vérification du code envoyé au livreur
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "emailLivreur" => "required|email|exists:livreurs,emailLivreur",
            "codeLivreur" => "required|string|size:6",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "errors" => $validator->errors()
            ], 422);
        }

        $livreur = Livreur::where("emailLivreur", $request->emailLivreur)->first();

        if (!$livreur) {
            return response()->json([
                "status" => "error",
                "message" => "Livreur introuvable."
            ], 404);
        }

        $code = CodeLivreur::where("idLivreur", $livreur->id)
            ->where("codeLivreur", $request->codeLivreur)
            ->latest()
            ->first();

        if (!$code) {
            return response()->json([
                "status" => "error",
                "message" => "Code incorrect ou expiré."
            ], 400);
        }

        $livreur->verify_email = "oui";
        $livreur->save();

        $code->delete();

        // Retourner un token si tu utilises Sanctum
        $token = $livreur->createToken('livreur-token')->plainTextToken;

        return response()->json([
            "status" => "success",
            "message" => "Email vérifié avec succès.",
            "livreur" => $livreur,
            "token" => $token,
        ], 200);
    }
}
