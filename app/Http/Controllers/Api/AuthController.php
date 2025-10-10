<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Client;
use App\Models\Fournisseur;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required|min:6",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "errors" => $validator->errors()
            ], 422);
        }

        // Vérifier si c'est un Client
        $client = Client::where("email", $request->email)->first();
        if ($client && Hash::check($request->password, $client->password)) {
            $client->tokens()->delete();
            $token = $client->createToken("clientToken")->plainTextToken;

            return response()->json([
                "status" => "success",
                "type"   => "client",
                "user"   => $client,
                "token"  => $token,
            ]);
        }

        // Vérifier si c'est un Fournisseur
        $fournisseur = Fournisseur::where("emailFRN", $request->email)->first();
        if ($fournisseur && Hash::check($request->password, $fournisseur->passwordFRN)) {
            $fournisseur->tokens()->delete();
            $token = $fournisseur->createToken("fournisseurToken")->plainTextToken;

            return response()->json([
                "status" => "success",
                "type"   => "fournisseur",
                "user"   => $fournisseur,
                "token"  => $token,
            ]);
        }

        return response()->json([
            "status" => "error",
            "message" => "Identifiants invalides."
        ], 401);
    }
}
