<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response; 

class FrontendController extends AbstractController
{
    #[Route("/", name: "app_home")]
    public function index(): Response
    {
        return $this->render('frontend/index.html.twig');
    }

    #[Route("/api/check-employee", name: "api_check_employee", methods: ['POST'])]
    public function checkEmployee(Request $request): JsonResponse
    {
        // Récupérer l'image envoyée
        $data = json_decode($request->getContent(), true);

        // Envoie de l'image vers le serveur Flask pour analyse
        $client = HttpClient::create();
        $response = $client->request('POST', 'http://127.0.0.1:5000/predict', [
            'json' => [
                'image' => $data['image']
            ]
        ]);

        $apiResponse = $response->toArray();

        if (isset($apiResponse['employee'])) {
            // Renvoie les données de l'employé
            return new JsonResponse([
                'message' => 'Employé reconnu',
                'employee' => $apiResponse['employee']
            ]);
        } else {
            // Si l'employé n'est pas trouvé
            return new JsonResponse(['message' => 'Employé non trouvé'], 404);
        }
    }

    // Page du dashboard (optionnel)
    #[Route('/front/dashboard', name: 'app_front_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('front/dashboard.html.twig');
    }
}
