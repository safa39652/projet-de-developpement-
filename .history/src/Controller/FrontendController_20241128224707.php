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
    
    // src/Controller/ApiController.php
    #[Route('/api/check-employee', name: 'api_check_employee', methods: ['POST'])]
public function checkEmployee(Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    if (!isset($data['image'])) {
        return new JsonResponse(['message' => 'Aucune image reçue'], 400);  // Erreur si l'image est absente
    }

    // Traitement de l'image (exemple fictif ici)
    // En production, vous devrez vérifier l'image et peut-être utiliser un modèle pour la reconnaissance faciale

    // Simulation d'un employé
    $employee = [
        'id' => 1,
        'firstName' => 'John',
        'lastName' => 'Doe',
        'photoPath' => '/images/employees/john_doe.jpg',
    ];

    return new JsonResponse([
        'message' => 'Employé reconnu',
        'employee' => $employee
    ]);
}


    

    

    // Page du dashboard (optionnel)
    #[Route('/front/dashboard', name: 'app_front_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('front/dashboard.html.twig');
    }
}
