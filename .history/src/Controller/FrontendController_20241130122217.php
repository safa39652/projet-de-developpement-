<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response; 
use App\Repository\EmployeRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
class FrontendController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    #[Route("/", name: "app_home")]
    public function index(): Response
    {
        return $this->render('frontend/index.html.twig');
    }
    #[Route('/api/check-employee', name: 'api_check_employee', methods: ['POST'])]
    public function checkEmployee(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['image'])) {
            return new JsonResponse(['message' => 'Aucune image reçue'], 400);
        }

        // Appeler l'API Python
        $response = $this->httpClient->request('POST', 'http://localhost:5000/recognize', [
            'json' => [
                'image_path' => $this->getParameter('kernel.project_dir') . '/public/uploads/' . $data['image']
            ]
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {
            return new JsonResponse($response->toArray());
        }

        return new JsonResponse(['message' => 'Pas de correspondance'], 404);
    
    


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
