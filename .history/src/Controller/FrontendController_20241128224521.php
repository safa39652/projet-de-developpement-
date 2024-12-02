<?
// src/Controller/ApiController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class FrontendController
{
    public function checkEmployee(Request $request)
    {
        // Récupération des données JSON envoyées
        $data = json_decode($request->getContent(), true);

        // Vérifiez si l'image est présente
        if (!isset($data['image'])) {
            return new JsonResponse(['message' => 'Aucune image reçue'], 400);
        }

        // Logique pour traiter l'image et vérifier l'employé

        return new JsonResponse(['message' => 'Employé vérifié avec succès', 'employee' ]);
    }
}
