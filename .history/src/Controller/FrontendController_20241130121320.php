<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response; 
use App\Repository\EmployeRepository;
class FrontendController extends AbstractController
{
    #[Route("/", name: "app_home")]
    public function index(): Response
    {
        return $this->render('frontend/index.html.twig');
    }

    #[Route('/api/check-employee', name: 'api_check_employee', methods: ['POST'])]
    public function checkEmployee(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        // Vérifier la présence de l'image
        if (!isset($data['image'])) {
            return new JsonResponse(['message' => 'Aucune image reçue'], 400);
        }
    
        // Décoder les données Base64
        $imageData = base64_decode($data['image']);
        if ($imageData === false) {
            return new JsonResponse(['message' => 'Échec de la décodification de l\'image'], 400);
        }
    
        // Définir un nom unique pour l'image
        $imageName = 'uploaded_image_' . uniqid() . '.jpg';
    
        // Chemin complet de l'image
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';
        $filePath = $uploadDir . $imageName;
    
        // Créer le répertoire si nécessaire
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
    
        // Enregistrer l'image
        if (file_put_contents($filePath, $imageData) === false) {
            return new JsonResponse(['message' => 'Erreur lors de l\'enregistrement de l\'image'], 500);
        }
    
        // Simuler une réponse de vérification de l'employé
        $employee = [
            'id' => 1,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'photoPath' => '/uploads/' . $imageName, // Chemin dynamique
        ];
    
        return new JsonResponse([
            'message' => 'Employé reconnu',
            'employee' => $employee,
        ]);
    }
    


    #[Route('/front/dashboard', name: 'app_front_dashboard')]
    public function dashboard(EmployeRepository $employeRepository): Response
    {
        $employes = $employeRepository->findAll();
        // Exemple de données dynamiques
        $connectedUsers = 45;
        $pendingAlerts = 3;
        $weeklyReports = 12;

        return $this->render('front/dashboard.html.twig', [
            'connectedUsers' => $connectedUsers,
            'pendingAlerts' => $pendingAlerts,
            'weeklyReports' => $weeklyReports,
            'employes' => $employes,
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
