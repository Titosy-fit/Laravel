<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Livreur;
use App\Models\CodeLivreur;
use App\Models\Client;
use App\Models\Fournisseur;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\LivreurCodeMail;

class LivreurController extends Controller
{
    // ==================== REGISTER ====================
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "nomLivreur" => "required|string|max:255",
            "prenomLivreur" => "required|string|max:255",
            "adresseLivreur" => "nullable|string|max:255",
            "emailLivreur" => "required|email",
            "CIN" => "nullable|string|max:100",
            "dateCINLivreur" => "nullable|date",
            "lieuCINLivreur" => "nullable|string|max:255",
            "dateNaissanceLivreur" => "nullable|date",
            "passwordLivreur" => "required|string|min:6|confirmed",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "errors" => $validator->errors(),
            ], 422);
        }

    
        if (
            Livreur::where('emailLivreur', $request->emailLivreur)->exists() ||
            Client::where('email', $request->emailLivreur)->exists() ||
            Fournisseur::where('emailFRN', $request->emailLivreur)->exists()
        ) {
            return response()->json([
                "status" => "error",
                "message" => "Cet email est déjà utilisé par un autre compte."
            ], 422);
        }

        $livreur = Livreur::create([
            "nomLivreur" => $request->nomLivreur,
            "prenomLivreur" => $request->prenomLivreur,
            "adresseLivreur" => $request->adresseLivreur ?? null,
            "emailLivreur" => $request->emailLivreur,
            "CIN" => $request->CIN ?? null,
            "dateCINLivreur" => $request->dateCINLivreur ?? null,
            "lieuCINLivreur" => $request->lieuCINLivreur ?? null,
            "dateNaissanceLivreur" => $request->dateNaissanceLivreur ?? null,
            "passwordLivreur" => Hash::make($request->passwordLivreur),
            "etatInscriptionLivreur" => "en attente",
            "verify_email" => 0,
        ]);

       
        $codeModel = CodeLivreur::create([
            'idLivreur' => $livreur->id,
            'codeLivreur' => rand(100000, 999999),
            'typeCodeLivreur' => 'email',
        ]);

        try {
            Mail::to($livreur->emailLivreur)
                ->send(new LivreurCodeMail($codeModel->codeLivreur, $livreur));
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l’envoi du mail de code livreur : ' . $e->getMessage());
        }

        return response()->json([
            "status" => "success",
            "message" => "Inscription réussie. Un code a été envoyé à votre email pour vérification.",
            "livreur" => $livreur,
        ], 201);
    }

    // ==================== LOGIN ====================
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "emailLivreur" => "required|email",
            "passwordLivreur" => "required|min:6",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "errors" => $validator->errors(),
            ], 422);
        }

        $livreur = Livreur::where("emailLivreur", $request->emailLivreur)->first();

        if (!$livreur || !Hash::check($request->passwordLivreur, $livreur->passwordLivreur)) {
            return response()->json([
                "status" => "error",
                "message" => "Identifiants invalides."
            ], 401);
        }

        if ($livreur->etatInscriptionLivreur !== "validé") {
            return response()->json([
                "status" => "error",
                "message" => "Votre compte n’a pas encore été validé par l’administrateur."
            ], 403);
        }

        $livreur->tokens()->delete();

        $token = $livreur->createToken("livreurToken")->plainTextToken;

        return response()->json([
            "status" => "success",
            "message" => "Connexion réussie.",
            "livreur" => $livreur,
            "token" => $token,
        ], 200);
    }



    // ==================== LISTE EN ATTENTE ====================
    public function getEnAttente()
    {
        $livreurs = Livreur::where('etatInscriptionLivreur', 'en attente')->get();
        return response()->json([
            "status" => "success",
            "livreurs" => $livreurs,
        ]);
    }

    // ==================== VALIDATION PAR ADMIN ====================
    public function updateEtat(Request $request, $id)
    {
        $request->validate([
            'etat' => 'required|in:validé,rejeté'
        ]);

        $livreur = Livreur::findOrFail($id);

        $existingCode = CodeLivreur::where('idLivreur', $livreur->id)->latest()->first();
        if (!$existingCode) {
            $existingCode = CodeLivreur::create([
                'idLivreur' => $livreur->id,
                'codeLivreur' => rand(100000, 999999),
                'typeCodeLivreur' => 'email',
            ]);
            try {
                Mail::to($livreur->emailLivreur)
                    ->send(new LivreurCodeMail($existingCode->codeLivreur, $livreur));
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l’envoi du mail de code livreur : ' . $e->getMessage());
            }
        }

        $livreur->etatInscriptionLivreur = $request->etat;
        $livreur->save();

        return response()->json([
            "status" => "success",
            "message" => "État du livreur mis à jour.",
            "livreur" => $livreur,
        ]);
    }

    // ==================== VERIFICATION EMAIL ====================
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

        $livreur->verify_email = 1; 
        $livreur->save();
        $code->delete();

        $token = $livreur->createToken('livreurToken')->plainTextToken;

        return response()->json([
            "status" => "success",
            "message" => "Email vérifié avec succès.",
            "livreur" => $livreur,
            "token" => $token,
        ], 200);
    }




// ==================== CHANGE PASSWORD ====================
public function changePassword(Request $request)
{
    $livreur = $request->user(); 

    $validator = Validator::make($request->all(), [
        'oldPassword' => 'required|string',
        'newPassword' => 'required|string|min:6|confirmed', 
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first(),
        ], 422);
    }

    // Vérification ancien mot de passe
    if (!Hash::check($request->oldPassword, $livreur->passwordLivreur)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Ancien mot de passe incorrect.',
        ], 400);
    }

    // Changement mot de passe
    $livreur->passwordLivreur = Hash::make($request->newPassword);
    $livreur->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Mot de passe modifié avec succès.',
    ]);
}


}
