<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Approvisionnement;
use App\Models\Commande;
use App\Models\Prix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function ajoutProduit(Request $request)
    {
        // Debug pour voir ce qui arrive
        //Log::info("Reçu:", $request->all());
        //Log::info("Fichier:", [$request->file("imageProduct")]);

        $request->validate([
            "refProduct" => "required|unique:products",
            "designProduct" => "required|string",
            "marqueProduct" => "required|string",
            "description" => "nullable|string",
            "prix" => "required|numeric|min:0", // 🧩 ajouté ici
            "idSousCategorie" => "required|integer|exists:sous_categorie_produits,idSousCategorie",
            "idFournisseur" => "required|integer|exists:fournisseurs,idFournisseur",
            "imageProduct" => "nullable|file|max:2048",
            "imageDetailProduct.*" => "nullable|file|image|max:2048", // validation pour chaque image détaillée

        ]);

        // Upload image si présente
        $imagePath = null;
        if ($request->hasFile("imageProduct")) {
            $imagePath = $request->file("imageProduct")->store("produit", "public");
        }

        // Upload images détaillées
        $imageDetailsPaths = [];
        if ($request->hasFile("imageDetailProduct")) {
            foreach ($request->file("imageDetailProduct") as $image) {
                $imageDetailsPaths[] = $image->store("produit/details", "public");
            }
        }

        $stock = 0;
        // Créer le produit
        $product = Product::create([
            "refProduct" => $request->refProduct,
            "designProduct" => $request->designProduct,
            "marqueProduct" => $request->marqueProduct,
            "description" => $request->description,
            "stock" => $stock,
            "idSousCategorie" => $request->idSousCategorie,
            "idFournisseur" => $request->idFournisseur,
            "imageProduct" => $imagePath,
            "imageDetailProduct" => $imageDetailsPaths,

        ]);

        Prix::create([
            "idProduct" => $product->idProduct, // ou $product->id selon ta clé
            "prix" => $request->prix,
            "dateModification" => now(),
        ]);

        return response()->json([
            "success" => true,
            "message" => "Produit ajouté avec succès",
            "data" => [
                "produit" => $product,
                "prix" => $request->prix,
            ],
        ]);
    }

    /**
     * Modifier un produit
     */
    public function updateImage(Request $request, $idProduct)
    {
        try {
            // Valider seulement l'image
            $request->validate([
                "imageProduct" => "required|file|max:2048|mimes:jpeg,png,jpg,gif",
            ]);

            // Trouver le produit
            $product = Product::where('idProduct', $idProduct)->first();

            if (!$product) {
                return response()->json([
                    "success" => false,
                    "message" => "Produit non trouvé"
                ], 404);
            }

            // DEBUG: Log pour voir ce qui se passe
            Log::info("Modification image produit ID: " . $idProduct);
            Log::info("Image reçue:", ['file_name' => $request->file('imageProduct')->getClientOriginalName()]);

            // Gérer l'upload de la nouvelle image
            $imagePath = $product->imageProduct;

            // Supprimer l'ancienne image si elle existe
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                Log::info("Ancienne image supprimée: " . $imagePath);
            }

            // Uploader la nouvelle image
            $imagePath = $request->file("imageProduct")->store("produit", "public");
            Log::info("Nouvelle image uploadée: " . $imagePath);

            // Mettre à jour seulement l'image du produit
            $product->update([
                "imageProduct" => $imagePath
            ]);

            Log::info("Image du produit modifiée avec succès");

            return response()->json([
                "success" => true,
                "message" => "Image du produit modifiée avec succès",
                "data" => [
                    'idProduct' => $product->idProduct,
                    'imageProduct' => $imagePath,
                    'imageUrl' => Storage::disk('public')->url($imagePath)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la modification de l'image du produit: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                "success" => false,
                "message" => "Erreur lors de la modification de l'image",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function updateProduit(Request $request, $idProduct)
    {
        try {
            // Valider les données (sans l'image)
            $request->validate([
                "designProduct" => "required|string",
                "marqueProduct" => "required|string",
                "description" => "nullable|string",
            ]);

            // Trouver le produit
            $product = Product::where('idProduct', $idProduct)->first();

            if (!$product) {
                return response()->json([
                    "success" => false,
                    "message" => "Produit non trouvé"
                ], 404);
            }

            // DEBUG: Log pour voir ce qui se passe
            Log::info("Modification produit ID: " . $idProduct);
            Log::info("Données reçues:", $request->all());
            Log::info("Produit trouvé:", [
                'idProduct' => $product->idProduct,
                'designProduct' => $product->designProduct,
                'idFournisseur' => $product->idFournisseur
            ]);

            // Mettre à jour le produit (sans l'image)
            $updateData = [
                "designProduct" => $request->designProduct,
                "marqueProduct" => $request->marqueProduct,
                "description" => $request->description,
            ];

            $product->update($updateData);

            Log::info("Produit modifié avec succès (sans image)");

            return response()->json([
                "success" => true,
                "message" => "Produit modifié avec succès",
                "data" => $product
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la modification du produit: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                "success" => false,
                "message" => "Erreur lors de la modification du produit",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function getProduitsByFournisseur($idFournisseur)
    {
        // Récupère tous les produits avec leur dernier prix
        $produits = Product::with(['dernierPrix'])
            ->where('idFournisseur', $idFournisseur)
            ->get();

        // Calcule le stock pour chaque produit
        $produitsAvecStock = $produits->map(function ($produit) {
            $totalAppro = $produit->approvisionnements()->sum('qteAppro');
            $totalCom = $produit->commandes()->sum('qteCom');
            $produit->stockProd = $totalAppro - $totalCom;
            return $produit;
        });

        if ($produitsAvecStock->isEmpty()) {
            return response()->json([
                "success" => false,
                "message" => "Aucun produit trouvé pour ce fournisseur"
            ], 404);
        }

        return response()->json($produitsAvecStock);
    }

    public function ajoutNouveauPrix(Request $request, $idProduct)
    {
        $request->validate([
            'prix' => 'required|numeric|min:0',
        ]);

        $produit = Product::find($idProduct);

        if (!$produit) {
            return response()->json([
                'success' => false,
                'message' => 'Produit introuvable.'
            ], 404);
        }

        // Crée un nouveau prix dans la table `prix`
        $nouveauPrix = Prix::create([
            'idProduct' => $produit->idProduct,
            'prix' => $request->prix,
            'dateModification' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nouveau prix ajouté avec succès.',
            'data' => $nouveauPrix,
        ]);
    }

    public function getHistoriquePrix($idProduct)
    {
        // Vérifier si le produit existe
        $produit = Product::find($idProduct);

        if (!$produit) {
            return response()->json([
                'success' => false,
                'message' => 'Produit introuvable.'
            ], 404);
        }

        // Récupérer tous les prix du produit triés du plus récent au plus ancien
        $historique = \App\Models\Prix::where('idProduct', $idProduct)
            ->orderBy('dateModification', 'desc')
            ->get(['prix', 'dateModification']);

        if ($historique->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun historique de prix trouvé pour ce produit.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $historique
        ]);
    }



    public function index()
    {
        try {
            // Récupère tous les produits avec leurs relations
            $produits = Product::with([
                'dernierPrix',
                'sousCategorie.categorieProduit',
                'approvisionnements',
                'commandes'
            ])->get();

            // Formate les données avec les accesseurs
            $produitsAvecStock = $produits->map(function ($produit) {
                return [
                    'idProduct' => $produit->idProduct,
                    'refProduct' => $produit->refProduct,
                    'designProduct' => $produit->designProduct,
                    'marqueProduct' => $produit->marqueProduct,
                    'description' => $produit->description,
                    'imageProduct' => $produit->imageProduct,
                    'imageDetailProduct' => $produit->imageDetailProduct,
                    'idSousCategorie' => $produit->idSousCategorie,
                    'idFournisseur' => $produit->idFournisseur,
                    'categoryTitle' => $produit->sousCategorie ?
                        ($produit->sousCategorie->categorieProduit ?
                            $produit->sousCategorie->categorieProduit->nomCategorie . ' > ' . $produit->sousCategorie->nomSousCategorie :
                            $produit->sousCategorie->nomSousCategorie) :
                        'Non catégorisé',
                    'stockProd' => $produit->stock_prod, // Utilise l'accesseur
                    'total_approvisionnements' => $produit->approvisionnements->sum('qteAppro'),
                    'total_commandes' => $produit->commandes->sum('qteCom'),
                    'dernierPrix' => $produit->dernier_prix, // Utilise l'accesseur
                    'dernier_prix' => $produit->dernier_prix_details, // Utilise l'accesseur
                ];
            });

            if ($produitsAvecStock->isEmpty()) {
                return response()->json([
                    "success" => false,
                    "message" => "Aucun produit trouvé"
                ], 404);
            }

            return response()->json($produitsAvecStock);
        } catch (\Exception $e) {
            \Log::error("Erreur dans ProductController@index: " . $e->getMessage());

            return response()->json([
                "success" => false,
                "message" => "Erreur serveur lors du chargement des produits",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function getBySousCategorie($idSousCategorie)
    {
        $produits = \App\Models\Product::where('idSousCategorie', $idSousCategorie)->get();
        return response()->json($produits);
    }

    public function show($id)
    {
        try {
            // 🔍 Récupère un seul produit avec ses relations
            $produit = Product::with([
                'dernierPrix',
                'sousCategorie.categorieProduit',
                'approvisionnements',
                'commandes'
            ])->find($id); // <-- on utilise find($id)

            if (!$produit) {
                return response()->json([
                    "success" => false,
                    "message" => "Produit introuvable"
                ], 404);
            }

            // 🔧 Formater la réponse
            $produitData = [
                'idProduct' => $produit->idProduct,
                'refProduct' => $produit->refProduct,
                'designProduct' => $produit->designProduct,
                'marqueProduct' => $produit->marqueProduct,
                'description' => $produit->description,
                'imageProduct' => $produit->imageProduct,
                'imageDetailProduct' => $produit->imageDetailProduct,
                'idSousCategorie' => $produit->idSousCategorie,
                'idFournisseur' => $produit->idFournisseur,
                'categoryTitle' => $produit->sousCategorie ?
                    ($produit->sousCategorie->categorieProduit ?
                        $produit->sousCategorie->categorieProduit->nomCategorie . ' > ' . $produit->sousCategorie->nomSousCategorie :
                        $produit->sousCategorie->nomSousCategorie) :
                    'Non catégorisé',
                'stockProd' => $produit->stock_prod,
                'total_approvisionnements' => $produit->approvisionnements->sum('qteAppro'),
                'total_commandes' => $produit->commandes->sum('qteCom'),
                'dernierPrix' => $produit->dernier_prix,
                'dernier_prix' => $produit->dernier_prix_details,
                'price' => $produit->dernier_prix_details['prix'] ?? null, // optionnel pour ton front
            ];

            return response()->json($produitData, 200);
        } catch (\Exception $e) {
            \Log::error("Erreur dans ProductController@show: " . $e->getMessage());

            return response()->json([
                "success" => false,
                "message" => "Erreur serveur lors du chargement du produit",
                "error" => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Supprimer un produit
     */
    public function destroy($id)
    {
        try {
            $product = Product::where('idProduct', $id)->first();

            if (!$product) {
                return response()->json([
                    "success" => false,
                    "message" => "Produit non trouvé"
                ], 404);
            }

            // Supprimer les images du stockage
            if ($product->imageProduct) {
                Storage::disk('public')->delete($product->imageProduct);
            }

            // Supprimer les images détaillées
            if ($product->imageDetailProduct && is_array($product->imageDetailProduct)) {
                foreach ($product->imageDetailProduct as $detailImage) {
                    Storage::disk('public')->delete($detailImage);
                }
            }

            // Supprimer le produit de la base de données
            $product->delete();

            return response()->json([
                "success" => true,
                "message" => "Produit supprimé avec succès"
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la suppression du produit: " . $e->getMessage());

            return response()->json([
                "success" => false,
                "message" => "Erreur lors de la suppression du produit",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    // ProductController.php

    public function indexWithCategories(Request $request)
    {
        try {
            // Récupérer les paramètres de filtrage
            $query = $request->get('q', '');
            $categoryId = $request->get('category_id');

            Log::info("Filtrage demandé (avec stock/prix):", [
                'recherche' => $query,
                'categorie_id' => $categoryId
            ]);

            // Charger les produits avec filtres et les relations nécessaires
            $produits = Product::with([
                'dernierPrix', // Relation pour obtenir le dernier prix
                'sousCategorie.categorieProduit',
                'approvisionnements', // Relation pour le stock calculé
                'commandes'         // Relation pour le stock calculé
            ])
                ->when($query, function ($q) use ($query) {
                    // Filtrage par mots-clés (design, marque, description)
                    $q->where('designProduct', 'LIKE', "%{$query}%")
                        ->orWhere('marqueProduct', 'LIKE', "%{$query}%")
                        ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->when($categoryId, function ($q) use ($categoryId) {
                    // Filtrage par catégorie principale
                    $q->whereHas('sousCategorie', function ($subQ) use ($categoryId) {
                        $subQ->where('categorieProduit_id', $categoryId);
                    });
                })
                ->get(); // Exécute la requête

            Log::info("Produits filtrés chargés: " . $produits->count());

            // Mapper et formater les résultats pour inclure le stock et le prix calculés
            $result = $produits->map(function ($p) {

                // Calcul du chemin catégorie > sous-catégorie
                $categoryPath = "Non catégorisé";
                if ($p->sousCategorie && $p->sousCategorie->categorieProduit) {
                    $categoryPath = $p->sousCategorie->categorieProduit->nomCategorie . " > " . $p->sousCategorie->nomSousCategorie;
                } elseif ($p->sousCategorie) {
                    $categoryPath = "Catégorie > " . $p->sousCategorie->nomSousCategorie;
                }

                return [
                    "idProduct" => $p->idProduct,
                    "refProduct" => $p->refProduct,
                    "designProduct" => $p->designProduct,
                    "marqueProduct" => $p->marqueProduct,
                    "description" => $p->description,
                    "imageProduct" => $p->imageProduct,
                    "imageDetailProduct" => $p->imageDetailProduct,
                    "idSousCategorie" => $p->idSousCategorie,
                    "idFournisseur" => $p->idFournisseur,
                    "created_at" => $p->created_at,
                    "updated_at" => $p->updated_at,

                    // Données supplémentaires tirées de la fonction index()
                    "categoryTitle" => $categoryPath,
                    'stockProd' => $p->stock_prod, // Assurez-vous d'avoir l'accesseur getStockProdAttribute dans le modèle Product
                    'total_approvisionnements' => $p->approvisionnements->sum('qteAppro'),
                    'total_commandes' => $p->commandes->sum('qteCom'),
                    'dernierPrix' => $p->dernier_prix, // Assurez-vous d'avoir l'accesseur getDernierPrixAttribute dans le modèle Product
                    'dernier_prix_details' => $p->dernier_prix_details, // Assurez-vous d'avoir l'accesseur getDernierPrixDetailsAttribute dans le modèle Product

                    // Relations incluses pour la cohérence si nécessaire
                    "sous_categorie" => $p->sousCategorie ? [
                        "idSousCategorie" => $p->sousCategorie->idSousCategorie,
                        "nomSousCategorie" => $p->sousCategorie->nomSousCategorie,
                        "imageSousCategorie" => $p->sousCategorie->imageSousCategorie,
                    ] : null,
                    "categorie" => $p->sousCategorie && $p->sousCategorie->categorieProduit ? [
                        "idCategorie" => $p->sousCategorie->categorieProduit->idCategorie,
                        "nomCategorie" => $p->sousCategorie->categorieProduit->nomCategorie,
                    ] : null,
                ];
            });

            if ($result->isEmpty()) {
                return response()->json([
                    "success" => false,
                    "message" => "Aucun produit trouvé",
                ], 404);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error("Erreur dans indexWithCategories (avec stock/prix): " . $e->getMessage());
            return response()->json([
                "error" => "Erreur lors du chargement des produits",
                "message" => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Alternative avec Request pour plus de flexibilité
     */
    public function deleteProduct(Request $request, $id)
    {
        return $this->destroy($id);
    }

    public function getSearchSuggestions(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $categoryId = $request->get('category_id');

            $suggestions = Product::when($query, function ($q) use ($query) {
                $q->where('designProduct', 'LIKE', "%{$query}%")
                    ->orWhere('marqueProduct', 'LIKE', "%{$query}%");
            })
                ->when($categoryId, function ($q) use ($categoryId) {
                    $q->whereHas('sousCategorie', function ($subQ) use ($categoryId) {
                        $subQ->where('categorieProduit_id', $categoryId);
                    });
                })
                ->with(['sousCategorie.categorieProduit'])
                ->limit(10)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->idProduct,
                        'name' => $product->designProduct,
                        'brand' => $product->marqueProduct,
                        'category' => $product->sousCategorie ?
                            $product->sousCategorie->categorieProduit->nomCategorie . ' > ' . $product->sousCategorie->nomSousCategorie
                            : 'Non catégorisé'
                    ];
                });

            return response()->json($suggestions);
        } catch (\Exception $e) {
            Log::error("Erreur suggestions: " . $e->getMessage());
            return response()->json([], 500);
        }
    }

    public function getEtatStock($idProduct)
    {
        try {
            // Récupérer le produit avec ses relations
            $produit = Product::with(['sousCategorie', 'fournisseur', 'dernierPrix'])
                ->where('idProduct', $idProduct)
                ->first();

            if (!$produit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produit non trouvé'
                ], 404);
            }

            // Récupérer tous les approvisionnements pour ce produit
            $approvisionnements = Approvisionnement::where('idProduct', $idProduct)
                ->orderBy('dateAppro', 'desc')
                ->get();

            // Récupérer toutes les commandes pour ce produit
            $commandes = Commande::with('client')
                ->where('idProduct', $idProduct)
                ->orderBy('dateCom', 'desc')
                ->get();

            // Calculer le stock actuel
            $totalAppro = $approvisionnements->sum('qteAppro');
            $totalCommandes = $commandes->sum('qteCom');
            $stockActuel = $totalAppro - $totalCommandes;

            // Statistiques supplémentaires
            $statistiques = [
                'total_approvisionnements' => $totalAppro,
                'total_commandes' => $totalCommandes,
                'stock_actuel' => $stockActuel,
                'nombre_approvisionnements' => $approvisionnements->count(),
                'nombre_commandes' => $commandes->count(),
                'dernier_approvisionnement' => $approvisionnements->first() ? $approvisionnements->first()->dateAppro : null,
                'derniere_commande' => $commandes->first() ? $commandes->first()->dateCom : null,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'produit' => $produit,
                    'statistiques' => $statistiques,
                    'approvisionnements' => $approvisionnements,
                    'commandes' => $commandes,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la récupération de l'état de stock: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur lors de la récupération de l\'état de stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * État de stock global pour tous les produits d'un fournisseur
     */
    public function getEtatStockGlobal($idFournisseur)
    {
        try {
            $produits = Product::with(['sousCategorie', 'dernierPrix'])
                ->where('idFournisseur', $idFournisseur)
                ->get()
                ->map(function ($produit) {
                    $totalAppro = $produit->approvisionnements()->sum('qteAppro');
                    $totalCommandes = $produit->commandes()->sum('qteCom');
                    $stockActuel = $totalAppro - $totalCommandes;

                    return [
                        'produit' => $produit,
                        'statistiques' => [
                            'total_approvisionnements' => $totalAppro,
                            'total_commandes' => $totalCommandes,
                            'stock_actuel' => $stockActuel,
                            'nombre_approvisionnements' => $produit->approvisionnements()->count(),
                            'nombre_commandes' => $produit->commandes()->count(),
                        ]
                    ];
                });

            $statistiquesGlobales = [
                'total_produits' => $produits->count(),
                'total_stock' => $produits->sum('statistiques.stock_actuel'),
                'produits_en_rupture' => $produits->where('statistiques.stock_actuel', '<=', 0)->count(),
                'produits_faible_stock' => $produits->where('statistiques.stock_actuel', '>', 0)
                    ->where('statistiques.stock_actuel', '<=', 10)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'produits' => $produits,
                    'statistiques_globales' => $statistiquesGlobales,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la récupération de l'état de stock global: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur lors de la récupération de l\'état de stock global',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getProductSuggestions(Request $request)
    {
        // Récupère les paramètres de la requête
        $search = $request->query('search');
        $categoryId = $request->query('categoryId');

        // La recherche doit être d'au moins 2-3 caractères pour l'efficacité
        if (empty($search) || strlen($search) < 2) {
            return response()->json([], 200);
        }

        try {
            // Démarre la requête sur le modèle Product
            $query = Product::select('idProduct', 'designProduct', 'refProduct')
                ->where(function (Builder $q) use ($search) {
                    // Recherche dans la désignation (nom) ou la référence
                    $q->where('designProduct', 'like', '%' . $search . '%')
                        ->orWhere('refProduct', 'like', '%' . $search . '%');
                })
                // Limite le nombre de suggestions pour de meilleures performances
                ->limit(10);

            // Si une catégorie est spécifiée, on filtre par sous-catégories de cette catégorie
            if ($categoryId && $categoryId !== 'null') { // Vérifie aussi 'null' comme chaîne si c'est ce que React envoie
                $query->whereHas('sousCategorie', function (Builder $q) use ($categoryId) {
                    $q->where('categorieProduit_id', $categoryId);
                });
            }

            // Exécute la requête
            $products = $query->get();

            // Mappez les résultats pour les adapter au format souhaité par le frontend (comme la SubCategory)
            // On réutilise l'interface 'SubCategory' avec les champs du produit pour simplifier le front.
            $suggestions = $products->map(function ($product) {
                return [
                    'idSousCategorie' => $product->idProduct, // Utilise l'ID du produit
                    'nomSousCategorie' => $product->designProduct, // Utilise le nom du produit
                    'productCount' => null, // Pas de count de produits ici, c'est le produit lui-même
                    // Lien direct vers la page de détail du produit
                    'link' => '/produit/' . $product->idProduct,
                    'ref' => $product->refProduct, // Ajout de la référence pour l'affichage
                    'isProduct' => true, // Indicateur pour le front
                ];
            });

            return response()->json($suggestions, 200);
        } catch (\Exception $e) {
            // En cas d'erreur serveur
            return response()->json(['message' => 'Erreur lors de la récupération des suggestions de produits: ' . $e->getMessage()], 500);
        }
    }
}
