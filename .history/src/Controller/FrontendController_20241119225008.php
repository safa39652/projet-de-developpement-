<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        // Simuler une donnée statique pour un employé détecté
        $data = json_decode($request->getContent(), true);

        // Supposons un ID statique
        $staticEmployeeId = 1;

        if (!isset($data['employeeId']) || $data['employeeId'] != $staticEmployeeId) {
            return new JsonResponse(['message' => 'Employé non trouvé'], 404);
        }

        // Données statiques de l'employé
        $employee = [
            'id' => $staticEmployeeId,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'photoPath' => '/images/employees/john_doe.jpg',
        ];

        return new JsonResponse([
            'message' => 'Bonne journée',
            'employee' => $employee,
        ]);
    }


    #[Route('/front/dashboard', name: 'app_front_dashboard')]
    public function dashboard(): Response
    {
        // Exemple de données dynamiques
        $connectedUsers = 45;
        $pendingAlerts = 3;
        $weeklyReports = 12;

        return $this->render('front/dashboard.html.twig', [
            'connectedUsers' => $connectedUsers,
            'pendingAlerts' => $pendingAlerts,
            'weeklyReports' => $weeklyReports,
        ]);
    }
    
    #[Route("/admin/login", name: "admin_login", methods: ["GET", "POST"])]
    public function adminLogin(Request $request): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
    
        // Example check (replace with real logic)
        if ($username === 'admin' && $password === 'admin') {
            // Redirect to the dashboard if login is successful
            return $this->redirectToRoute('app_front_dashboard');
        }
    
        // Render login form with error message if login fails
        return $this->render('admin/login.html.twig');
    }
    
    
}
