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
    // Vérifiez si l'image est envoyée dans le corps de la requête
    $data = json_decode($request->getContent(), true);

    if (!isset($data['image'])) {
        // Retourne un message d'erreur si l'image est manquante
        return new JsonResponse(['message' => 'Aucune image reçue'], 400);
    }

    // Simulation d'un employé
    $employee = [
        'id' => 1,
        'firstName' => 'John',
        'lastName' => 'Doe',
        'photoPath' => '/images/employees/john_doe.jpg',
    ];

    // Retourner la réponse JSON
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
