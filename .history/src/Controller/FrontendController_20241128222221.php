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
        $data = json_decode($request->getContent(), true);
        // Vérifier si les données existent
        if (!isset($data['image'])) {
            return new JsonResponse(['message' => 'Aucune image reçue'], 400);
        }
    
        // Votre logique de traitement de l'image ici (par exemple, appeler un modèle de reconnaissance faciale)
        return new JsonResponse(['message' => 'Employé reconnu', 'employee' => $employee]);
    }
    

    // Page du dashboard (optionnel)
    #[Route('/front/dashboard', name: 'app_front_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('front/dashboard.html.twig');
    }
}
